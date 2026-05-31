<?php

return [
    'title' => 'المدفوعات',

    'flash_added' => 'تم إضافة الدفعة بنجاح',

    'validation_amount_exceeds_remaining' => 'مبلغ الدفعة يتجاوز المتبقي على الفاتورة (:remaining).',
    'validation_invoice_fully_paid' => 'الفاتورة مسددة بالكامل — لا يمكن إضافة دفعة إضافية.',

    'audit_payment_recorded' => 'تسجيل دفعة بقيمة :amount للفاتورة :invoice_number',

    'methods' => [
        'cash' => 'نقدي',
        'card' => 'بطاقة',
        'bank_transfer' => 'تحويل بنكي',
        'other' => 'أخرى',
    ],

    'notifications' => [
        'payment_recorded_title' => 'تسجيل دفعة',
        'payment_recorded_body' => 'دفعة بقيمة :amount للفاتورة :invoice — المريض :patient',

        'invoice_open_title' => 'فاتورة بحاجة متابعة',
        'invoice_open_body' => 'الفاتورة :invoice — المريض :patient — الحالة: :status — الإجمالي: :total',

        'invoice_paid_title' => 'فاتورة مسددة بالكامل',
        'invoice_paid_body' => 'الفاتورة :invoice — المريض :patient — تم سداد الإجمالي :total',
    ],

    'reports_th_invoice' => 'الفاتورة',
    'reports_th_method' => 'الطريقة',
    'reports_th_payment_date' => 'التاريخ',
    'reports_recent_payments' => 'آخر 5 دفعات',

    'dashboard_recent_payments' => 'آخر 5 دفعات',
    'dashboard_no_data' => 'لا توجد بيانات',
];
