<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use App\Support\AuditLogger;
use App\Support\ClinicFinancialStats;
use App\Support\ClinicPermissions;
use App\Support\ClinicReportCache;
use App\Support\ClinicReportStats;
use App\Support\ClinicSettings;
use App\Support\Excel\ClinicExcelWorkbook;
use App\Support\Excel\ClinicExportMeta;
use App\Support\Excel\XlsxExportResponse;
use App\Support\InventoryReportStats;
use App\Support\PaymentMethods;
use App\Support\Queries\InvoiceListQuery;
use App\Support\ReportViewMeta;
use App\Support\ClinicPdf;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $data = array_merge($this->buildReportData($request), [
            'pageTitle' => __('reports.page_title'),
        ]);

        if ($request->ajax()) {
            return view('reports.partials.content', $data);
        }

        return view('reports.index', $data);
    }

    public function printReport()
    {
        AuditLogger::log(
            'export',
            'reports',
            null,
            __('reports.audit_print_view'),
            null,
            null
        );

        return view('reports.print', array_merge($this->buildReportData(request()), [
            'backUrl' => route('reports.index'),
        ]));
    }

    public function pdfReport()
    {
        AuditLogger::log(
            'export',
            'reports',
            null,
            __('reports.audit_pdf_export'),
            null,
            null
        );

        return ClinicPdf::download(
            'reports.print',
            array_merge($this->buildReportData(request()), [
                'backUrl' => route('reports.index'),
            ]),
            'reports-'.now()->format('Y-m-d_His').'.pdf'
        );
    }

    public function exportExcel(): StreamedResponse
    {
        AuditLogger::log(
            'export',
            'reports',
            null,
            __('reports.audit_excel_export'),
            null,
            null
        );

        $data = $this->buildReportData(request());
        $filename = 'reports-'.now()->format('Y-m-d_His').'.xlsx';

        return XlsxExportResponse::stream($filename, function (Writer $writer) use ($data): void {
            $clinic = $data['clinic'];
            $cur = $clinic->currency ?? '';
            $em = __('common.em_dash');
            $meta = ClinicExportMeta::make(__('reports.page_heading'));
            $wb = new ClinicExcelWorkbook($writer, $meta);
            $summarySheet = $wb->addSheet(__('reports.excel_sheet_summary'));
            $wb->writeBanner(__('reports.page_heading'), 2);
            $wb->writeTableHeader($summarySheet, [__('reports.excel_hdr_indicator'), __('reports.excel_hdr_value')]);
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_clinic_name'), $clinic->clinic_name ?: config('app.name')]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_patients'), $data['totalPatients']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_doctors'), $data['totalDoctors']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_appointments'), $data['totalAppointments']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_visits'), $data['totalVisits']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_invoices'), $data['totalInvoices']]));
            $wb->writeSectionTitle(__('reports.excel_section_accrual'), 2);
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_accrual_revenue'), $this->excelAmount((float) $data['totalAccrualRevenue'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_ar'), $this->excelAmount((float) $data['totalAccountsReceivable'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_doctor_share_accrual'), $this->excelAmount((float) $data['totalDoctorShareExpense'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_gross_profit_accrual'), $this->excelAmount((float) $data['grossProfit'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_expenses_accrual'), $this->excelAmount((float) $data['totalExpensesAll'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_payroll_accrual'), $this->excelAmount((float) $data['totalPayrollPaid'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_operating_cost_accrual'), $this->excelAmount((float) $data['totalOperatingCost'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_net_profit_accrual'), $this->excelAmount((float) $data['netProfit'], $cur)]));
            $wb->writeSectionTitle(__('reports.excel_section_cash'), 2);
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_patient_payments_total'), $this->excelAmount((float) $data['totalPatientCashIn'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_invoice_paid_reference'), $this->excelAmount((float) $data['totalCashCollected'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_total_cash_out'), $this->excelAmount((float) $data['totalCashOutReport'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_net_cash_flow'), $this->excelAmount((float) $data['netCashFlowLifetime'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_cash_till_balance'), $this->excelAmount((float) $data['cashBalance'], $cur)]));
            if (! empty($data['showInventoryReports'])) {
                $wb->writeSectionTitle(__('reports.excel_section_inventory'), 2);
                $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_inventory_valuation'), $this->excelAmount((float) ($data['inventoryValuation'] ?? 0), $cur)]));
                $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_inventory_purchases_month'), $this->excelAmount((float) ($data['inventoryPurchasesThisMonth'] ?? 0), $cur)]));
                $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_inventory_low_stock'), (string) ($data['inventoryLowStockCount'] ?? 0)]));
                $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_inventory_expiring'), (string) ($data['inventoryExpiringCount'] ?? 0)]));
                $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_inventory_skus'), (string) ($data['inventoryTotalSkus'] ?? 0)]));
            }
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_accrual_today'), $this->excelAmount((float) $data['todayAccrualRevenue'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_cash_collect_today'), $this->excelAmount((float) $data['todayCashCollected'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_exp_accrual_today'), $this->excelAmount((float) $data['todayAccrualExpensesRecorded'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_exp_pay_today'), $this->excelAmount((float) $data['todayExpenses'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_pay_accrual_today'), $this->excelAmount((float) $data['todayAccrualPayrollRecorded'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_payroll_out_today'), $this->excelAmount((float) $data['todayPayroll'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_accrual_month'), $this->excelAmount((float) $data['thisMonthAccrualRevenue'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_cash_month'), $this->excelAmount((float) $data['thisMonthCashCollected'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_exp_pay_month'), $this->excelAmount((float) $data['thisMonthExpenses'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_payroll_out_month'), $this->excelAmount((float) $data['thisMonthPayroll'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_net_profit_month'), $this->excelAmount((float) $data['thisMonthProfit'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_net_cf_month'), $this->excelAmount((float) $data['thisMonthNetCashFlowReport'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_net_profit_day'), $this->excelAmount((float) $data['todayNetProfit'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_unpaid_count'), $data['unpaidInvoices']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_unpaid_amount'), $this->excelAmount((float) $data['unpaidAmount'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_partial_count'), $data['partialInvoices']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_partial_remaining'), $this->excelAmount((float) $data['partialAmount'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_paid_count'), $data['paidInvoices']]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_row_paid_amount_total'), $this->excelAmount((float) $data['paidAmount'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_total_cash_out_all_time_title'), $this->excelAmount((float) $data['totalOperatingOutflows'], $cur)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.kpi_receivable_exposure_title'), $this->excelAmount((float) $data['totalReceivableExposure'], $cur)]));
            $wb->writeSectionTitle(__('reports.excel_section_period_compare'), 2);
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_accrual_revenue', 'excel_period_last_month'), round((float) $data['lastMonthAccrualRevenue'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_accrual_revenue', 'excel_period_this_month'), round((float) $data['thisMonthAccrualRevenue'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_accrual_revenue', 'excel_period_ytd'), round((float) $data['ytdAccrualRevenue'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_accrual_revenue', 'excel_period_last_30'), round((float) $data['last30AccrualRevenue'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_cash_patients', 'excel_period_last_month'), round((float) $data['lastMonthCashCollected'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_cash_patients', 'excel_period_this_month'), round((float) $data['thisMonthCashCollected'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_cash_patients', 'excel_period_ytd'), round((float) $data['ytdCashCollected'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_cash_patients', 'excel_period_last_30'), round((float) $data['last30CashCollected'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_share_accrual', 'excel_period_last_month'), round((float) $data['lastMonthDoctorShareExpense'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_share_accrual', 'excel_period_this_month'), round((float) $data['thisMonthDoctorShareExpense'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_share_accrual', 'excel_period_ytd'), round((float) $data['ytdDoctorShareExpense'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_share_accrual', 'excel_period_last_30'), round((float) $data['last30DoctorShareExpense'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expenses_accrual', 'excel_period_last_month'), round((float) $data['lastMonthAccrualExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expenses_accrual', 'excel_period_this_month'), round((float) $data['thisMonthAccrualExpensesRecorded'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expenses_accrual', 'excel_period_ytd'), round((float) $data['ytdAccrualExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expenses_accrual', 'excel_period_last_30'), round((float) $data['last30AccrualExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_accrual', 'excel_period_last_month'), round((float) $data['lastMonthAccrualPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_accrual', 'excel_period_this_month'), round((float) $data['thisMonthAccrualPayrollRecorded'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_accrual', 'excel_period_ytd'), round((float) $data['ytdAccrualPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_accrual', 'excel_period_last_30'), round((float) $data['last30AccrualPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expense_payments_cash', 'excel_period_last_month'), round((float) $data['lastMonthExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expense_payments_cash', 'excel_period_this_month'), round((float) $data['thisMonthExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expense_payments_cash', 'excel_period_ytd'), round((float) $data['ytdExpenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_expense_payments_cash', 'excel_period_last_30'), round((float) $data['last30Expenses'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_disbursement_cash', 'excel_period_last_month'), round((float) $data['lastMonthPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_disbursement_cash', 'excel_period_this_month'), round((float) $data['thisMonthPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_disbursement_cash', 'excel_period_ytd'), round((float) $data['ytdPayroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_payroll_disbursement_cash', 'excel_period_last_30'), round((float) $data['last30Payroll'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_settlements_cash', 'excel_period_last_month'), round((float) $data['lastMonthDoctorPayoutsCash'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_settlements_cash', 'excel_period_this_month'), round((float) $data['thisMonthDoctorPayoutsCash'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_settlements_cash', 'excel_period_ytd'), round((float) $data['ytdDoctorPayoutsCash'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_doctor_settlements_cash', 'excel_period_last_30'), round((float) $data['last30DoctorPayoutsCash'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_total_cash_out', 'excel_period_last_month'), round((float) $data['lastMonthCashOut'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_total_cash_out', 'excel_period_this_month'), round((float) $data['thisMonthCashPaidOutReport'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_total_cash_out', 'excel_period_ytd'), round((float) $data['ytdCashOut'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_total_cash_out', 'excel_period_last_30'), round((float) $data['last30CashOut'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_cash_flow', 'excel_period_last_month'), round((float) $data['lastMonthNetCashFlow'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_cash_flow', 'excel_period_this_month'), round((float) $data['thisMonthNetCashFlowReport'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_cash_flow', 'excel_period_ytd'), round((float) $data['ytdNetCashFlow'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_cash_flow', 'excel_period_last_30'), round((float) $data['last30NetCashFlow'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_profit_accrual', 'excel_period_last_month'), round((float) $data['lastMonthProfit'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_profit_accrual', 'excel_period_this_month'), round((float) $data['thisMonthProfit'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_profit_accrual', 'excel_period_ytd'), round((float) $data['ytdNetProfit'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([$this->xlPeriod('excel_metric_net_profit_accrual', 'excel_period_last_30'), round((float) $data['last30NetProfit'], 2)]));
            $writer->addRow(XlsxExportResponse::dataRow([__('reports.excel_exported_at'), $data['generatedAt']->format('d/m/Y H:i')]));

            $wb->writeSectionTitle(__('reports.excel_heading_income_by_pm'), 2);
            foreach ($data['incomeByPaymentMethod'] as $code => $amt) {
                $writer->addRow(XlsxExportResponse::dataRow([
                    $this->paymentMethodLabelForExport((string) $code),
                    round((float) $amt, 2),
                ]));
            }

            $wb->writeSectionTitle(__('reports.excel_heading_expense_by_pm'), 2);
            foreach ($data['expenseOutflowsByPaymentMethod'] as $code => $amt) {
                $writer->addRow(XlsxExportResponse::dataRow([
                    $this->paymentMethodLabelForExport((string) $code),
                    round((float) $amt, 2),
                ]));
            }

            $this->writeReportsTableSheet($wb, __('reports.excel_sheet_recent_patients'), [
                __('reports.excel_th_full_name'),
                __('reports.excel_th_phone'),
                __('reports.excel_th_registered'),
            ], $data['recentPatients'], function ($patient) use ($em): array {
                return [
                    $patient->full_name,
                    $patient->phone ?? $em,
                    $patient->created_at?->format('d/m/Y'),
                ];
            });

            $this->writeReportsTableSheet($wb, __('reports.excel_sheet_recent_appointments'), [
                __('appointments.excel_th_patient'),
                __('appointments.excel_th_doctor'),
                __('appointments.field_appointment_date'),
                __('appointments.excel_th_created_at'),
            ], $data['recentAppointments'], function ($appointment) use ($em): array {
                return [
                    optional($appointment->patient)->full_name ?? $em,
                    optional($appointment->doctor)->full_name ?? $em,
                    $appointment->appointment_date?->format('d/m/Y'),
                    $appointment->created_at?->format('d/m/Y H:i'),
                ];
            });

            $this->writeReportsTableSheet($wb, __('reports.excel_sheet_recent_invoices'), [
                __('invoices.field_invoice_number'),
                __('invoices.field_patient'),
                __('invoices.field_total'),
                __('invoices.field_status'),
            ], $data['recentInvoices'], function ($invoice) use ($em): array {
                return [
                    $invoice->invoice_number,
                    optional($invoice->patient)->full_name ?? $em,
                    round((float) $invoice->total, 2),
                    $this->invoiceStatusLabelForExport($invoice->status),
                ];
            });

            $this->writeReportsTableSheet($wb, __('reports.excel_sheet_recent_payments'), [
                __('invoices.field_patient'),
                __('payments.reports_th_invoice'),
                __('common.amount'),
                __('payments.reports_th_method'),
                __('payments.reports_th_payment_date'),
            ], $data['recentPayments'], function ($payment) use ($em): array {
                return [
                    optional(optional($payment->invoice)->patient)->full_name ?? $em,
                    optional($payment->invoice)->invoice_number ?? $em,
                    round((float) $payment->amount, 2),
                    $this->paymentMethodLabelForExport($payment->payment_method ?? ''),
                    $payment->payment_date?->format('d/m/Y') ?? $em,
                ];
            });

            $this->writeReportsTableSheet($wb, __('reports.excel_sheet_expense_summary'), [
                __('expenses.field_category'),
                __('reports.pdf_th_total_short'),
            ], $data['expenseSummaryByCategoryAllTime'], function ($row): array {
                return [
                    $row->category_name,
                    round((float) $row->total_amount, 2),
                ];
            });

            $invoiceExportRequest = Request::create('/reports/excel', 'GET', request()->only([
                'date_from', 'date_to', 'doctor_id', 'status', 'invoice_number', 'patient',
            ]));
            $invoiceExportQuery = InvoiceListQuery::fromRequest($invoiceExportRequest);
            $invoiceCountAgg = (clone $invoiceExportQuery)->count();
            $sumTotalAgg = (float) (clone $invoiceExportQuery)->sum('total');
            $sumPaidAgg = (float) (clone $invoiceExportQuery)->sum('paid');
            $aggRemaining = round($sumTotalAgg - $sumPaidAgg, 2);

            $invoiceHeaders = [
                __('reports.excel_inv_row_date'),
                __('invoices.field_patient'),
                __('invoices.field_doctor'),
                __('invoices.field_invoice_number'),
                __('invoices.field_total'),
                __('invoices.field_paid'),
                __('invoices.field_remaining'),
                __('invoices.field_status'),
            ];
            $filteredSheet = $wb->addSheet(__('reports.excel_sheet_invoices_filtered'));
            $wb->writeBanner(__('reports.excel_sheet_invoices_filtered'), count($invoiceHeaders));
            $wb->writeSectionTitle(__('reports.excel_invoice_filter_summary_title'), count($invoiceHeaders));
            $wb->writeKeyValueBlock([
                [__('reports.excel_invoice_count'), $invoiceCountAgg],
                [__('reports.excel_invoice_sum_total'), round($sumTotalAgg, 2)],
                [__('reports.excel_invoice_sum_paid'), round($sumPaidAgg, 2)],
                [__('reports.excel_invoice_sum_remaining'), $aggRemaining],
            ]);
            $wb->writeSectionTitle(__('reports.excel_section_margin_ref'), count($invoiceHeaders));
            $wb->writeKeyValueBlock([
                [__('reports.excel_accrual_whole_clinic'), round((float) $data['totalAccrualRevenue'], 2)],
                [__('reports.excel_net_profit_whole_clinic'), round((float) $data['netProfit'], 2)],
            ]);
            $wb->writeTableHeader($filteredSheet, $invoiceHeaders);
            $i = 0;
            foreach ($invoiceExportQuery->orderByDesc('created_at')->cursor() as $inv) {
                $remaining = round((float) $inv->total - (float) $inv->paid, 2);
                $wb->writeDataRow([
                    $inv->created_at?->format('d/m/Y'),
                    optional($inv->patient)->full_name ?? $em,
                    optional($inv->treatingDoctor)->full_name ?? $em,
                    $inv->invoice_number,
                    round((float) $inv->total, 2),
                    round((float) $inv->paid, 2),
                    $remaining,
                    $this->invoiceStatusLabelForExport($inv->status),
                ], $i++);
            }
            $wb->finishCurrentTable($filteredSheet, count($invoiceHeaders));
        });
    }

    /**
     * @param  iterable<mixed>  $rows
     * @param  callable(mixed): list<int|string|float|null>  $mapRow
     */
    private function writeReportsTableSheet(
        ClinicExcelWorkbook $wb,
        string $sheetName,
        array $headers,
        iterable $rows,
        callable $mapRow,
    ): void {
        $sheet = $wb->beginTableSheet($sheetName, $sheetName, $headers);
        $i = 0;
        foreach ($rows as $row) {
            $wb->writeDataRow($mapRow($row), $i++);
        }
        $wb->finishCurrentTable($sheet, count($headers));
    }

    private function invoiceStatusLabelForExport(?string $status): string
    {
        return match ($status) {
            'paid' => __('common.paid'),
            'partial' => __('common.partial'),
            'unpaid' => __('common.unpaid'),
            default => (string) $status,
        };
    }

    private function xlPeriod(string $metricKey, string $periodKey): string
    {
        return __('reports.excel_row_labeled_period', [
            'metric' => __('reports.'.$metricKey),
            'period' => __('reports.'.$periodKey),
        ]);
    }

    private function excelAmount(?float $v, string $cur): string
    {
        return round((float) $v, 2).($cur !== '' ? ' '.$cur : '');
    }

    private function paymentMethodLabelForExport(string $method): string
    {
        return PaymentMethods::label($method);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildReportData(?Request $request = null): array
    {
        $request = $request ?? request();

        $payload = ClinicReportCache::rememberReportData($request, fn (): array => $this->computeReportData($request));
        // Avoid stale/serialized model instances from cache; always hydrate clinic settings live.
        $payload['clinic'] = ClinicSettings::current();
        $payload['cur'] = $payload['clinic']->currency ?? '';
        $payload['generatedAt'] = now();

        $user = $request->user();
        $payload['showInventoryReports'] = $user !== null
            && ($user->can(ClinicPermissions::VIEW_INVENTORY) || $user->can(ClinicPermissions::MANAGE_INVENTORY));

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function computeReportData(Request $request): array
    {
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalAppointments = Appointment::count();
        $totalVisits = Visit::count();
        $totalInvoices = Invoice::count();
        $totalAccrualRevenue = ClinicFinancialStats::totalAccrualRevenue();
        $totalCashCollected = ClinicFinancialStats::totalCashCollected();
        $totalAccountsReceivable = ClinicFinancialStats::totalAccountsReceivable();
        $totalDoctorShareExpense = ClinicFinancialStats::totalDoctorShareExpense();
        $totalOperatingExpensesKpi = ClinicFinancialStats::totalOperatingExpensesOther();
        $grossProfit = ClinicFinancialStats::grossProfit();
        $totalOperatingCost = ClinicFinancialStats::totalOperatingCost();
        $accrualCashArDelta = ClinicFinancialStats::accrualCashArDelta();
        $totalPatientCashIn = ClinicFinancialStats::totalPatientCashIn();
        $totalIncome = $totalPatientCashIn;
        $totalRevenue = $totalAccrualRevenue;
        $totalPayrollPaid = ClinicFinancialStats::totalPayrollPaid();
        $todayAccrualRevenue = ClinicFinancialStats::todayAccrualRevenue();
        $todayCashCollected = ClinicFinancialStats::todayCashCollected();
        $todayDoctorShareExpense = ClinicFinancialStats::todayDoctorShareExpense();
        $todayIncome = $todayCashCollected;
        $todayPayroll = ClinicFinancialStats::todayPayroll();
        $thisMonthAccrualRevenue = ClinicFinancialStats::thisMonthAccrualRevenue();
        $thisMonthCashCollected = ClinicFinancialStats::thisMonthCashCollected();
        $thisMonthDoctorShareExpense = ClinicFinancialStats::thisMonthDoctorShareExpense();
        $thisMonthIncome = $thisMonthCashCollected;
        $thisMonthExpenses = ClinicFinancialStats::thisMonthExpenses();
        $thisMonthPayroll = ClinicFinancialStats::thisMonthPayroll();
        $thisMonthProfit = ClinicFinancialStats::thisMonthProfit();
        $todayNetProfit = ClinicFinancialStats::todayNetProfit();
        $todayGrossProfit = ClinicFinancialStats::todayGrossProfit();
        $thisMonthGrossProfit = ClinicFinancialStats::grossProfitBetween(now()->copy()->startOfMonth(), now()->copy()->endOfMonth());
        $todayOperatingCostFull = ClinicFinancialStats::todayOperatingCost();
        $thisMonthOperatingCostFull = ClinicFinancialStats::operatingCostBetween(now()->copy()->startOfMonth(), now()->copy()->endOfMonth());
        $todayAccrualExpensesRecorded = ClinicFinancialStats::todayAccrualExpensesRecorded();
        $todayAccrualPayrollRecorded = ClinicFinancialStats::todayAccrualPayrollRecorded();
        $thisMonthAccrualExpensesRecorded = ClinicFinancialStats::thisMonthAccrualExpensesRecorded();
        $thisMonthAccrualPayrollRecorded = ClinicFinancialStats::thisMonthAccrualPayrollRecorded();
        $todayDoctorPayoutsCash = ClinicFinancialStats::todayDoctorPayoutsCash();
        $thisMonthDoctorPayoutsCash = ClinicFinancialStats::thisMonthDoctorPayoutsCash();
        $todayCashPaidOutReport = ClinicFinancialStats::todayCashPaidOut();
        $thisMonthCashPaidOutReport = ClinicFinancialStats::thisMonthCashPaidOut();
        $todayNetCashFlowReport = ClinicFinancialStats::todayNetCashFlow();
        $thisMonthNetCashFlowReport = ClinicFinancialStats::thisMonthNetCashFlow();
        $totalCashOutReport = ClinicFinancialStats::totalCashOut();
        $netCashFlowLifetime = ClinicFinancialStats::netCashFlowLifetime();
        $cashBalance = ClinicFinancialStats::cashBalance();
        $ytdAccrualExpenses = ClinicFinancialStats::yearToDateAccrualExpenses();
        $ytdAccrualPayroll = ClinicFinancialStats::yearToDateAccrualPayroll();
        $ytdCashOut = ClinicFinancialStats::yearToDateCashOut();
        $ytdNetCashFlow = ClinicFinancialStats::yearToDateNetCashFlow();
        $lastMonthAccrualExpenses = ClinicFinancialStats::lastMonthAccrualExpenses();
        $lastMonthAccrualPayroll = ClinicFinancialStats::lastMonthAccrualPayroll();
        $lastMonthCashOut = ClinicFinancialStats::lastMonthCashOut();
        $lastMonthNetCashFlow = ClinicFinancialStats::lastMonthNetCashFlow();
        $last30AccrualExpenses = ClinicFinancialStats::last30DaysAccrualExpenses();
        $last30AccrualPayroll = ClinicFinancialStats::last30DaysAccrualPayroll();
        $last30CashOut = ClinicFinancialStats::last30DaysCashOut();
        $last30NetCashFlow = ClinicFinancialStats::last30DaysNetCashFlow();
        $lastMonthDoctorPayoutsCash = ClinicFinancialStats::lastMonthDoctorPayoutsCash();
        $ytdDoctorPayoutsCash = ClinicFinancialStats::yearToDateDoctorPayoutsCash();
        $last30DoctorPayoutsCash = ClinicFinancialStats::last30DaysDoctorPayoutsCash();
        $profitabilitySnapshot = ClinicFinancialStats::getAccrualMetrics();
        $cashFlowSnapshot = ClinicFinancialStats::getCashFlowMetrics();

        $invoiceCounts = ClinicReportStats::invoiceCountsByStatus();
        $unpaidInvoices = $invoiceCounts['unpaid'];
        $partialInvoices = $invoiceCounts['partial'];
        $paidInvoices = $invoiceCounts['paid'];

        $amounts = ClinicReportStats::invoiceAmountsByStatus();
        $unpaidAmount = $amounts['unpaid_amount'];
        $partialAmount = $amounts['partial_amount'];
        $paidAmount = $amounts['paid_amount'];

        $recentPatients = Patient::query()
            ->select(['id', 'full_name', 'phone', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        $recentAppointments = Appointment::query()
            ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'created_at'])
            ->with([
                'patient:id,full_name',
                'doctor:id,full_name',
            ])
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = Invoice::query()
            ->select(['id', 'invoice_number', 'patient_id', 'total', 'status', 'created_at'])
            ->with(['patient:id,full_name'])
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payment::query()
            ->select(['id', 'invoice_id', 'amount', 'payment_method', 'payment_date', 'created_at'])
            ->with([
                'invoice:id,invoice_number,patient_id',
                'invoice.patient:id,full_name',
            ])
            ->latest()
            ->take(5)
            ->get();

        $totalExpensesAll = ClinicFinancialStats::totalExpenses();
        $todayExpenses = ClinicFinancialStats::todayExpenses();
        $netProfit = ClinicFinancialStats::netProfit();
        $totalOperatingOutflows = ClinicFinancialStats::totalOperatingOutflows();

        $lastMonthAccrualRevenue = ClinicFinancialStats::lastMonthAccrualRevenue();
        $lastMonthCashCollected = ClinicFinancialStats::lastMonthCashCollected();
        $lastMonthDoctorShareExpense = ClinicFinancialStats::lastMonthDoctorShareExpense();
        $lastMonthIncome = $lastMonthCashCollected;
        $lastMonthExpenses = ClinicFinancialStats::lastMonthExpenses();
        $lastMonthPayroll = ClinicFinancialStats::lastMonthPayroll();
        $lastMonthProfit = ClinicFinancialStats::lastMonthProfit();
        $lastMonthGrossProfit = ClinicFinancialStats::lastMonthGrossProfit();
        $lastMonthOperatingCostFull = ClinicFinancialStats::lastMonthOperatingCost();

        $ytdAccrualRevenue = ClinicFinancialStats::yearToDateAccrualRevenue();
        $ytdCashCollected = ClinicFinancialStats::yearToDateCashCollected();
        $ytdDoctorShareExpense = ClinicFinancialStats::yearToDateDoctorShareExpense();
        $ytdIncome = $ytdCashCollected;
        $ytdExpenses = ClinicFinancialStats::yearToDateExpenses();
        $ytdPayroll = ClinicFinancialStats::yearToDatePayroll();
        $ytdNetProfit = ClinicFinancialStats::yearToDateNetProfit();
        $ytdGrossProfit = ClinicFinancialStats::yearToDateGrossProfit();
        $ytdOperatingCostFull = ClinicFinancialStats::yearToDateOperatingCost();

        $last30AccrualRevenue = ClinicFinancialStats::last30DaysAccrualRevenue();
        $last30CashCollected = ClinicFinancialStats::last30DaysCashCollected();
        $last30DoctorShareExpense = ClinicFinancialStats::last30DaysDoctorShareExpense();
        $last30Income = $last30CashCollected;
        $last30Expenses = ClinicFinancialStats::last30DaysExpenses();
        $last30Payroll = ClinicFinancialStats::last30DaysPayroll();
        $last30NetProfit = ClinicFinancialStats::last30DaysNetProfit();
        $last30GrossProfit = ClinicFinancialStats::last30DaysGrossProfit();
        $last30OperatingCostFull = ClinicFinancialStats::last30DaysOperatingCost();

        $monthlyFinancialTrend = ClinicFinancialStats::monthlyFinancialTrend(12);
        $incomeByPaymentMethod = PaymentMethods::orderedTotalsWithZeros(
            ClinicFinancialStats::incomeTotalsByPaymentMethod()
        );
        $expenseOutflowsByPaymentMethod = PaymentMethods::orderedTotalsWithZeros(
            ClinicFinancialStats::expensePaymentTotalsByPaymentMethod()
        );

        $totalReceivableExposure = round((float) $unpaidAmount + (float) $partialAmount, 2);

        $expenseFilterQuery = Expense::query()
            ->when($request->filled('report_expense_category_id'), function ($q) use ($request) {
                $q->where('expense_category_id', $request->integer('report_expense_category_id'));
            })
            ->when($request->filled('report_expense_date_from'), function ($q) use ($request) {
                $q->whereDate('expense_date', '>=', $request->date('report_expense_date_from'));
            })
            ->when($request->filled('report_expense_date_to'), function ($q) use ($request) {
                $q->whereDate('expense_date', '<=', $request->date('report_expense_date_to'));
            });

        $expenseFilteredTotal = (float) (clone $expenseFilterQuery)->sum('amount');

        $expenseSummaryByCategory = (clone $expenseFilterQuery)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount) as total_amount')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderBy('expense_categories.name')
            ->get();

        $expenseSummaryByCategoryAllTime = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount) as total_amount')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderBy('expense_categories.name')
            ->get();

        $expenseCategoriesForReportFilter = ExpenseCategory::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $clinic = ClinicSettings::current();

        return array_merge(InventoryReportStats::snapshot(), [
            'reportMeta' => ReportViewMeta::forMainReports($request),
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'totalPatients' => $totalPatients,
            'totalDoctors' => $totalDoctors,
            'totalAppointments' => $totalAppointments,
            'totalVisits' => $totalVisits,
            'totalInvoices' => $totalInvoices,
            'totalRevenue' => $totalRevenue,
            'totalAccrualRevenue' => $totalAccrualRevenue,
            'totalCashCollected' => $totalCashCollected,
            'totalAccountsReceivable' => $totalAccountsReceivable,
            'totalDoctorShareExpense' => $totalDoctorShareExpense,
            'totalOperatingExpensesKpi' => $totalOperatingExpensesKpi,
            'grossProfit' => $grossProfit,
            'totalOperatingCost' => $totalOperatingCost,
            'accrualCashArDelta' => $accrualCashArDelta,
            'totalIncome' => $totalIncome,
            'totalPatientCashIn' => $totalPatientCashIn,
            'totalPayrollPaid' => $totalPayrollPaid,
            'todayAccrualRevenue' => $todayAccrualRevenue,
            'todayCashCollected' => $todayCashCollected,
            'todayDoctorShareExpense' => $todayDoctorShareExpense,
            'todayIncome' => $todayIncome,
            'todayPayroll' => $todayPayroll,
            'thisMonthAccrualRevenue' => $thisMonthAccrualRevenue,
            'thisMonthCashCollected' => $thisMonthCashCollected,
            'thisMonthDoctorShareExpense' => $thisMonthDoctorShareExpense,
            'thisMonthIncome' => $thisMonthIncome,
            'thisMonthExpenses' => $thisMonthExpenses,
            'thisMonthPayroll' => $thisMonthPayroll,
            'thisMonthProfit' => $thisMonthProfit,
            'todayNetProfit' => $todayNetProfit,
            'todayGrossProfit' => $todayGrossProfit,
            'thisMonthGrossProfit' => $thisMonthGrossProfit,
            'todayOperatingCostFull' => $todayOperatingCostFull,
            'thisMonthOperatingCostFull' => $thisMonthOperatingCostFull,
            'profitabilitySnapshot' => $profitabilitySnapshot,
            'cashFlowSnapshot' => $cashFlowSnapshot,
            'todayAccrualExpensesRecorded' => $todayAccrualExpensesRecorded,
            'todayAccrualPayrollRecorded' => $todayAccrualPayrollRecorded,
            'thisMonthAccrualExpensesRecorded' => $thisMonthAccrualExpensesRecorded,
            'thisMonthAccrualPayrollRecorded' => $thisMonthAccrualPayrollRecorded,
            'todayDoctorPayoutsCash' => $todayDoctorPayoutsCash,
            'thisMonthDoctorPayoutsCash' => $thisMonthDoctorPayoutsCash,
            'todayCashPaidOutReport' => $todayCashPaidOutReport,
            'thisMonthCashPaidOutReport' => $thisMonthCashPaidOutReport,
            'todayNetCashFlowReport' => $todayNetCashFlowReport,
            'thisMonthNetCashFlowReport' => $thisMonthNetCashFlowReport,
            'totalCashOutReport' => $totalCashOutReport,
            'netCashFlowLifetime' => $netCashFlowLifetime,
            'cashBalance' => $cashBalance,
            'ytdAccrualExpenses' => $ytdAccrualExpenses,
            'ytdAccrualPayroll' => $ytdAccrualPayroll,
            'ytdCashOut' => $ytdCashOut,
            'ytdNetCashFlow' => $ytdNetCashFlow,
            'lastMonthAccrualExpenses' => $lastMonthAccrualExpenses,
            'lastMonthAccrualPayroll' => $lastMonthAccrualPayroll,
            'lastMonthCashOut' => $lastMonthCashOut,
            'lastMonthNetCashFlow' => $lastMonthNetCashFlow,
            'last30AccrualExpenses' => $last30AccrualExpenses,
            'last30AccrualPayroll' => $last30AccrualPayroll,
            'last30CashOut' => $last30CashOut,
            'last30NetCashFlow' => $last30NetCashFlow,
            'lastMonthDoctorPayoutsCash' => $lastMonthDoctorPayoutsCash,
            'ytdDoctorPayoutsCash' => $ytdDoctorPayoutsCash,
            'last30DoctorPayoutsCash' => $last30DoctorPayoutsCash,
            'unpaidInvoices' => $unpaidInvoices,
            'partialInvoices' => $partialInvoices,
            'paidInvoices' => $paidInvoices,
            'unpaidAmount' => $unpaidAmount,
            'partialAmount' => $partialAmount,
            'paidAmount' => $paidAmount,
            'recentPatients' => $recentPatients,
            'recentAppointments' => $recentAppointments,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'totalExpensesAll' => $totalExpensesAll,
            'todayExpenses' => $todayExpenses,
            'netProfit' => $netProfit,
            'totalOperatingOutflows' => $totalOperatingOutflows,
            'lastMonthAccrualRevenue' => $lastMonthAccrualRevenue,
            'lastMonthCashCollected' => $lastMonthCashCollected,
            'lastMonthDoctorShareExpense' => $lastMonthDoctorShareExpense,
            'lastMonthIncome' => $lastMonthIncome,
            'lastMonthExpenses' => $lastMonthExpenses,
            'lastMonthPayroll' => $lastMonthPayroll,
            'lastMonthProfit' => $lastMonthProfit,
            'lastMonthGrossProfit' => $lastMonthGrossProfit,
            'lastMonthOperatingCostFull' => $lastMonthOperatingCostFull,
            'ytdAccrualRevenue' => $ytdAccrualRevenue,
            'ytdCashCollected' => $ytdCashCollected,
            'ytdDoctorShareExpense' => $ytdDoctorShareExpense,
            'ytdIncome' => $ytdIncome,
            'ytdExpenses' => $ytdExpenses,
            'ytdPayroll' => $ytdPayroll,
            'ytdNetProfit' => $ytdNetProfit,
            'ytdGrossProfit' => $ytdGrossProfit,
            'ytdOperatingCostFull' => $ytdOperatingCostFull,
            'last30AccrualRevenue' => $last30AccrualRevenue,
            'last30CashCollected' => $last30CashCollected,
            'last30DoctorShareExpense' => $last30DoctorShareExpense,
            'last30Income' => $last30Income,
            'last30Expenses' => $last30Expenses,
            'last30Payroll' => $last30Payroll,
            'last30NetProfit' => $last30NetProfit,
            'last30GrossProfit' => $last30GrossProfit,
            'last30OperatingCostFull' => $last30OperatingCostFull,
            'monthlyFinancialTrend' => $monthlyFinancialTrend,
            'incomeByPaymentMethod' => $incomeByPaymentMethod,
            'expenseOutflowsByPaymentMethod' => $expenseOutflowsByPaymentMethod,
            'totalReceivableExposure' => $totalReceivableExposure,
            'expenseFilteredTotal' => $expenseFilteredTotal,
            'expenseSummaryByCategory' => $expenseSummaryByCategory,
            'expenseSummaryByCategoryAllTime' => $expenseSummaryByCategoryAllTime,
            'expenseCategoriesForReportFilter' => $expenseCategoriesForReportFilter,
            'reportExpenseCategoryId' => $request->input('report_expense_category_id'),
            'reportExpenseDateFrom' => $request->input('report_expense_date_from'),
            'reportExpenseDateTo' => $request->input('report_expense_date_to'),
        ]);
    }
}
