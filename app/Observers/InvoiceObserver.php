<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\DoctorEarningSyncService;
use App\Services\InAppNotificationService;
use App\Support\ClinicCacheInvalidator;

class InvoiceObserver
{
    public function __construct(
        private readonly DoctorEarningSyncService $doctorEarningSyncService,
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function saved(Invoice $invoice): void
    {
        $this->doctorEarningSyncService->syncFromInvoice($invoice);

        if ($invoice->status === 'paid' && $invoice->wasChanged('status')) {
            $this->inAppNotificationService->notifyInvoiceFullyPaid($invoice);
        }

        if (in_array($invoice->status, ['unpaid', 'partial'], true)) {
            if ($invoice->wasRecentlyCreated || $invoice->wasChanged(['status', 'total', 'paid'])) {
                $this->inAppNotificationService->notifyOpenInvoiceIfNeeded($invoice);
            }
        }

        ClinicCacheInvalidator::flush($invoice->clinic_id);
    }
}
