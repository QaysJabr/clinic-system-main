<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use App\Support\PaymentMethods;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use BelongsToClinic;

    public const SETTLEMENT_FULL = 'full';

    public const SETTLEMENT_INSTALLMENTS = 'installments';

    protected $fillable = [
        'clinic_id',
        'expense_category_id',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'settlement_type',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function isFullSettlement(): bool
    {
        return $this->settlement_type === self::SETTLEMENT_FULL;
    }

    public function isInstallments(): bool
    {
        return $this->settlement_type === self::SETTLEMENT_INSTALLMENTS;
    }

    /**
     * @return HasMany<ExpensePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class)->orderByDesc('paid_at')->orderByDesc('id');
    }

    public function paymentsOrdered(): HasMany
    {
        return $this->hasMany(ExpensePayment::class)->orderBy('paid_at')->orderBy('id');
    }

    public function paidTotal(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return round(max(0, (float) $this->amount - $this->paidTotal()), 2);
    }

    public function settlementStatusFromPaid(?float $paidSum = null): string
    {
        $paid = $paidSum ?? $this->paidTotal();
        if ($this->remainingAmountFromPaid($paid) <= 0.009) {
            return 'paid';
        }

        return $paid > 0.009 ? 'partial' : 'unpaid';
    }

    public function remainingAmountFromPaid(?float $paidSum = null): float
    {
        $paid = $paidSum ?? $this->paidTotal();

        return round(max(0, (float) $this->amount - $paid), 2);
    }

    public function settlementStatusLabel(?float $paidSum = null): string
    {
        return match ($this->settlementStatusFromPaid($paidSum)) {
            'paid' => __('expenses.status_full_settled'),
            'partial' => __('expenses.status_partial_settled'),
            default => __('expenses.status_unpaid'),
        };
    }

    public function settlementStatusBadgeClasses(?float $paidSum = null): string
    {
        return match ($this->settlementStatusFromPaid($paidSum)) {
            'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300',
            'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/45 dark:text-amber-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function settlementStatusColor(?float $paidSum = null): string
    {
        return match ($this->settlementStatusFromPaid($paidSum)) {
            'paid' => '#10B981',
            'partial' => '#F59E0B',
            default => '#94A3B8',
        };
    }

    public function documentNumber(): string
    {
        return 'EXP-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * مزامنة صف الدفع الوحيد لمصروف «دفعة واحدة» مع حقول الرأس.
     */
    public function syncFullSettlementPaymentRow(): void
    {
        if (! $this->isFullSettlement()) {
            return;
        }

        $payment = $this->payments()->orderBy('id')->first();
        if ($payment === null) {
            $this->payments()->create([
                'amount' => $this->amount,
                'paid_at' => $this->expense_date,
                'payment_method' => $this->payment_method ?? 'cash',
                'notes' => null,
                'created_by' => $this->created_by,
            ]);

            return;
        }

        $payment->update([
            'amount' => $this->amount,
            'paid_at' => $this->expense_date,
            'payment_method' => $this->payment_method ?? 'cash',
        ]);
    }

    /**
     * تحديث payment_method على الرأس لأقساط (عرض/فلترة) من آخر دفعة مسجّلة.
     */
    public function refreshHeaderPaymentMethodFromPayments(): void
    {
        if (! $this->isInstallments()) {
            return;
        }

        $last = $this->payments()->orderByDesc('paid_at')->orderByDesc('id')->first();
        if ($last !== null && $last->payment_method !== $this->payment_method) {
            $this->forceFill(['payment_method' => $last->payment_method])->saveQuietly();
        }
    }

    public static function paymentMethodLabels(): array
    {
        return PaymentMethods::options();
    }

    /**
     * حذف دفعة مع إعادة حساب طريقة الدفع على الرأس عند الحاجة.
     */
    public function deletePayment(ExpensePayment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment->delete();
            if ($this->isInstallments()) {
                $fallback = $this->payments()->orderByDesc('paid_at')->orderByDesc('id')->first();
                $this->forceFill([
                    'payment_method' => $fallback?->payment_method ?? 'other',
                ])->saveQuietly();
            }
        });
    }

    protected static function booted(): void
    {
        static::saving(function (Expense $expense): void {
            if ($expense->expense_category_id) {
                $category = ExpenseCategory::withoutGlobalScopes()
                    ->select(['id', 'clinic_id'])
                    ->find($expense->expense_category_id);
                if ($category && $category->clinic_id) {
                    $expense->clinic_id = $category->clinic_id;
                }
            }
        });
    }

    /**
     * @return BelongsTo<ExpenseCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

