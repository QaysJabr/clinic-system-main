<?php

namespace App\Http\Controllers;

use App\Enums\ClinicalRecordType;
use App\Models\Patient;
use App\Models\PatientClinicalRecord;
use App\Services\Emr\PatientClinicalProfileService;
use App\Services\Emr\PatientDiagnosisService;
use App\Services\Emr\PatientQrService;
use App\Services\Emr\PatientTimelineService;
use App\Support\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class PatientEmrController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PatientTimelineService $timeline,
        private readonly PatientClinicalProfileService $clinical,
        private readonly PatientDiagnosisService $diagnoses,
        private readonly PatientQrService $qr,
    ) {}

    public function chart(Request $request, Patient $patient): View
    {
        $this->authorize('view', $patient);

        $canViewClinical = Gate::allows('viewPatientClinical', $patient);
        $canManageClinical = Gate::allows('update', $patient) && $canViewClinical;

        $timeline = $canViewClinical
            ? $this->timeline->build($patient, $request->user())
            : collect();

        $clinicalBundle = $canViewClinical
            ? $this->clinical->activeClinicalBundle($patient)
            : ['grouped' => [], 'risk_flags' => []];
        $clinicalGrouped = $clinicalBundle['grouped'];
        $riskFlags = $clinicalBundle['risk_flags'];

        $diagnosisHistory = $canViewClinical
            ? $this->diagnoses->searchPaginator($patient, $request, 10)
            : null;

        $attachments = $patient->attachments()
            ->select(['id', 'patient_id', 'visit_id', 'file_name', 'file_type', 'category', 'file_size', 'notes', 'created_at'])
            ->latest()
            ->get();

        $pageTitle = __('patients.page_title_emr', ['name' => $patient->full_name]);

        $viewData = compact(
            'patient',
            'pageTitle',
            'canViewClinical',
            'canManageClinical',
            'timeline',
            'clinicalGrouped',
            'riskFlags',
            'diagnosisHistory',
            'attachments',
        );

        if ($request->ajax()) {
            return view('patients.partials.emr-chart', $viewData);
        }

        return view('patients.emr-chart', $viewData);
    }

    public function storeClinicalRecord(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);
        abort_unless(Gate::allows('viewPatientClinical', $patient), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', ClinicalRecordType::values())],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:5000'],
            'severity' => ['nullable', 'string', 'max:32'],
        ]);

        $record = $this->clinical->storeRecord(
            $patient,
            ClinicalRecordType::from($validated['type']),
            $validated['title'],
            $validated['details'] ?? null,
            $validated['severity'] ?? null,
            (int) $request->user()->id,
        );

        AuditLogger::log(
            'create',
            'patient_clinical_records',
            $record->id,
            __('emr.audit_clinical_create', ['type' => $validated['type'], 'patient' => $patient->id]),
            null,
            ['title' => $record->title, 'type' => $record->type]
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('emr.flash_clinical_saved'));
    }

    public function destroyClinicalRecord(Request $request, Patient $patient, PatientClinicalRecord $record): RedirectResponse
    {
        $this->authorize('update', $patient);
        abort_unless((int) $record->patient_id === (int) $patient->id, 404);

        $old = ['title' => $record->title, 'type' => $record->type];
        $this->clinical->deactivateRecord($record);

        AuditLogger::log(
            'update',
            'patient_clinical_records',
            $record->id,
            __('emr.audit_clinical_deactivate', ['patient' => $patient->id]),
            $old,
            ['is_active' => false]
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('emr.flash_clinical_removed'));
    }

    public function qrCard(Request $request, Patient $patient): View
    {
        $this->authorize('view', $patient);

        $plain = $this->qr->issueToken($patient);
        $portalUrl = $this->qr->portalUrl($plain);

        return view('patients.qr-card', [
            'patient' => $patient,
            'lookupUrl' => $portalUrl,
        ]);
    }

    public function issuePortalLink(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('view', $patient);

        $plain = $this->qr->issueToken($patient);

        return redirect()
            ->back()
            ->with('success', __('portal.flash_link_created'))
            ->with('patient_portal_url', $this->qr->portalUrl($plain));
    }
}
