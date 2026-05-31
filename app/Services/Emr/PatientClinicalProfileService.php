<?php

namespace App\Services\Emr;

use App\Enums\ClinicalRecordType;
use App\Models\Patient;
use App\Models\PatientClinicalRecord;
use Illuminate\Support\Collection;

final class PatientClinicalProfileService
{
    /**
     * @return array<string, Collection<int, PatientClinicalRecord>>
     */
    public function groupedActiveRecords(Patient $patient): array
    {
        return $this->activeClinicalBundle($patient)['grouped'];
    }

    /**
     * @return list<string>
     */
    public function activeRiskFlags(Patient $patient): array
    {
        return $this->activeClinicalBundle($patient)['risk_flags'];
    }

    /**
     * One query for EMR header: grouped clinical rows + risk flag titles.
     *
     * @return array{grouped: array<string, Collection<int, PatientClinicalRecord>>, risk_flags: list<string>}
     */
    public function activeClinicalBundle(Patient $patient): array
    {
        $records = $patient->clinicalRecords()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        $grouped = [];
        foreach (ClinicalRecordType::cases() as $type) {
            $grouped[$type->value] = $records->where('type', $type->value)->values();
        }

        $riskFlags = $records
            ->where('type', ClinicalRecordType::RiskFlag->value)
            ->sortBy('title')
            ->pluck('title')
            ->values()
            ->all();

        return [
            'grouped' => $grouped,
            'risk_flags' => $riskFlags,
        ];
    }

    public function storeRecord(
        Patient $patient,
        ClinicalRecordType $type,
        string $title,
        ?string $details = null,
        ?string $severity = null,
        ?int $recordedBy = null,
    ): PatientClinicalRecord {
        return PatientClinicalRecord::query()->create([
            'clinic_id' => $patient->clinic_id,
            'patient_id' => $patient->id,
            'type' => $type->value,
            'title' => $title,
            'details' => $details,
            'severity' => $severity,
            'is_active' => true,
            'recorded_by' => $recordedBy,
        ]);
    }

    public function deactivateRecord(PatientClinicalRecord $record): void
    {
        $record->forceFill(['is_active' => false])->save();
    }
}
