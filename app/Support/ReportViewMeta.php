<?php

namespace App\Support;

use App\Models\Doctor;
use App\Models\ExpenseCategory;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

final class ReportViewMeta
{
    public static function dateRangeLabel(?CarbonInterface $from, ?CarbonInterface $to): string
    {
        if ($from && $to) {
            return __('common.date_range_between', [
                'from' => $from->format('d/m/Y'),
                'to' => $to->format('d/m/Y'),
            ]);
        }
        if ($from) {
            return __('common.date_range_from', ['from' => $from->format('d/m/Y')]);
        }
        if ($to) {
            return __('common.date_range_until', ['to' => $to->format('d/m/Y')]);
        }

        return __('common.date_range_all');
    }

    /**
     * @return list<string>
     */
    public static function expenseFilterLines(Request $request): array
    {
        $lines = [];
        if ($request->filled('report_expense_category_id')) {
            $cat = ExpenseCategory::query()->find($request->integer('report_expense_category_id'));
            $lines[] = __('reports.filter_expense_category_prefix').' '.($cat?->name ?? '#'.$request->integer('report_expense_category_id'));
        }
        if ($request->filled('report_expense_date_from')) {
            $lines[] = __('reports.filter_expense_from_prefix').' '.$request->date('report_expense_date_from')?->format('d/m/Y');
        }
        if ($request->filled('report_expense_date_to')) {
            $lines[] = __('reports.filter_expense_to_prefix').' '.$request->date('report_expense_date_to')?->format('d/m/Y');
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    public static function invoiceFilterLines(Request $request): array
    {
        $lines = [];
        if ($request->filled('date_from')) {
            $lines[] = __('reports.filter_invoices_from_prefix').' '.$request->date('date_from')?->format('d/m/Y');
        }
        if ($request->filled('date_to')) {
            $lines[] = __('reports.filter_invoices_to_prefix').' '.$request->date('date_to')?->format('d/m/Y');
        }
        if ($request->filled('doctor_id')) {
            $doc = Doctor::query()->find($request->integer('doctor_id'));
            $lines[] = __('reports.filter_doctor_prefix').' '.($doc?->full_name ?? '#'.$request->integer('doctor_id'));
        }
        if ($request->filled('status') && in_array($request->input('status'), ['unpaid', 'partial', 'paid'], true)) {
            $lines[] = __('reports.filter_invoice_status_prefix').' '.match ($request->input('status')) {
                'paid' => __('common.paid'),
                'partial' => __('common.partial'),
                'unpaid' => __('common.unpaid'),
                default => (string) $request->input('status'),
            };
        }

        return $lines;
    }

    /**
     * @return array{title: string, date_range_label: string, filter_lines: list<string>}
     */
    public static function forMainReports(Request $request): array
    {
        $from = $request->date('report_date_from');
        $to = $request->date('report_date_to');

        return [
            'title' => __('reports.meta_title_default'),
            'date_range_label' => self::dateRangeLabel($from, $to),
            'filter_lines' => array_merge(
                self::expenseFilterLines($request),
                self::invoiceFilterLines($request),
            ),
        ];
    }
}
