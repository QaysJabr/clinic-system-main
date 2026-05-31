<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\InAppNotificationService;
use App\Support\ClinicCacheInvalidator;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ExpenseObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly InAppNotificationService $inAppNotificationService
    ) {}

    public function created(Expense $expense): void
    {
        $this->inAppNotificationService->notifyExpenseRecorded($expense);
        ClinicCacheInvalidator::flush($expense->clinic_id);
    }

    public function updated(Expense $expense): void
    {
        ClinicCacheInvalidator::flush($expense->clinic_id);
    }
}
