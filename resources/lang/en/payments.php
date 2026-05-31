<?php

return [
    'title' => 'Payments',
    /* Patient payments on invoices (invoice_payments module) */

    'flash_added' => 'Payment added successfully.',

    'validation_amount_exceeds_remaining' => 'Payment amount exceeds the remaining balance on the invoice (:remaining).',
    'validation_invoice_fully_paid' => 'The invoice is already fully settled — cannot add another payment.',

    'audit_payment_recorded' => 'Recorded payment of :amount for invoice :invoice_number',

    'methods' => [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank transfer',
        'other' => 'Other',
    ],

    'notifications' => [
        'payment_recorded_title' => 'Payment recorded',
        'payment_recorded_body' => 'Payment of :amount for invoice :invoice — patient :patient',

        'invoice_open_title' => 'Invoice needs follow-up',
        'invoice_open_body' => 'Invoice :invoice — patient :patient — status: :status — total: :total',

        'invoice_paid_title' => 'Invoice fully settled',
        'invoice_paid_body' => 'Invoice :invoice — patient :patient — total settled :total',
    ],

    /* Reports / tables */
    'reports_th_invoice' => 'Invoice',
    'reports_th_method' => 'Method',
    'reports_th_payment_date' => 'Date',
    'reports_recent_payments' => 'Last 5 payments',

    'dashboard_recent_payments' => 'Last 5 payments',
    'dashboard_no_data' => 'No data.',
];
