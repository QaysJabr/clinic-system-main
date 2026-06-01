<?php

namespace App\Services;

use App\Models\Patient;
use App\Support\TenantValidation;

final class PatientFileNumberService
{
    public function generate(?int $clinicId = null): string
    {
        $clinicId ??= TenantValidation::clinicIdForRules();

        if (! $clinicId) {
            return 'P-'.now()->format('ymdHis');
        }

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $seq = Patient::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->count() + 1 + $attempt;
            $candidate = 'P-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

            $exists = Patient::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->where('file_number', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }
        }

        return 'P-'.now()->format('ymdHis');
    }
}
