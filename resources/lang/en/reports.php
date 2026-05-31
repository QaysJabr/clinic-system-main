<?php

return [
    /* Core navigation & hero */
    'title' => 'Reports',
    'page_title' => 'Reports & analytics',
    'page_heading' => 'Reports & analytics',
    'hero_tag' => 'Reports',
    'hero_subtitle' => 'Full clinic snapshot — figures from live data',
    'last_updated_at' => 'Last updated:',
    'print_generated_at_label' => 'Printed at:',
    'pdf_fallback_clinic' => 'Clinic system',

    'nav_aria' => 'Reports navigation',
    'nav_financial' => 'Financial overview',
    'nav_receivables' => 'AR',
    'nav_print' => 'Print reports',
    'nav_pdf' => 'Export PDF',
    'nav_excel' => 'Export Excel',
    'nav_receivables_full' => 'Receivables & balances',
    'nav_inventory' => 'Inventory',

    'section_inventory' => 'Inventory & supplies (assets)',
    'kpi_inventory_valuation' => 'Inventory valuation',
    'kpi_inventory_valuation_hint' => 'Estimated value: quantity on hand × unit cost (active items).',
    'kpi_inventory_purchases_month' => 'Stock purchases (this month)',
    'kpi_inventory_purchases_month_hint' => 'Total received purchase orders posted this month.',
    'kpi_inventory_low_stock' => 'Low-stock items',
    'kpi_inventory_expiring' => 'Expiring soon (30 days)',
    'kpi_inventory_skus' => 'Active SKUs',
    'kpi_inventory_skus_label' => 'active items',
    'row_inventory_valuation' => 'Inventory valuation (on hand)',
    'row_inventory_valuation_hint' => 'Asset snapshot — not included in net profit above.',
    'link_inventory_detail' => 'Inventory reports →',
    'excel_section_inventory' => 'Inventory',

    'section_filter' => 'Filters',
    'section_receivables_list' => 'Receivable invoices',
    'receivables_filter_heading' => 'Filter receivables',
    'receivables_stat_total' => 'Total remaining',
    'receivables_stat_invoices' => 'Matching invoices',
    'receivables_stat_unpaid' => 'Unpaid',
    'receivables_stat_partial' => 'Partially paid',
    'receivables_empty_hint' => 'Try adjusting the date range or doctor filter.',
    'back_to_financial' => 'Back to financial reports',
    'doctor_invoices_section' => 'Invoices in period',
    'doctor_invoices_count' => ':count invoice(s)',
    'doctor_invoices_empty' => 'No invoices in this period.',

    'section_profitability' => 'Profitability (accrual)',
    'section_cash_flow' => 'Cash flow (liquidity)',
    'section_extended' => 'Extended financial summaries',
    'section_period_comparison' => 'Period comparison',
    'section_monthly_trend' => 'Monthly trend (last 12 months)',
    'section_monthly_trend_hint' => 'Profitability columns = accrual only; cash columns = actual payments. Each row is a calendar month; the current month is month-to-date.',
    'section_expense_filter' => 'Expense summary filter',
    'section_expense_distribution' => 'Expense distribution by category',
    'section_recent_activity' => 'Recent activity',
    'section_report_footer_heading' => 'Report footer',
    'section_quick_links' => 'Quick links',

    'badge_accrual' => 'Accrual',
    'badge_cash' => 'Cash',

    /* KPI — profitability */
    'kpi_total_accrual_revenue' => 'Total revenue (accrual)',
    'kpi_total_accrual_revenue_hint' => 'Sum of invoice totals (paid or not).',
    'kpi_accounts_receivable' => 'Accounts receivable (AR)',
    'kpi_accounts_receivable_hint' => 'max(0, total − paid) per unpaid/partial invoices.',
    'kpi_doctor_share' => 'Doctor shares (expense)',
    'kpi_doctor_share_hint' => 'Invoice total × percentage (accrual).',
    'kpi_doctor_share_table' => 'Doctor shares (expense)',
    'kpi_doctor_share_table_short' => 'Doctor shares',
    'kpi_gross_profit' => 'Gross profit',
    'kpi_gross_profit_hint' => 'Accrual revenue − doctor shares.',
    'kpi_net_profit' => 'Net profit',
    'kpi_net_profit_hint' => 'Accrual revenue − operating cost (accrual).',
    'kpi_net_profit_accrual' => 'Net profit (accrual)',
    'kpi_total_operating_cost_accrual' => 'Operating cost (accrual)',

    /* KPI — cash */
    'kpi_patient_cash_in' => 'Cash in from patients (payments)',
    'kpi_patient_cash_in_hint' => 'Sum of payments.amount — not used as accrual revenue.',
    'kpi_total_cash_out' => 'Total cash out',
    'kpi_net_cash_flow_cumulative' => 'Net cash flow (cumulative)',
    'kpi_net_cash_flow_cumulative_hint' => 'Cash in − cash out since inception.',
    'kpi_cash_balance' => 'Cash balance',
    'kpi_cash_balance_hint' => 'Opening balance + cumulative net cash flow.',
    'kpi_cash_balance_detailed' => 'Cash balance (opening + cumulative)',
    'kpi_cash_balance_settings_hint' => 'Opening balance from settings + cumulative net cash flow.',

    'cross_check_invoice_paid_vs_payments' => 'Cross-check: total «Paid» on invoices = :amount — may differ slightly from the payments ledger if adjustments exist.',

    /* Subsections */
    'subsection_profitability_summary' => 'Profitability snapshot (accrual — today / month / total)',
    'subsection_cashflow_summary' => 'Cash flow snapshot (liquidity — today / month / total)',
    'kpi_total_cash_out_all_time_title' => 'Total cash out (all time)',
    'kpi_receivable_exposure_title' => 'Total receivable exposure',
    'kpi_receivable_exposure_hint' => 'Unpaid + remaining on partial invoices',
    'kpi_ytd_net_profit' => 'Net profit year to date',
    'kpi_cash_till' => 'Cash till',

    /* Table columns */
    'th_metric' => 'Metric',
    'th_today' => 'Today',
    'th_this_month' => 'This month',
    'th_grand_total' => 'Grand total',
    'th_last_month' => 'Last month',
    'th_ytd' => 'Year to date',
    'th_last_30_days' => 'Last 30 days',

    /* Row labels shared */
    'row_accrual_revenue_detail' => 'Accrual revenue',
    'row_cash_patients_detail' => 'Cash in from patients (payments)',
    'row_doctor_payouts_cash' => 'Doctor settlements (cash)',
    'row_net_profit_plain' => 'Net profit',
    'row_doctor_share_expense_comparison' => 'Doctor shares (expense)',
    'row_total_operating_cost_accrual' => 'Operating cost (accrual)',
    'row_doctor_share_table' => 'Doctor shares (expense)',

    /* Print view short labels */
    'print_short_accrual_revenue' => 'Revenue (accrual)',
    'print_short_ar' => 'AR',
    /* PDF cards */
    'pdf_tag' => 'Reports',
    'pdf_headline_sub' => 'Reports & analytics — full clinic snapshot',
    'pdf_section_profitability' => 'Profitability (accrual)',
    'pdf_section_cash' => 'Cash flow',
    'pdf_section_profitability_table' => 'Profitability snapshot (accrual)',
    'pdf_section_cash_table' => 'Cash flow snapshot',
    'pdf_section_extended_cards' => 'Extended financial summaries',
    'pdf_cash_all_time_out' => 'Cash out (all time)',
    'pdf_receivable_due' => 'Receivable due',
    'pdf_ytd_net_profit' => 'Net profit YTD (accrual)',
    'pdf_cash_balance_short' => 'Cash balance',

    /* PDF — invoice summary hints */
    'pdf_invoice_cards_title' => 'Invoice financial summary',
    'pdf_invoice_unpaid_total' => 'Total unpaid invoices',
    'pdf_invoice_unpaid_hint' => 'Sum of totals for «unpaid» invoices',
    'pdf_invoice_partial_remaining' => 'Remaining (partial invoices)',
    'pdf_invoice_partial_formula_hint' => 'Total (invoice total − paid)',
    'pdf_invoice_paid_full' => 'Fully paid invoices',
    'pdf_invoice_paid_hint' => 'Sum of totals for «paid» invoices',

    /* General KPI row */
    'kpi_heading_general' => 'General indicators',

    /* PDF status distribution */
    'pdf_section_invoice_status' => 'Invoices by status',
    'pdf_status_heading_unpaid' => 'Unpaid',
    'pdf_status_heading_partial' => 'Partially paid',
    'pdf_status_heading_paid' => 'Fully paid',

    /* Common table */
    'pdf_th_method' => 'Method',
    'pdf_th_amount' => 'Amount',
    'pdf_th_month' => 'Month',
    'pdf_th_operation_accrual' => 'Operating (accrual)',
    'pdf_th_collect_cash_short' => 'Collections (cash)',
    'pdf_th_out_cash_short' => 'Out (cash)',
    'pdf_th_net_flow_short' => 'Net flow',

    'month_label_format' => 'F Y',

    /* Expense filter form */
    'expense_filter_heading' => 'Expense summary criteria',
    'filter_category' => 'Category',
    'filter_all' => 'All',
    'filter_date_from' => 'From date',
    'filter_date_to' => 'To date',
    'btn_apply' => 'Apply',
    'btn_reset' => 'Reset',
    'expense_filtered_total_line' => 'Total expenses matching filter:',
    'expense_distribution_with_filter_suffix' => ' (matching filter)',
    /* Meta filter lines built in ReportViewMeta */
    'meta_title_default' => 'Reports & analytics',
    'filter_expense_category_prefix' => 'Expense category:',
    'filter_expense_from_prefix' => 'Expenses from:',
    'filter_expense_to_prefix' => 'Expenses to:',
    'filter_invoices_from_prefix' => 'Invoices from:',
    'filter_invoices_to_prefix' => 'Invoices to:',
    'filter_doctor_prefix' => 'Doctor:',
    'filter_invoice_status_prefix' => 'Invoice status:',
    /* Audit */
    'audit_print_view' => 'Open print preview — reports',
    'audit_pdf_export' => 'Export PDF — reports',
    'audit_excel_export' => 'Export Excel — reports & analytics',

    /* Footer / recent blocks */
    'recent_patients_heading' => 'Last 5 patients',
    'recent_appointments_heading' => 'Last 5 appointments',
    'recent_patients_th_name' => 'Patient',
    'recent_patients_th_phone' => 'Phone',
    'recent_patients_th_registered' => 'Registered',
    'recent_appointments_th_patient' => 'Patient',
    'recent_appointments_th_doctor' => 'Doctor',
    'recent_appointments_th_date' => 'Date',

    /* Empty states */
    'empty_no_matching_expenses' => 'No matching expenses.',
    'empty_no_data' => 'No data',
    /* Quick links dashboard */
    'quick_dashboard' => 'Dashboard',

    /* Excel workbook */
    'excel_sheet_summary' => 'Summary',
    'excel_sheet_recent_patients' => 'Patients',
    'excel_sheet_recent_appointments' => 'Appts',
    'excel_sheet_recent_invoices' => 'Recent invoices',
    'excel_sheet_recent_payments' => 'Payments',
    'excel_sheet_expense_summary' => 'Expenses',
    'excel_sheet_invoices_filtered' => 'Filtered invoices',

    'excel_hdr_indicator' => 'Metric',
    'excel_hdr_value' => 'Value',
    /* Summary rows clinic */
    'excel_row_clinic_name' => 'Clinic name',
    'excel_row_total_patients' => 'Patients (total)',
    'excel_row_total_doctors' => 'Doctors (total)',
    'excel_row_total_appointments' => 'Appointments (total)',
    'excel_row_total_visits' => 'Visits (total)',
    'excel_row_total_invoices' => 'Invoices (total)',
    /* Accrual block */
    'excel_section_accrual' => 'Profitability (accrual)',
    'excel_row_accrual_revenue' => 'Total revenue (accrual)',
    'excel_row_ar' => 'Accounts receivable (AR)',
    'excel_row_doctor_share_accrual' => 'Doctor shares expense (accrual)',
    'excel_row_gross_profit_accrual' => 'Gross profit (accrual)',
    'excel_row_expenses_accrual' => 'Recorded expenses (accrual)',
    'excel_row_payroll_accrual' => 'Recorded payroll (accrual)',
    'excel_row_operating_cost_accrual' => 'Operating cost (accrual)',
    'excel_row_net_profit_accrual' => 'Net profit (accrual)',
    /* Cash block */
    'excel_section_cash' => 'Cash flow (liquidity)',
    'excel_row_patient_payments_total' => 'Cash in from patients (payments table)',
    'excel_row_invoice_paid_reference' => 'Invoice «paid» field total (reference)',
    'excel_row_total_cash_out' => 'Total cash out',
    'excel_row_net_cash_flow' => 'Net cash flow (cumulative)',
    'excel_row_cash_till_balance' => 'Cash balance (till)',
    /* Snapshot rows */
    'excel_row_accrual_today' => 'Accrual revenue — today',
    'excel_row_cash_collect_today' => 'Cash from patients — today',
    'excel_row_exp_accrual_today' => 'Recorded expenses — today (accrual)',
    'excel_row_exp_pay_today' => 'Expense payments — today (cash)',
    'excel_row_pay_accrual_today' => 'Recorded payroll — today (accrual)',
    'excel_row_payroll_out_today' => 'Payroll disbursement — today (cash)',
    'excel_row_accrual_month' => 'Accrual revenue — this month',
    'excel_row_cash_month' => 'Cash from patients — this month',
    'excel_row_exp_pay_month' => 'Expense payments — this month (cash)',
    'excel_row_payroll_out_month' => 'Payroll disbursement — this month (cash)',
    'excel_row_net_profit_month' => 'Net profit — this month (accrual)',
    'excel_row_net_cf_month' => 'Net cash flow — this month',
    'excel_row_net_profit_day' => 'Net profit — today (accrual)',
    /* Counts */
    'excel_row_unpaid_count' => 'Unpaid invoices (count)',
    'excel_row_unpaid_amount' => 'Unpaid total amount',
    'excel_row_partial_count' => 'Partial invoices (count)',
    'excel_row_partial_remaining' => 'Remaining (partial)',
    'excel_row_paid_count' => 'Paid invoices (count)',
    'excel_row_paid_amount_total' => 'Fully paid — total invoiced',

    'excel_section_period_compare' => 'Period comparison',

    'excel_row_labeled_period' => ':metric — :period',
    'excel_period_last_month' => 'Last month',
    'excel_period_this_month' => 'This month',
    'excel_period_ytd' => 'Year to date',
    'excel_period_last_30' => 'Last 30 days',

    'excel_metric_accrual_revenue' => 'Accrual revenue',
    'excel_metric_cash_patients' => 'Cash from patients (payments)',
    'excel_metric_doctor_share_accrual' => 'Doctor shares (accrual)',
    'excel_metric_expenses_accrual' => 'Recorded expenses (accrual)',
    'excel_metric_payroll_accrual' => 'Recorded payroll (accrual)',
    'excel_metric_expense_payments_cash' => 'Expense payments (cash)',
    'excel_metric_payroll_disbursement_cash' => 'Payroll disbursement (cash)',
    'excel_metric_doctor_settlements_cash' => 'Doctor settlements (cash)',
    'excel_metric_total_cash_out' => 'Total cash out',
    'excel_metric_net_cash_flow' => 'Net cash flow',
    'excel_metric_net_profit_accrual' => 'Net profit (accrual)',

    'excel_exported_at' => 'Exported at',

    /* Section headers excel additional */
    'excel_heading_income_by_pm' => 'Invoice collections by payment method',
    'excel_heading_expense_by_pm' => 'Expense payments by payment method',

    /* Recent sheets headers */
    'excel_th_full_name' => 'Full name',
    'excel_th_phone' => 'Phone',
    'excel_th_registered' => 'Registered',
    'excel_th_created_appt' => 'Created',

    /* Invoices filtered sheet */
    'excel_invoice_filter_summary_title' => 'Invoice summary (export filters)',
    'excel_invoice_count' => 'Invoice count',
    'excel_invoice_sum_total' => 'Invoice totals sum',
    'excel_invoice_sum_paid' => 'Paid sum',
    'excel_invoice_sum_remaining' => 'Remaining sum',
    'excel_section_margin_ref' => 'Profitability reference (whole clinic)',
    'excel_accrual_whole_clinic' => 'Accrual revenue (whole clinic)',
    'excel_net_profit_whole_clinic' => 'Net profit (accrual — whole clinic)',
    'excel_inv_row_date' => 'Date',

    /* PDF payments section title */
    'pdf_recent_five_payments' => 'Last 5 payments',

    /* Duplicate short keys kept from before */
    'accrual_revenue' => 'Accrual revenue',
    'cash_collection_patients' => 'Cash collection (patients)',
    'accounts_receivable' => 'Accounts receivable (AR)',
    'doctor_share_accrual' => 'Doctor shares (accrual)',
    'registered_expenses' => 'Recorded expenses',
    'registered_payroll' => 'Recorded payroll',
    'net_profit_accrual' => 'Net profit (accrual)',
    'pdf_th_total_short' => 'Total',

    /* Operating cost row subtitle in comparison table */
    'operating_cost_accrual_subline' => 'Shares + recorded expenses + recorded payroll',
];
