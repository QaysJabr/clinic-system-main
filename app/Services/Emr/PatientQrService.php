<?php

namespace App\Services\Emr;

use App\Models\Patient;
use App\Models\PatientLookupToken;
use Illuminate\Support\Str;

final class PatientQrService
{
    public function issueToken(Patient $patient): string
    {
        $plain = Str::random(40);

        PatientLookupToken::query()->create([
            'clinic_id' => $patient->clinic_id,
            'patient_id' => $patient->id,
            'token' => hash('sha256', $plain),
            'expires_at' => null,
        ]);

        return $plain;
    }

    public function resolvePatient(string $plain): ?Patient
    {
        $hash = hash('sha256', $plain);

        $row = PatientLookupToken::withoutGlobalScopes()
            ->where('token', $hash)
            ->where(function ($q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $row) {
            return null;
        }

        return Patient::withoutGlobalScopes()->find($row->patient_id);
    }

    public function lookupUrl(string $plain): string
    {
        return route('patients.lookup', ['token' => $plain]);
    }

    public function portalUrl(string $plain): string
    {
        return route('portal.patient.show', ['token' => $plain]);
    }
}
