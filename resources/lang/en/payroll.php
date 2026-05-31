<?php

return [
    'title' => 'Payroll',
    'nav' => 'Payroll',

    'page_title_index' => 'Staff payroll',
    'page_title_create' => 'Record payroll payment',
    'page_title_edit' => 'Edit payroll payment',

    /* Payment cycle (period type) */
    'cycle_weekly' => 'Weekly',
    'cycle_monthly' => 'Monthly',

    'runs_page_title' => 'Payroll cycles',
    'runs_subtitle' => 'Define a pay period (weekly or monthly), then generate payroll line items for staff whose compensation profile <span class="font-semibold">payment cycle</span> matches this period type.',
    'runs_flash_could_not_save' => 'Could not save',
    'runs_section_register_period' => 'Register a period',
    'runs_heading_new_period' => 'New period',
    'runs_field_period_kind' => 'Period type',
    'runs_field_period_from' => 'Period start',
    'runs_field_period_until' => 'Period end',
    'runs_notes_optional' => 'Notes (optional)',
    'runs_submit_register' => 'Save period',
    'runs_registered_periods_heading' => 'Recorded periods',
    'runs_payroll_cycles_table_heading' => 'Payroll cycles',
    'runs_link_staff_payments' => 'Payments ledger',
    'runs_th_from' => 'From',
    'runs_th_until' => 'To',
    'runs_th_notes' => 'Notes',
    'runs_th_line_items' => 'Line items',
    'runs_generate_items' => 'Generate line items',
    'runs_empty_no_periods' => 'No periods recorded yet.',
    'runs_th_action' => 'Action',

    'runs_validation_period_exists' => 'This period is already recorded.',
    'runs_flash_period_saved_draft' => 'Pay period saved as draft. Use “Generate line items” to create payment records.',
    'runs_audit_generate_summary' => 'Payroll run #:id: created :created line item(s); skipped existing :existing, inactive :inactive, before profile start :before_profile, incomplete profile :incomplete.',
    'runs_flash_generate_summary' => 'Generated: :created new line item(s). Skipped: :existing existing, :inactive inactive, :before_profile before profile start, :incomplete incomplete profile.',

    'run_status_draft' => 'Draft',
    'run_status_generated' => 'Generated',

    /* Staff role types (used in payroll filters) */
    'role_doctor' => 'Doctor',
    'role_receptionist' => 'Reception',
    'role_accountant' => 'Accountant',
    'role_nurse' => 'Nurse',
    'role_worker' => 'Worker',
    'role_cleaner' => 'Cleaner',
    'role_assistant' => 'Assistant',
    'role_other' => 'Other',

    /* Period label formatting */
    'period_range_separator' => '—',
    'period_date_arrow' => '→',

    /* Display status vs remaining (consistent with partial/unpaid) */
    'status_completed' => 'Fully paid',
    'status_partial' => 'Partial',
    'status_unpaid' => 'Unpaid',

    'notifications' => [
        'current_period_note' => ' (current period)',
        'outstanding_partial_title' => 'Salary partially paid',
        'outstanding_partial_body' => ':staff — :period:note — remaining :remaining:currency_suffix of total due.',
        'outstanding_unpaid_title' => 'Salary not fully disbursed',
        'outstanding_unpaid_body' => ':staff — :period:note — total due not fully paid — remaining :remaining:currency_suffix.',
        'settled_title' => 'Payroll fully disbursed',
        'settled_body' => ":staff's salary for :period has been fully settled.",
    ],

    'validation_deduction_vs_base_bonus' => 'Deduction cannot exceed base pay plus bonus.',
    'validation_paid_exceeds_due' => 'Paid amount cannot exceed amount due (base + bonus − deduction).',
    'validation_negative_remaining' => 'Remaining cannot be negative.',
    'validation_duplicate_period' => 'A payroll record already exists for this staff member and same period start/end.',

    'flash_created' => 'Payroll payment recorded successfully.',
    'flash_updated' => 'Payroll payment updated successfully.',
    'flash_deleted' => 'Payroll payment deleted successfully.',

    'audit_create' => 'Record payroll payment — staff :staff_id — :period',
    'audit_update' => 'Update payroll payment #:id',
    'audit_delete' => 'Delete payroll payment #:id',

    /* Reports / dashboard */
    'reports_dashboard_accrual_label' => 'Recorded payroll',
    'reports_dashboard_accrual_hint' => 'Payroll entries (accrual).',
    'reports_row_payroll_accrual' => 'Recorded payroll (accrual)',
    'reports_row_payroll_cash' => 'Payroll disbursement (cash)',
    'reports_calendar_row_payroll_accrual' => 'Recorded payroll (accrual)',
    'reports_calendar_row_payroll_cash' => 'Payroll disbursement (cash)',
    'reports_calendar_row_operating_accrual_hint' => 'Doctor shares + recorded expenses + recorded payroll',
    'reports_row_expense_payroll_cash_sub' => 'Expense payments + payroll disbursement + doctor settlements (all time)',
    'reports_profitability_note' => 'Profitability rows: revenue, shares, and recorded expenses/payroll (accrual); operating cost accrual and net profit. Cash rows: collections from patients, expense payments, payroll disbursement, doctor settlements, total outflows, net cash flow. Do not mix columns between layers. «Last 30 days» includes today.',

    'reports_table_row_doctor_share_expense' => 'Doctor shares (expense)',
    'reports_table_row_accrued_expenses' => 'Recorded expenses (accrual)',
    'reports_table_row_accrued_payroll' => 'Recorded payroll (accrual)',
    'reports_th_payroll_accrual' => 'Payroll (accrual)',
    'reports_table_row_expense_cash_out' => 'Expense payments (cash)',
    'reports_table_row_payroll_cash_out' => 'Payroll disbursement (cash)',
    'reports_operating_cost_accrual_title' => 'Operating cost (accrual)',
    'reports_operating_cost_accrual_sub' => 'Shares + recorded expenses + recorded payroll',

    'reports_summary_expenses_accrual_short' => 'Expense total (table)',
    'reports_summary_payroll_accrual_short' => 'Payroll entries — separate from percentage shares',
    'reports_summary_operating_mix' => 'Doctor shares + recorded expenses + recorded payroll',
    'reports_cashflow_patient_in' => 'Patient cash in (payments)',
    'reports_cashflow_out_sub' => 'Expense payments + payroll + paid doctor settlements',

    'dashboard_nav_payroll' => 'Payroll',
    'dashboard_accrual_cards_title' => 'Full operating cost (shares + expenses + payroll)',
    'dashboard_net_profit_line' => 'Net profit',
    'dashboard_net_profit_formula' => '= revenue − doctor shares − expenses − payroll.',
    'dashboard_net_profit_operating_card_note' => 'The «full operating cost» card below groups (shares + expenses + payroll) for quick comparison.',

    /* Table headers (staff payments list) — will match partials */
    'th_staff' => 'Staff',
    'th_period' => 'Period',
    'th_total_due' => 'Total due',
    'th_paid' => 'Paid',
    'th_remaining' => 'Remaining',
    'th_status' => 'Status',
    'th_pay_date' => 'Pay date',
    'th_actions' => 'Actions',
    'btn_add_record' => 'Record payment',
    'filter_role' => 'Role',
    'filter_period_type' => 'Period type',
    'filter_period_from' => 'Period from',
    'filter_period_to' => 'Period to',

    'nav_sidebar' => 'Payroll & payments',

    'subtitle_index' => 'Manual payroll entries per staff member and period (weekly or monthly).',

    'stat_month_paid' => 'Paid this month',
    'stat_month_due' => 'Due this month',
    'stat_outstanding' => 'With balance due',
    'stat_month_records' => 'Records this month',
    'quick_this_month' => 'This month',
    'empty_title' => 'No payroll records',

    'list_section_label' => 'Payroll ledger',
    'table_main_heading' => 'Staff payroll',

    'link_compensation_profiles' => 'Compensation profiles',
    'link_payroll_runs' => 'Payroll cycles',

    'th_staff_full_name' => 'Staff member',
    'th_role_type' => 'Role',
    'th_period_kind' => 'Period type',
    'th_period_start' => 'Period start',
    'th_period_end' => 'Period end',
    'th_base' => 'Base pay',
    'th_bonus' => 'Bonus',
    'th_deduction' => 'Deduction',

    'subtitle_create' => 'Enter base pay, bonus, deduction, and amount paid — total due and remaining are computed automatically.',

    'alert_no_staff_title' => 'No staff on file',
    'alert_no_staff_body' => 'Add a staff member from Staff before recording payroll.',

    'form_section_payment_data' => 'Payment details',

    'placeholder_select_staff' => '— Select staff member —',

    'field_period_start_short' => 'Period start date',
    'field_period_end_short' => 'Period end date',

    'field_compensation_profile_optional' => 'Compensation profile (optional)',
    'placeholder_none' => '— None —',

    'confirm_delete_record_title' => 'Delete payroll record',
    'confirm_delete_record_body' => 'Delete this payroll record?',
    'no_matching_records' => 'No matching payroll records.',

    'btn_save_payroll_record' => 'Save',
    'btn_update_payroll_record' => 'Update',

    'field_days_worked_optional' => 'Days worked (optional)',
    'field_base_amount' => 'Base pay',
    'field_bonus_optional' => 'Bonus (optional)',
    'field_deduction_optional' => 'Deduction (optional)',
    'field_paid_required' => 'Paid amount',
    'field_payment_date_optional' => 'Payment date (optional)',
    'field_payment_method_optional' => 'Payment method (optional)',
    'field_notes_optional' => 'Notes (optional)',
    'btn_back_to_list' => 'Back',
    'btn_add_staff' => 'Add staff member',

    'preview_title' => 'Calculation preview',
    'preview_total_due_label' => 'Due:',
    'preview_remaining_label' => 'Remaining:',
    'preview_hint' => 'Due = base + bonus − deduction. Remaining = due − paid.',

    'reports_cash_cards_out_subnote' => 'Expenses + payroll + doctor settlements (cash only)',
];
