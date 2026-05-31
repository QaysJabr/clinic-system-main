<?php

namespace App\Support;

use App\Models\ClinicSetting;
use App\Models\Invoice;

final class ClinicBusinessRules
{
    public static function settings(): ClinicSetting
    {
        return ClinicSetting::current();
    }

    public static function visitHasInvoice(int $visitId): bool
    {
        return Invoice::query()->where('visit_id', $visitId)->exists();
    }

    public static function visitInvoiceCount(int $visitId): int
    {
        return Invoice::query()->where('visit_id', $visitId)->count();
    }
}
