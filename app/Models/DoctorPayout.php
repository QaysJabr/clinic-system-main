<?php

namespace App\Models;

/**
 * اسم بديل لطبيعة الجدول: مستحقات/دفعات الطبيب المخزّنة في `doctor_earnings`.
 * (حصة تُحسب عند دفع الفاتورة → pending؛ تُعلَم مدفوعة للطبيب عند التسوية.)
 *
 * @mixin DoctorEarning
 */
class DoctorPayout extends DoctorEarning
{
    /**
     * نفس الجدول — لا تكرار بيانات.
     */
    protected $table = 'doctor_earnings';
}
