<?php

return [
    'title' => 'Invoices',
    'nav' => 'Invoices',

    'page_title_index' => 'Invoices',
    'page_title_create' => 'Create invoice',
    'page_title_edit' => 'Edit invoice',
    'page_title_show' => 'Invoice details',
    'page_heading_create_slot' => 'Add new invoice',
    'page_heading_edit_slot' => 'Edit invoice',
    'page_heading_show_slot' => 'View invoice',

    'subtitle_index' => 'Track invoices, collections, and quick filters.',

    'nav_aria' => 'Invoices navigation',
    'nav_list' => 'Invoice list',
    'nav_details' => 'Invoice details',
    'nav_edit' => 'Edit',

    'stat_today' => "Today's invoices",
    'stat_unpaid' => 'Unpaid',
    'stat_partial' => 'Partially paid',
    'stat_outstanding' => 'Outstanding balance',
    'quick_today' => 'Today',
    'results_count' => ':from–:to of :total',
    'empty_title' => 'No invoices yet',
    'empty_hint' => 'Start by creating a new invoice for a patient.',
    'field_amounts' => 'Amounts',
    'paid_short' => 'Paid:',
    'remaining_short' => 'Due:',
    'create_subtitle' => 'Choose a patient and add invoice line items.',
    'back_to_invoice' => 'Back to invoice',
    'add_payment_hint' => 'Maximum payment amount: :amount',
    'filter_results' => 'Filter results',
    'filter_criteria' => 'Search criteria',
    'search_placeholder' => 'Search…',
    'list_section' => 'Invoice list',
    'table_heading_all' => 'All invoices',
    'export_excel' => 'Export Excel',
    'add_new' => 'Add invoice',

    'field_invoice_number' => 'Invoice number',
    'field_patient' => 'Patient',
    'field_patient_name' => 'Patient name',
    'field_doctor' => 'Doctor',
    'field_status' => 'Status',
    'field_date_from' => 'From date',
    'field_date_to' => 'To date',
    'field_specific_day' => 'Specific day (optional)',
    'field_visit_optional' => 'Visit (optional)',
    'field_notes' => 'Notes',
    'field_total' => 'Total',
    'field_total_amount' => 'Total amount',
    'field_paid' => 'Paid',
    'field_remaining' => 'Remaining',
    'field_created_at' => 'Created at',
    'field_invoice_date' => 'Invoice date',
    'field_due_date_column' => 'Due',
    'field_visit_line' => 'Visit',

    'status_option_all' => 'All',

    'th_actions' => 'Actions',
    'action_view' => 'View',
    'action_edit' => 'Edit',
    'confirm_delete_title' => 'Delete invoice',
    'confirm_delete_body' => 'Are you sure you want to delete this invoice?',
    'no_matching_results' => 'No matching results.',

    'link_services_catalog' => 'Services catalog',
    'services_catalog_hint' => '— pick a service to fill the price automatically, or enter a line manually.',

    'patient_select_placeholder' => 'Choose patient',
    'visit_none' => 'No visit',
    'visit_fallback_label' => 'Visit',

    'section_line_items' => 'Line items',
    'label_service' => 'Service',
    'service_manual' => 'Manual',
    'label_item_name' => 'Line name',
    'placeholder_item_name' => 'Line name',
    'label_price' => 'Price',
    'placeholder_price' => 'Price',
    'label_quantity' => 'Quantity',
    'placeholder_quantity' => 'Quantity',
    'btn_remove_line' => 'Remove',
    'btn_add_line' => 'Add line',

    'total_preview' => 'Total:',
    'save_invoice' => 'Save invoice',
    'update_invoice' => 'Update invoice',

    'show_print_intro' => 'Print or export a copy of this invoice',
    'print' => 'Print',
    'export_pdf' => 'Export PDF',

    'section_invoice_info' => 'Invoice information',
    'section_amounts' => 'Amounts',
    'section_items' => 'Line items',
    'section_payments' => 'Payments',

    'label_invoice_number' => 'Invoice number:',
    'label_patient' => 'Patient:',
    'label_visit' => 'Visit:',
    'label_status' => 'Status:',
    'label_notes' => 'Notes:',
    'label_grand_total' => 'Grand total:',
    'label_paid' => 'Paid:',
    'label_remaining' => 'Remaining:',

    'th_item_name' => 'Line name',
    'th_qty' => 'Qty',
    'th_line_total' => 'Total',
    'no_line_items' => 'No lines.',
    'no_payments' => 'No payments.',

    'add_payment_heading' => 'Record payment',
    'btn_add_payment' => 'Add payment',
    'payment_field_method' => 'Payment method',
    'payment_field_date' => 'Payment date',
    'back_to_index' => 'Back to invoices',

    'print_page_title' => 'Invoice — :number',
    'print_document_tag' => 'Invoice',
    'section_invoice_details' => 'Invoice details',
    'section_patient_details' => 'Patient details',
    'print_clinic_fallback' => 'Management system',
    'section_general_notes' => 'General notes',

    /* PDF download filename prefix (latin, safe). */
    'pdf_download_prefix' => 'invoice',

    /* Flash / validation */
    'flash_created' => 'Invoice created successfully.',
    'flash_updated' => 'Invoice updated successfully.',
    'flash_deleted' => 'Invoice deleted successfully.',
    'flash_delete_has_payments' => 'Cannot delete an invoice that has recorded payments.',

    'audit_create' => 'Create invoice :number',
    'audit_update' => 'Update invoice :number',
    'audit_delete' => 'Delete invoice :number',
    'audit_export_print' => 'Print invoice: :number',
    'audit_export_pdf' => 'Export invoice PDF: :number',
    'audit_export_excel' => 'Excel export — invoices',

    'excel_sheet_name' => 'Invoices',

    'excel_th_created_at' => 'Created at',
    'excel_th_patient' => 'Patient',
    'excel_th_doctor' => 'Doctor',
    'excel_th_invoice_number' => 'Invoice number',
    'excel_th_total' => 'Total',
    'excel_th_paid' => 'Paid',
    'excel_th_remaining' => 'Remaining',
    'excel_th_status' => 'Status',

    'validation_invalid_service' => 'Invalid or inactive service.',
    'validation_item_name_required' => 'Invoice line name is required.',
    'validation_total_below_payments' => 'The invoice total after editing cannot be less than recorded payments (:amount).',
    'validation_one_invoice_per_visit' => 'This visit is already linked to another invoice — only one invoice is allowed.',
    'validation_patient_visit_mismatch' => 'Patient does not match the selected visit.',
    'validation_visit_patient_must_match' => 'The visit must belong to the selected patient.',

    /* Dashboard (invoice-linked only) */
    'dashboard_alert_open_title' => 'Invoices not fully settled:',
    'dashboard_invoice_word' => '{1} invoice|[2,*] invoices',
    'dashboard_alert_open_link' => 'View invoices',

    'dashboard_stat_invoices_linked_visits_own' => 'Invoices tied to your visits',
    'dashboard_stat_invoices_total_all' => 'Total invoices',
    'dashboard_stat_invoices_sub_own' => 'Where you are the doctor on the invoice',
    'dashboard_stat_invoices_sub_all' => 'Invoice',

    'dashboard_section_activity' => 'Invoices & today’s activity',

    'dashboard_card_unpaid_title' => 'Unpaid invoices',
    'dashboard_card_unpaid_sub' => 'Invoice count',

    'dashboard_card_partial_title' => 'Partially paid invoices',
    'dashboard_card_partial_sub' => 'Invoice count',

    'dashboard_card_paid_title' => 'Fully paid invoices',
    'dashboard_card_paid_sub' => 'Invoice count',

    'dashboard_recent_invoices' => 'Last 5 invoices',
    'dashboard_no_data' => 'No data.',

    /* Reports — invoice/collection sections only */
    'reports_heading_collection_by_method' => 'Patient invoice collections by payment method (sum of invoice payments)',
    'reports_financial_cards_title' => 'Financial summary (invoices)',
    'reports_card_paid_full_title' => 'Total fully paid',
    'reports_card_paid_full_sub' => 'Sum of invoices marked paid',
    'reports_card_partial_remaining_title' => 'Remaining (partial invoices)',
    'reports_card_partial_remaining_sub' => 'Total (invoice total − paid)',
    'reports_card_unpaid_title' => 'Total unpaid',
    'reports_card_unpaid_sub' => 'Sum of unpaid invoice totals',

    'reports_heading_status_distribution' => 'Invoices by status',
    'reports_status_heading_unpaid' => 'Unpaid',
    'reports_status_heading_partial' => 'Partially paid',
    'reports_status_heading_paid' => 'Fully paid',
    'reports_status_amount_prefix' => 'Amount:',
    'reports_status_remaining_prefix' => 'Remaining:',
    'reports_total_invoices_kpi' => 'Total invoices',
    'reports_recent_invoices' => 'Last 5 invoices',
    'reports_recent_payments' => 'Last 5 payments',
    'reports_quick_link' => 'Invoices',

    /* Receivables (AR report) */
    'receivables_page_title' => 'Accounts receivable',
    'receivables_subtitle' => 'Unpaid or partial invoices — :range',
    'receivables_banner_title' => 'Total remaining (rows matching filters)',
    'receivables_empty' => 'No matching receivables.',
    'receivables_nav_reports' => 'Reports',
    'receivables_apply' => 'Apply',

    'receivables_meta_title' => 'Accounts receivable report',
    'receivables_pdf_total_remaining_filtered' => 'Total remaining (filters)',
    'receivables_pdf_section_table' => 'Receivables table',
    'receivables_filter_doctor' => 'Doctor: :name',

    'receivables_excel_sheet_receivables' => 'Receivables',
    'receivables_excel_sheet_summary' => 'Summary',
    'receivables_excel_col_item' => 'Item',
    'receivables_excel_col_value' => 'Value',
    'receivables_excel_row_total_matching' => 'Total receivables (displayed rows)',
    'receivables_excel_row_ar_reference' => 'Clinic AR total (reference)',
    'receivables_excel_row_accrual_reference' => 'Accrual revenue (reference)',
    'receivables_excel_row_patient_cash_reference' => 'Patient cash collections (reference)',
    'receivables_excel_row_doctor_share_reference' => 'Doctor accrual shares (reference)',
    'receivables_excel_row_net_profit_reference' => 'Net accrual profit (reference)',

    'receivables_pdf_print_date' => 'Printed :datetime',

    'audit_receivables_pdf' => 'PDF — accounts receivable',
    'audit_receivables_excel' => 'Excel — accounts receivable',

    /* JS dynamic line-item form */
    'js_placeholder_item_name' => 'Line name',
    'js_placeholder_price' => 'Price',
    'js_placeholder_quantity' => 'Quantity',
];
