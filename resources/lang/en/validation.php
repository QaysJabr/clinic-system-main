<?php

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

$base['attributes'] = array_merge($base['attributes'] ?? [], [
    'patient_name' => 'patient name',
    'doctor_name' => 'doctor name',
    'patient_id' => 'patient',
    'visit_id' => 'visit',
    'invoice_id' => 'invoice',
    'items' => 'line items',
    'invoice_total' => 'invoice total',
    'paid_amount' => 'paid amount',
    'due_date' => 'due date',
    'appointment_date' => 'appointment date',
    'payment_method' => 'payment method',
    'payment_date' => 'payment date',
    'amount' => 'amount',
    'notes' => 'notes',
    'phone' => 'phone',
    'email' => 'email',
    'password' => 'password',
    'expense_category_id' => 'category',
    'title' => 'title',
    'settlement_type' => 'settlement type',
    'expense_date' => 'expense date',
    'first_payment_amount' => 'first payment amount',
    'first_payment_paid_at' => 'first payment date',
    'first_payment_method' => 'first payment method',
    'first_payment_notes' => 'first payment notes',
    'deduction' => 'deduction',
    'period_start' => 'period start',
    'period_end' => 'period end',
    'period_type' => 'period type',
    'staff_id' => 'staff member',
    'base_amount' => 'base pay',
    'bonus' => 'bonus',
    'paid_at' => 'payment date',
]);

return $base;
