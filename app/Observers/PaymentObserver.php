<?php

namespace App\Observers;

use App\Models\Payment;
use App\Support\ClinicCacheInvalidator;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        ClinicCacheInvalidator::flush($payment->clinic_id);
    }

    public function deleted(Payment $payment): void
    {
        ClinicCacheInvalidator::flush($payment->clinic_id);
    }
}
