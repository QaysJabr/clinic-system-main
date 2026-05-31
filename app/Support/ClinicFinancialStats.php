<?php

namespace App\Support;

use App\Models\DoctorEarning;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StaffPayment;
use App\Support\ClinicSettings;
use Illuminate\Support\Carbon;

/**
 * طبقتان ماليتان منفصلتان (لا تُخلط الأرقام بينهما في العرض أو صافي الربح مقابل التدفق النقدي):
 *
 * 1) الربحية (استحقاق — Profitability): إيراد من مجموع invoices.total؛ حصص أطباء من doctor_earnings؛
 *    مصروفات من سجلات المصروفات (expenses.amount)؛ رواتب من سجلات الرواتب (staff_payments.paid_amount عند تسجيل السجل).
 *    صافي الربح = إيراد − حصص − مصروفات مسجّلة − رواتب مسجّلة.
 *
 * 2) السيولة (نقد — Cash flow): وارد من دفعات المرضى (payments.amount)؛ صادر من دفعات المصروفات (expense_payments)،
 *    دفعات الرواتب (حسب تاريخ الصرف)، وتسويات أطباء «مدفوع» (doctor_earnings عند paid_at).
 *    صافي التدفق النقدي = وارد − صادر؛ رصيد النقد = الرصيد الافتتاحي (إعدادات العيادة) + صافي التراكمي.
 *
 * مجموع الفواتير.paid يُستخدم لتنبيه التناسق مع الذمم فقط — لا يُستخدم كإيراد استحقاق.
 */
final class ClinicFinancialStats
{
    /** إيرادات الاستحقاق — مجموع invoices.total (كل الوقت). */
    public static function totalAccrualRevenue(): float
    {
        return round((float) (Invoice::query()->sum('total') ?? 0), 2);
    }

    /**
     * المتحصّل النقدي — مجموع المبالغ المحصّلة على الفواتير (invoices.paid).
     * يفترض أن يساوي مجموع payments.amount عند عدم وجود بيانات يدوية متعارضة.
     */
    public static function totalCashCollected(): float
    {
        return round((float) (Invoice::query()->sum('paid') ?? 0), 2);
    }

    /** @deprecated استخدم {@see totalCashCollected} — الاسم القديم كان يوحي بـ «إيرادات» محاسبياً. */
    public static function totalIncome(): float
    {
        return self::totalCashCollected();
    }

    /** ذمم مدينة (غير محصّل بعد) — مجموع max(0, total − paid). */
    public static function totalAccountsReceivable(): float
    {
        $raw = Invoice::query()
            ->selectRaw('COALESCE(SUM(GREATEST(0, total - paid)), 0) as ar')
            ->value('ar');

        return round((float) $raw, 2);
    }

    /** إجمالي حصص الأطباء (نسبة) كمصروف استحقاق — مجموع doctor_earnings.earning_amount. */
    public static function totalDoctorShareExpense(): float
    {
        return round((float) (DoctorEarning::query()->sum('earning_amount') ?? 0), 2);
    }

    public static function totalExpenses(): float
    {
        return round((float) (Expense::query()->sum('amount') ?? 0), 2);
    }

    public static function totalPayrollPaid(): float
    {
        return round((float) (StaffPayment::query()->sum('paid_amount') ?? 0), 2);
    }

    /** وارد نقدي من المرضى — مجموع payments.amount (مصدر التدفق النقدي الوارد). */
    public static function totalPatientCashIn(): float
    {
        return round((float) (Payment::query()->sum('amount') ?? 0), 2);
    }

    /** صادر نقدي — دفعات المصروفات الفعلية (expense_payments). */
    public static function totalCashExpensePaymentsOut(): float
    {
        return round((float) (ExpensePayment::query()->sum('amount') ?? 0), 2);
    }

    /** صادر نقدي — ما صُرف من الرواتب (نفس أرقام التزام الرواتب عند الصرف). */
    public static function totalCashPayrollOut(): float
    {
        return self::totalPayrollPaid();
    }

    /** صادر نقدي — تسويات حصص الأطباء المعلّمة «مدفوع». */
    public static function totalCashDoctorPayoutsOut(): float
    {
        return round((float) (DoctorEarning::query()
            ->where('status', DoctorEarning::STATUS_PAID)
            ->sum('earning_amount') ?? 0), 2);
    }

    public static function totalCashOut(): float
    {
        return round(
            self::totalCashExpensePaymentsOut() + self::totalCashPayrollOut() + self::totalCashDoctorPayoutsOut(),
            2
        );
    }

    public static function netCashFlowLifetime(): float
    {
        return round(self::totalPatientCashIn() - self::totalCashOut(), 2);
    }

