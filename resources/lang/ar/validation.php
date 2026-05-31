<?php

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

$base = array_merge($base, [
    'accepted' => 'يجب قبول :attribute.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صالحًا.',
    'max' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصرًا.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
        'numeric' => 'يجب ألا يكون :attribute أكبر من :max.',
        'string' => 'يجب ألا يزيد :attribute عن :max أحرف.',
    ],
    'min' => [
        'array' => 'يجب ألا يحتوي :attribute على أقل من :min عنصرًا.',
        'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.',
        'numeric' => 'يجب ألا يكون :attribute أصغر من :min.',
        'string' => 'يجب ألا يقل :attribute عن :min أحرف.',
    ],
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'required' => 'حقل :attribute مطلوب.',
    'unique' => ':attribute مستخدم بالفعل.',
    'date' => 'يجب أن يكون :attribute تاريخًا صحيحًا.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
]);

$base['attributes'] = array_merge($base['attributes'] ?? [], [
    'patient_name' => 'اسم المريض',
    'doctor_name' => 'اسم الطبيب',
    'patient_id' => 'المريض',
    'visit_id' => 'الزيارة',
    'invoice_id' => 'الفاتورة',
    'items' => 'بنود الفاتورة',
    'invoice_total' => 'إجمالي الفاتورة',
    'paid_amount' => 'المبلغ المدفوع',
    'due_date' => 'تاريخ الاستحقاق',
    'appointment_date' => 'تاريخ الموعد',
    'payment_method' => 'طريقة الدفع',
    'payment_date' => 'تاريخ الدفع',
    'amount' => 'المبلغ',
    'notes' => 'ملاحظات',
    'phone' => 'الهاتف',
    'email' => 'البريد الإلكتروني',
    'password' => 'كلمة المرور',
    'expense_category_id' => 'التصنيف',
    'title' => 'العنوان',
    'settlement_type' => 'نوع التسوية',
    'expense_date' => 'تاريخ المصروف',
    'first_payment_amount' => 'مبلغ الدفعة الأولى',
    'first_payment_paid_at' => 'تاريخ الدفعة الأولى',
    'first_payment_method' => 'طريقة الدفعة الأولى',
    'first_payment_notes' => 'ملاحظات الدفعة الأولى',
    'deduction' => 'الخصم',
    'period_start' => 'بداية الفترة',
    'period_end' => 'نهاية الفترة',
    'period_type' => 'نوع الفترة',
    'staff_id' => 'الموظف',
    'base_amount' => 'الأساس',
    'bonus' => 'العلاوة',
    'paid_at' => 'تاريخ الدفع',
]);

return $base;
