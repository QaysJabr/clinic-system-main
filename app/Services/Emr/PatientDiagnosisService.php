<?php

namespace App\Services\Emr;

use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class PatientDiagnosisService
{
    public function recordFromVisit(Visit $visit): ?PatientDiagnosis
    {
        $description = trim((string) $visit->diagnosis);
        if ($description === '') {
            return null;
        }

        $existing = PatientDiagnosis::query()
            ->where('visit_id', $visit->id)
            ->first();

        if ($existing) {
            $existing->forceFill([
                'description' => $description,
                'doctor_id' => $visit->doctor_id,
                'diagnosed_at' => $visit->visit_date,
            ])->save();

            return $existing;
        }

        return PatientDiagnosis::query()->create([
            'clinic_id' => $visit->clinic_id,
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'doctor_id' => $visit->doctor_id,
            'description' => $description,
            'diagnosed_at' => $visit->visit_date,
            'is_primary' => false,
        ]);
    }

    public function searchPaginator(Patient $patient, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $q = PatientDiagnosis::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor:id,full_name', 'visit:id,visit_date'])
            ->orderByDesc('diagnosed_at')
            ->orderByDesc('id');

        if ($search = trim((string) $request->input('q', ''))) {
            $q->where(function ($query) use ($search): void {
                $query->where('description', 'like', '%'.$search.'%')
                    ->orWhere('icd_code', 'like', '%'.$search.'%');
            });
        }

        return $q->paginate($perPage)->withQueryString();
    }
}
