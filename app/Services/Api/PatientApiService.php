<?php

namespace App\Services\Api;

use App\Models\Clinic;
use App\Models\Patient;
use App\Support\AuditLogger;
use App\Support\TenantValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class PatientApiService
{
    /**
     * @return array<string, mixed>
     */
    public static function validationRules(?Patient $patient = null): array
    {
        $clinicId = TenantValidation::clinicIdForRules();

        return [
            'file_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('patients', 'file_number')
                    ->ignore($patient?->id)
                    ->where(fn ($q) => $q->where('clinic_id', $clinicId)),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Patient
    {
        $user = Auth::user();
        abort_if($user === null, 401);

        $clinicId = (int) ($user->clinic_id ?? 0);
        abort_if($clinicId <= 0, 403);

        $clinic = Clinic::query()->with('plan')->find($clinicId);
        if ($clinic && ! $clinic->canAddPatient()) {
            throw ValidationException::withMessages([
                'full_name' => __('patients.subscription_patient_limit'),
            ]);
        }

        $fileNumber = trim((string) ($validated['file_number'] ?? ''));
        if ($fileNumber === '') {
            $fileNumber = $this->generateFileNumber($clinicId);
        }

        $patient = Patient::query()->create([
            'clinic_id' => $clinicId,
            'file_number' => $fileNumber,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        AuditLogger::log(
            'create',
            'patients',
            $patient->id,
            __('patients.audit_create_body', ['name' => $patient->full_name, 'file' => $patient->file_number]),
            null,
            $patient->only(['file_number', 'full_name', 'phone', 'date_of_birth', 'gender', 'notes', 'status']),
        );

        return $patient;
    }

    private function generateFileNumber(int $clinicId): string
    {
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