    public static function cashBalance(?float $openingCashBalance = null): float
    {
        $opening = $openingCashBalance ?? (float) (ClinicSettings::current()->opening_cash_balance ?? 0);

        return round($opening + self::netCashFlowLifetime(), 2);
    }

    /**
     * مصروفات مُثبّتة (استحقاق) بين تاريخي التزام — حسب تاريخ المصروف أو تاريخ الإنشاء.
     */
    public static function accrualExpensesRecordedBetween(Carbon $start, Carbon $end): float
    {
        return (float) (Expense::query()
            ->whereRaw('COALESCE(expense_date, DATE(created_at)) BETWEEN ? AND ?', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('amount') ?? 0);
    }

    /**
     * رواتب مُثبّتة (استحقاق) بين تاريخي تسجيل السجل — حسب تاريخ إنشاء سجل الراتب.
     */
    public static function accrualPayrollRecordedBetween(Carbon $start, Carbon $end): float
    {
        return (float) (StaffPayment::query()
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->sum('paid_amount') ?? 0);
    }

    /** دفعات مصروفات فعلية (نقد) — حسب paid_at. */
    public static function cashExpensePaymentsBetween(Carbon $start, Carbon $end): float
    {
        return (float) (ExpensePayment::query()
            ->whereDate('paid_at', '>=', $start->toDateString())
            ->whereDate('paid_at', '<=', $end->toDateString())
            ->sum('amount') ?? 0);
    }

    /** تسويات أطباء مدفوعة (نقد) — حسب paid_at. */
    public static function doctorPayoutsCashBetween(Carbon $start, Carbon $end): float
    {
        $s = $start->copy()->startOfDay();
        $e = $end->copy()->endOfDay();

        return (float) (DoctorEarning::query()
            ->where('status', DoctorEarning::STATUS_PAID)
            ->whereBetween('paid_at', [$s, $e])
            ->sum('earning_amount') ?? 0);
    }

    public static function cashOutBetween(Carbon $start, Carbon $end): float
    {
        return round(
            self::cashExpensePaymentsBetween($start, $end)
                + self::payrollPaidBetween($start, $end)
                + self::doctorPayoutsCashBetween($start, $end),
            2
        );
    }

    public static function netCashFlowBetween(Carbon $start, Carbon $end): float
    {
        return round(self::incomeBetween($start, $end) - self::cashOutBetween($start, $end), 2);
    }

    /**
     * مصروفات تشغيلية مباشرة: التزامات المصروفات + الرواتب المدفوعة (بدون حصص الأطباء).
     * تُعرض منفصلة عن حصص الأطباء لتفصيل الـ P&L؛ صافي الربح يخصم الطرفين معاً.
     */
    public static function totalOperatingExpensesOther(): float
    {
        return round(self::totalExpenses() + self::totalPayrollPaid(), 2);
    }

    /** الربح الإجمالي (قبل مصروفات التشغيل الأخرى والرواتب) = إيراد الاستحقاق − حصص الأطباء. */
    public static function grossProfit(): float
    {
        return round(
            self::totalAccrualRevenue() - self::totalDoctorShareExpense(),
            2
        );
    }

    /** تكلفة التشغيل الكلية = حصص أطباء + مصروفات + رواتب (ما يُخصم من الإيراد للوصول لصافي الربح). */
    public static function totalOperatingCost(): float
    {
        return round(
            self::totalDoctorShareExpense() + self::totalExpenses() + self::totalPayrollPaid(),
            2
        );
    }

    public static function netProfit(): float
    {
        return round(
            self::totalAccrualRevenue() - self::totalOperatingCost(),
            2
        );
    }

    public static function todayCashCollected(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return (float) (Payment::query()
            ->whereDate('payment_date', $at->toDateString())
            ->sum('amount') ?? 0);
    }

    public static function todayAccrualRevenue(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualRevenueBetween($at->copy()->startOfDay(), $at->copy()->endOfDay()), 2);
    }

    /** دفعات مصروفات اليوم (نقد). */
    public static function todayCashExpensePayments(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return (float) (ExpensePayment::query()
            ->whereDate('paid_at', $at->toDateString())
            ->sum('amount') ?? 0);
    }

    /** @deprecated {@see todayCashExpensePayments} */
    public static function todayExpenses(?Carbon $at = null): float
    {
        return self::todayCashExpensePayments($at);
    }

    /** مصروفات مُثبّتة اليوم (استحقاق) — حسب تاريخ المصروف أو الإنشاء. */
    public static function todayAccrualExpensesRecorded(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualExpensesRecordedBetween($at->copy()->startOfDay(), $at->copy()->endOfDay()), 2);
    }

    /** مدفوعات رواتب مرتبطة بتاريخ اليوم (تاريخ الدفع أو تاريخ إنشاء السجل). */
    /** صرف رواتب اليوم (نقد). */
    public static function todayCashPayrollOut(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return (float) (StaffPayment::query()
            ->whereRaw('DATE(COALESCE(payment_date, created_at)) = ?', [$at->toDateString()])
            ->sum('paid_amount') ?? 0);
    }

    /** @deprecated {@see todayCashPayrollOut} */
    public static function todayPayroll(?Carbon $at = null): float
    {
        return self::todayCashPayrollOut($at);
    }

    /** رواتب مُثبّتة اليوم (استحقاق) — تاريخ إنشاء السجل. */
    public static function todayAccrualPayrollRecorded(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualPayrollRecordedBetween($at->copy()->startOfDay(), $at->copy()->endOfDay()), 2);
    }

    public static function todayDoctorPayoutsCash(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorPayoutsCashBetween($at->copy()->startOfDay(), $at->copy()->endOfDay()), 2);
    }

    public static function todayDoctorShareExpense(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorShareExpenseBetween($at->copy()->startOfDay(), $at->copy()->endOfDay()), 2);
    }

    /** @deprecated استخدم {@see todayCashCollected} */
    public static function todayIncome(?Carbon $at = null): float
    {
        return self::todayCashCollected($at);
    }

    public static function thisMonthCashCollected(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return (float) (Payment::query()
            ->whereYear('payment_date', $at->year)
            ->whereMonth('payment_date', $at->month)
            ->sum('amount') ?? 0);
    }

    public static function thisMonthAccrualRevenue(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualRevenueBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    public static function thisMonthCashExpensePayments(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return (float) (ExpensePayment::query()
            ->whereYear('paid_at', $at->year)
            ->whereMonth('paid_at', $at->month)
            ->sum('amount') ?? 0);
    }

    /** @deprecated {@see thisMonthCashExpensePayments} */
    public static function thisMonthExpenses(?Carbon $at = null): float
    {
        return self::thisMonthCashExpensePayments($at);
    }

    public static function thisMonthAccrualExpensesRecorded(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualExpensesRecordedBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    /**
     * رواتب مُسجّلة ضمن الشهر الحالي حسب تاريخ الدفع أو تاريخ الإنشاء عند غياب تاريخ الدفع.
     */
    public static function thisMonthPayroll(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->startOfMonth()->toDateString();
        $end = $at->copy()->endOfMonth()->toDateString();

        return (float) (StaffPayment::query()
            ->whereRaw('COALESCE(payment_date, DATE(created_at)) BETWEEN ? AND ?', [$start, $end])
            ->sum('paid_amount') ?? 0);
    }

    public static function thisMonthCashPayrollOut(?Carbon $at = null): float
    {
        return self::thisMonthPayroll($at);
    }

    public static function thisMonthAccrualPayrollRecorded(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualPayrollRecordedBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    public static function thisMonthDoctorPayoutsCash(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorPayoutsCashBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    public static function lastMonthDoctorPayoutsCash(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return round(self::doctorPayoutsCashBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth()), 2);
    }

    public static function yearToDateDoctorPayoutsCash(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorPayoutsCashBetween($at->copy()->startOfYear(), $at->copy()), 2);
    }

    public static function last30DaysDoctorPayoutsCash(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return round(self::doctorPayoutsCashBetween($start, $at), 2);
    }

    public static function thisMonthDoctorShareExpense(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorShareExpenseBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    /** @deprecated استخدم {@see thisMonthCashCollected} */
    public static function thisMonthIncome(?Carbon $at = null): float
    {
        return self::thisMonthCashCollected($at);
    }

    public static function thisMonthProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::netProfitBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()), 2);
    }

    /** مجموع مدفوعات الفواتير بين تاريخين (شامل) حسب payment_date — أساس نقدي. */
    public static function incomeBetween(Carbon $start, Carbon $end): float
    {
        return (float) (Payment::query()
            ->whereDate('payment_date', '>=', $start->toDateString())
            ->whereDate('payment_date', '<=', $end->toDateString())
            ->sum('amount') ?? 0);
    }

    /** إيرادات الاستحقاق بين تاريخي إنشاء الفاتورة (شامل). */
    public static function accrualRevenueBetween(Carbon $start, Carbon $end): float
    {
        $s = $start->copy()->startOfDay();
        $e = $end->copy()->endOfDay();

        return (float) (Invoice::query()
            ->whereBetween('created_at', [$s, $e])
            ->sum('total') ?? 0);
    }

    /**
     * حصص الأطباء (مصروف) مرتبطة بفواتير صُدِرت ضمن الفترة (حسب تاريخ إنشاء الفاتورة).
     */
    public static function doctorShareExpenseBetween(Carbon $start, Carbon $end): float
    {
        $s = $start->copy()->startOfDay();
        $e = $end->copy()->endOfDay();

        return (float) (DoctorEarning::query()
            ->join('invoices', 'invoices.id', '=', 'doctor_earnings.invoice_id')
            ->whereBetween('invoices.created_at', [$s, $e])
            ->sum('doctor_earnings.earning_amount') ?? 0);
    }

    /**
     * @deprecated استخدم {@see cashExpensePaymentsBetween} (نقد) أو {@see accrualExpensesRecordedBetween} (استحقاق).
     */
    public static function expensesBetween(Carbon $start, Carbon $end): float
    {
        return self::cashExpensePaymentsBetween($start, $end);
    }

    /**
     * صرف رواتب (نقد) بين تاريخين — COALESCE(payment_date, تاريخ الإنشاء).
     */
    public static function payrollPaidBetween(Carbon $start, Carbon $end): float
    {
        return (float) (StaffPayment::query()
            ->whereRaw('COALESCE(payment_date, DATE(created_at)) BETWEEN ? AND ?', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->sum('paid_amount') ?? 0);
    }

    public static function grossProfitBetween(Carbon $start, Carbon $end): float
    {
        return round(
            self::accrualRevenueBetween($start, $end) - self::doctorShareExpenseBetween($start, $end),
            2
        );
    }

    public static function operatingCostBetween(Carbon $start, Carbon $end): float
    {
        return round(
            self::doctorShareExpenseBetween($start, $end)
                + self::accrualExpensesRecordedBetween($start, $end)
                + self::accrualPayrollRecordedBetween($start, $end),
            2
        );
    }

    public static function netProfitBetween(Carbon $start, Carbon $end): float
    {
        return round(
            self::accrualRevenueBetween($start, $end) - self::operatingCostBetween($start, $end),
            2
        );
    }

    public static function lastMonthAccrualRevenue(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return round(self::accrualRevenueBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth()), 2);
    }

    public static function lastMonthCashCollected(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::incomeBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    public static function lastMonthExpenses(?Carbon $at = null): float
    {
        return self::thisMonthExpenses(($at ?? now())->copy()->subMonth());
    }

    public static function lastMonthPayroll(?Carbon $at = null): float
    {
        return self::thisMonthPayroll(($at ?? now())->copy()->subMonth());
    }

    public static function lastMonthDoctorShareExpense(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return round(self::doctorShareExpenseBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth()), 2);
    }

    public static function lastMonthProfit(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::netProfitBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    /** @deprecated استخدم {@see lastMonthCashCollected} */
    public static function lastMonthIncome(?Carbon $at = null): float
    {
        return self::lastMonthCashCollected($at);
    }

    public static function yearToDateAccrualRevenue(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualRevenueBetween($at->copy()->startOfYear(), $at->copy()), 2);
    }

    public static function yearToDateCashCollected(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::incomeBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDateExpenses(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::expensesBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDatePayroll(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::payrollPaidBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDateDoctorShareExpense(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::doctorShareExpenseBetween($at->copy()->startOfYear(), $at->copy()), 2);
    }

    public static function yearToDateNetProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::netProfitBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDateAccrualExpenses(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualExpensesRecordedBetween($at->copy()->startOfYear(), $at->copy()), 2);
    }

    public static function yearToDateAccrualPayroll(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::accrualPayrollRecordedBetween($at->copy()->startOfYear(), $at->copy()), 2);
    }

    public static function yearToDateCashOut(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::cashOutBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDateNetCashFlow(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::netCashFlowBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function thisMonthCashPaidOut(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::cashOutBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth());
    }

    public static function thisMonthNetCashFlow(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::netCashFlowBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth());
    }

    /** @deprecated استخدم {@see yearToDateCashCollected} */
    public static function yearToDateIncome(?Carbon $at = null): float
    {
        return self::yearToDateCashCollected($at);
    }

    public static function last30DaysAccrualRevenue(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return round(self::accrualRevenueBetween($start, $at), 2);
    }

    public static function last30DaysCashCollected(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::incomeBetween($start, $at);
    }

    public static function last30DaysExpenses(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::cashExpensePaymentsBetween($start, $at);
    }

    public static function last30DaysAccrualExpenses(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return round(self::accrualExpensesRecordedBetween($start, $at), 2);
    }

    public static function last30DaysAccrualPayroll(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return round(self::accrualPayrollRecordedBetween($start, $at), 2);
    }

    public static function last30DaysPayroll(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::payrollPaidBetween($start, $at);
    }

    public static function last30DaysDoctorShareExpense(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return round(self::doctorShareExpenseBetween($start, $at), 2);
    }

    public static function last30DaysNetProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::netProfitBetween($start, $at);
    }

    public static function last30DaysCashOut(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::cashOutBetween($start, $at);
    }

    public static function last30DaysNetCashFlow(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::netCashFlowBetween($start, $at);
    }

    public static function lastMonthAccrualExpenses(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return round(self::accrualExpensesRecordedBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth()), 2);
    }

    public static function lastMonthAccrualPayroll(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return round(self::accrualPayrollRecordedBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth()), 2);
    }

    public static function lastMonthGrossProfit(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::grossProfitBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    public static function lastMonthOperatingCost(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::operatingCostBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    public static function lastMonthCashOut(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::cashOutBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    public static function lastMonthNetCashFlow(?Carbon $at = null): float
    {
        $m = ($at ?? now())->copy()->subMonth();

        return self::netCashFlowBetween($m->copy()->startOfMonth(), $m->copy()->endOfMonth());
    }

    public static function yearToDateGrossProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::grossProfitBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function yearToDateOperatingCost(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return self::operatingCostBetween($at->copy()->startOfYear(), $at->copy());
    }

    public static function last30DaysGrossProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::grossProfitBetween($start, $at);
    }

    public static function last30DaysOperatingCost(?Carbon $at = null): float
    {
        $at = $at ?? now();
        $start = $at->copy()->subDays(29)->startOfDay();

        return self::operatingCostBetween($start, $at);
    }

    /** @deprecated استخدم {@see last30DaysCashCollected} */
    public static function last30DaysIncome(?Carbon $at = null): float
    {
        return self::last30DaysCashCollected($at);
    }

    /**
     * إجمالي الصادر النقدي — دفعات مصروفات + رواتب + تسويات أطباء (نقد فقط).
     */
    public static function totalOperatingOutflows(): float
    {
        return self::totalCashOut();
    }

    /**
     * @return list<array{
     *     ym: string,
     *     label: string,
     *     accrual_revenue: float,
     *     doctor_share: float,
     *     expenses_accrual: float,
     *     payroll_accrual: float,
     *     gross_profit: float,
     *     operating_cost_accrual: float,
     *     net_profit_accrual: float,
     *     cash_in_patients: float,
     *     cash_out_expenses: float,
     *     cash_out_payroll: float,
     *     cash_out_doctors: float,
     *     net_cash_flow: float,
     *     cash_paid_out_total: float,
     *     cash_collected: float,
     *     expenses: float,
     *     payroll: float,
     *     operating_cost_total: float,
     *     net: float,
     *     income: float,
     * }>
     */
    public static function monthlyFinancialTrend(int $months = 12, ?Carbon $at = null): array
    {
        $at = $at ?? now();
        $end = $at->copy();
        $start = $at->copy()->subMonths(max(1, $months) - 1)->startOfMonth();

        $keys = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $ym = $m->format('Y-m');
            $keys[$ym] = [
                'ym' => $ym,
                'label' => $m->copy()->locale(app()->getLocale())->translatedFormat(__('reports.month_label_format')),
                'accrual_revenue' => 0.0,
                'doctor_share' => 0.0,
                'expenses_accrual' => 0.0,
                'payroll_accrual' => 0.0,
                'gross_profit' => 0.0,
                'operating_cost_accrual' => 0.0,
                'net_profit_accrual' => 0.0,
                'cash_in_patients' => 0.0,
                'cash_out_expenses' => 0.0,
                'cash_out_payroll' => 0.0,
                'cash_out_doctors' => 0.0,
                'cash_paid_out_total' => 0.0,
                'net_cash_flow' => 0.0,
                'cash_collected' => 0.0,
                'expenses' => 0.0,
                'payroll' => 0.0,
                'operating_cost_total' => 0.0,
                'net' => 0.0,
                'income' => 0.0,
            ];
        }

        $windowStart = $start->copy()->startOfDay();
        $windowEnd = $end->copy()->endOfDay();

        Invoice::query()
            ->whereBetween('created_at', [$windowStart, $windowEnd])
            ->select(['id', 'created_at', 'total'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $inv) {
                    if (! $inv->created_at) {
                        continue;
                    }
                    $ym = $inv->created_at->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['accrual_revenue'] += (float) $inv->total;
                    }
                }
            });

        Payment::query()
            ->whereDate('payment_date', '>=', $start->toDateString())
            ->whereDate('payment_date', '<=', $end->toDateString())
            ->select(['id', 'payment_date', 'amount'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $p) {
                    if (! $p->payment_date) {
                        continue;
                    }
                    $ym = $p->payment_date->format('Y-m');
                    if (isset($keys[$ym])) {
                        $amt = (float) $p->amount;
                        $keys[$ym]['cash_in_patients'] += $amt;
                        $keys[$ym]['cash_collected'] += $amt;
                    }
                }
            });

        DoctorEarning::query()
            ->join('invoices', 'invoices.id', '=', 'doctor_earnings.invoice_id')
            ->whereBetween('invoices.created_at', [$windowStart, $windowEnd])
            ->select(['doctor_earnings.id', 'doctor_earnings.earning_amount', 'invoices.created_at as invoice_created_at'])
            ->orderBy('doctor_earnings.id')
            ->chunk(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $row) {
                    $created = $row->invoice_created_at ?? null;
                    if (! $created) {
                        continue;
                    }
                    $ym = Carbon::parse($created)->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['doctor_share'] += (float) $row->earning_amount;
                    }
                }
            });

        Expense::query()
            ->whereRaw('COALESCE(expense_date, DATE(created_at)) BETWEEN ? AND ?', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->select(['id', 'amount', 'expense_date', 'created_at'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $ex) {
                    $d = $ex->expense_date ?? $ex->created_at;
                    if (! $d) {
                        continue;
                    }
                    $ym = Carbon::parse($d)->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['expenses_accrual'] += (float) $ex->amount;
                    }
                }
            });

        ExpensePayment::query()
            ->whereDate('paid_at', '>=', $start->toDateString())
            ->whereDate('paid_at', '<=', $end->toDateString())
            ->select(['id', 'paid_at', 'amount'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $row) {
                    if (! $row->paid_at) {
                        continue;
                    }
                    $ym = $row->paid_at->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['cash_out_expenses'] += (float) $row->amount;
                    }
                }
            });

        StaffPayment::query()
            ->whereBetween('created_at', [$windowStart, $windowEnd])
            ->select(['id', 'paid_amount', 'created_at'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $sp) {
                    if (! $sp->created_at) {
                        continue;
                    }
                    $ym = $sp->created_at->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['payroll_accrual'] += (float) $sp->paid_amount;
                    }
                }
            });

        StaffPayment::query()
            ->whereRaw('COALESCE(payment_date, DATE(created_at)) BETWEEN ? AND ?', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->select(['id', 'paid_amount', 'payment_date', 'created_at'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $sp) {
                    $d = $sp->payment_date ?? $sp->created_at;
                    $ym = Carbon::parse($d)->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['cash_out_payroll'] += (float) $sp->paid_amount;
                    }
                }
            });

        DoctorEarning::query()
            ->where('status', DoctorEarning::STATUS_PAID)
            ->whereBetween('paid_at', [$windowStart, $windowEnd])
            ->select(['id', 'earning_amount', 'paid_at'])
            ->orderBy('id')
            ->chunkById(1000, function ($chunk) use (&$keys): void {
                foreach ($chunk as $de) {
                    if (! $de->paid_at) {
                        continue;
                    }
                    $ym = $de->paid_at->format('Y-m');
                    if (isset($keys[$ym])) {
                        $keys[$ym]['cash_out_doctors'] += (float) $de->earning_amount;
                    }
                }
            });

        $out = [];
        foreach ($keys as &$row) {
            $row['accrual_revenue'] = round($row['accrual_revenue'], 2);
            $row['doctor_share'] = round($row['doctor_share'], 2);
            $row['expenses_accrual'] = round($row['expenses_accrual'], 2);
            $row['payroll_accrual'] = round($row['payroll_accrual'], 2);
            $row['gross_profit'] = round($row['accrual_revenue'] - $row['doctor_share'], 2);
            $row['operating_cost_accrual'] = round(
                $row['doctor_share'] + $row['expenses_accrual'] + $row['payroll_accrual'],
                2
            );
            $row['net_profit_accrual'] = round($row['accrual_revenue'] - $row['operating_cost_accrual'], 2);
            $row['cash_in_patients'] = round($row['cash_in_patients'], 2);
            $row['cash_out_expenses'] = round($row['cash_out_expenses'], 2);
            $row['cash_out_payroll'] = round($row['cash_out_payroll'], 2);
            $row['cash_out_doctors'] = round($row['cash_out_doctors'], 2);
            $row['cash_paid_out_total'] = round(
                $row['cash_out_expenses'] + $row['cash_out_payroll'] + $row['cash_out_doctors'],
                2
            );
            $row['net_cash_flow'] = round(
                $row['cash_in_patients'] - $row['cash_paid_out_total'],
                2
            );
            $row['cash_collected'] = $row['cash_in_patients'];
            $row['expenses'] = $row['expenses_accrual'];
            $row['payroll'] = $row['payroll_accrual'];
            $row['operating_cost_total'] = $row['operating_cost_accrual'];
            $row['net'] = $row['net_profit_accrual'];
            $row['income'] = $row['cash_in_patients'];
            $out[] = $row;
        }
        unset($row);

        return array_reverse($out);
    }

    /**
     * توزيع التحصيل النقدي حسب طريقة الدفع (كل الوقت) — من جدول payments.
     *
     * @return array<string, float>
     */
    public static function incomeTotalsByPaymentMethod(): array
    {
        $out = [];
        foreach (Payment::query()->select(['payment_method', 'amount'])->cursor() as $p) {
            $m = $p->payment_method !== null && trim((string) $p->payment_method) !== ''
                ? (string) $p->payment_method
                : 'other';
            $out[$m] = ($out[$m] ?? 0) + (float) $p->amount;
        }
        foreach ($out as &$v) {
            $v = round($v, 2);
        }
        unset($v);

        return $out;
    }

    /**
     * مجموع دفعات المصروفات الفعلية حسب طريقة الدفع (كل الوقت) — من expense_payments.
     *
     * @return array<string, float>
     */
    public static function expensePaymentTotalsByPaymentMethod(): array
    {
        $out = [];
        foreach (ExpensePayment::query()->select(['payment_method', 'amount'])->cursor() as $p) {
            $m = $p->payment_method !== null && trim((string) $p->payment_method) !== ''
                ? (string) $p->payment_method
                : 'other';
            $out[$m] = ($out[$m] ?? 0) + (float) $p->amount;
        }
        foreach ($out as &$v) {
            $v = round($v, 2);
        }
        unset($v);

        return $out;
    }

    public static function todayGrossProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(
            self::todayAccrualRevenue($at) - self::todayDoctorShareExpense($at),
            2
        );
    }

    public static function todayOperatingCost(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(
            self::todayDoctorShareExpense($at) + self::todayAccrualExpensesRecorded($at) + self::todayAccrualPayrollRecorded($at),
            2
        );
    }

    public static function todayNetProfit(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(
            self::todayAccrualRevenue($at) - self::todayOperatingCost($at),
            2
        );
    }

    public static function todayCashPaidOut(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(
            self::todayCashExpensePayments($at) + self::todayCashPayrollOut($at) + self::todayDoctorPayoutsCash($at),
            2
        );
    }

    public static function todayNetCashFlow(?Carbon $at = null): float
    {
        $at = $at ?? now();

        return round(self::todayCashCollected($at) - self::todayCashPaidOut($at), 2);
    }

    /**
     * فحص تناسق: إيراد الاستحقاق − (وارد دفعات المرضى + الذمم). يُفضّل مواءمته مع جدول payments وليس invoices.paid فقط.
     */
    public static function accrualCashArDelta(): float
    {
        return round(
            self::totalAccrualRevenue() - (self::totalPatientCashIn() + self::totalAccountsReceivable()),
            2
        );
    }

    /**
     * مؤشرات الربحية (استحقاق فقط) — للتقارير وقسم الربح في لوحة التحكم.
     *
     * @return array<string, float>
     */
    public static function getAccrualMetrics(?Carbon $at = null): array
    {
        $at = $at ?? now();

        return [
            'totalRevenue' => self::totalAccrualRevenue(),
            'totalDoctorShares' => self::totalDoctorShareExpense(),
            'grossProfit' => self::grossProfit(),
            'totalExpenses' => self::totalExpenses(),
            'totalSalaries' => self::totalPayrollPaid(),
            'totalOperatingCost' => self::totalOperatingCost(),
            'netProfit' => self::netProfit(),
            'todayRevenue' => self::todayAccrualRevenue($at),
            'todayDoctorShares' => self::todayDoctorShareExpense($at),
            'todayExpenses' => self::todayAccrualExpensesRecorded($at),
            'todaySalaries' => self::todayAccrualPayrollRecorded($at),
            'todayGrossProfit' => self::todayGrossProfit($at),
            'todayOperatingCost' => self::todayOperatingCost($at),
            'todayNetProfit' => self::todayNetProfit($at),
            'thisMonthRevenue' => self::thisMonthAccrualRevenue($at),
            'thisMonthDoctorShares' => self::thisMonthDoctorShareExpense($at),
            'thisMonthExpenses' => self::thisMonthAccrualExpensesRecorded($at),
            'thisMonthSalaries' => self::thisMonthAccrualPayrollRecorded($at),
            'thisMonthGrossProfit' => self::grossProfitBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()),
            'thisMonthOperatingCost' => self::operatingCostBetween($at->copy()->startOfMonth(), $at->copy()->endOfMonth()),
            'thisMonthNetProfit' => self::thisMonthProfit($at),
        ];
    }

    /**
     * مؤشرات التدفق النقدي (سيولة) — دفعات فعلية فقط.
     *
     * @return array<string, float>
     */
    public static function getCashFlowMetrics(?Carbon $at = null): array
    {
        $at = $at ?? now();

        return [
            'cashInPatientsTotal' => self::totalPatientCashIn(),
            'cashOutExpensesTotal' => self::totalCashExpensePaymentsOut(),
            'cashOutPayrollTotal' => self::totalCashPayrollOut(),
            'cashOutDoctorsTotal' => self::totalCashDoctorPayoutsOut(),
            'cashPaidOutTotal' => self::totalCashOut(),
            'netCashFlowLifetime' => self::netCashFlowLifetime(),
            'cashBalance' => self::cashBalance(),
            'todayCashIn' => self::todayCashCollected($at),
            'todayCashOut' => self::todayCashPaidOut($at),
            'todayNetCashFlow' => self::todayNetCashFlow($at),
            'thisMonthCashIn' => self::thisMonthCashCollected($at),
            'thisMonthCashOut' => self::thisMonthCashPaidOut($at),
            'thisMonthNetCashFlow' => self::thisMonthNetCashFlow($at),
        ];
    }

    /**
     * @return array<string, float|array<string, float>>
     */
    public static function allForDashboard(?Carbon $at = null): array
    {
        $at = $at ?? now();
        $accrual = self::getAccrualMetrics($at);
        $cash = self::getCashFlowMetrics($at);

        return array_merge(
            [
                'profitability' => $accrual,
                'cashFlow' => $cash,
            ],
            [
                'totalAccrualRevenue' => $accrual['totalRevenue'],
                'totalDoctorShareExpense' => $accrual['totalDoctorShares'],
                'grossProfit' => $accrual['grossProfit'],
                'totalExpenses' => $accrual['totalExpenses'],
                'totalPayrollPaid' => $accrual['totalSalaries'],
                'totalOperatingCost' => $accrual['totalOperatingCost'],
                'totalExpensesAndPayrollOnly' => self::totalOperatingExpensesOther(),
                'totalOperatingExpenses' => self::totalOperatingExpensesOther(),
                'netProfit' => $accrual['netProfit'],
                'totalAccountsReceivable' => self::totalAccountsReceivable(),
                'totalCashCollected' => self::totalCashCollected(),
                'totalPatientCashIn' => $cash['cashInPatientsTotal'],
                'totalCashPaidOut' => $cash['cashPaidOutTotal'],
                'netCashFlowLifetime' => $cash['netCashFlowLifetime'],
                'cashBalance' => $cash['cashBalance'],
                'accrualCashArDelta' => self::accrualCashArDelta(),
                'todayAccrualRevenue' => $accrual['todayRevenue'],
                'todayDoctorShareExpense' => $accrual['todayDoctorShares'],
                'todayGrossProfit' => $accrual['todayGrossProfit'],
                'todayOperatingCost' => $accrual['todayOperatingCost'],
                'todayAccrualExpensesRecorded' => $accrual['todayExpenses'],
                'todayAccrualPayrollRecorded' => $accrual['todaySalaries'],
                'todayNetProfit' => $accrual['todayNetProfit'],
                'todayCashCollected' => $cash['todayCashIn'],
                'todayCashPaidOut' => $cash['todayCashOut'],
                'todayNetCashFlow' => $cash['todayNetCashFlow'],
                'todayExpenses' => self::todayCashExpensePayments($at),
                'todayPayroll' => self::todayCashPayrollOut($at),
                'todayDoctorPayoutsCash' => self::todayDoctorPayoutsCash($at),
                'thisMonthAccrualRevenue' => $accrual['thisMonthRevenue'],
                'thisMonthDoctorShareExpense' => $accrual['thisMonthDoctorShares'],
                'thisMonthGrossProfit' => $accrual['thisMonthGrossProfit'],
                'thisMonthOperatingCost' => $accrual['thisMonthOperatingCost'],
                'thisMonthAccrualExpensesRecorded' => $accrual['thisMonthExpenses'],
                'thisMonthAccrualPayrollRecorded' => $accrual['thisMonthSalaries'],
                'thisMonthProfit' => $accrual['thisMonthNetProfit'],
                'thisMonthCashCollected' => $cash['thisMonthCashIn'],
                'thisMonthCashPaidOut' => $cash['thisMonthCashOut'],
                'thisMonthNetCashFlow' => $cash['thisMonthNetCashFlow'],
                'thisMonthExpenses' => self::thisMonthCashExpensePayments($at),
                'thisMonthPayroll' => self::thisMonthPayroll($at),
                'thisMonthDoctorPayoutsCash' => self::thisMonthDoctorPayoutsCash($at),
                'totalIncome' => $cash['cashInPatientsTotal'],
            ]
        );
    }
}
