<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use App\Support\AuditLogger;
use App\Support\Queries\PatientListQuery;
use App\Support\TenantValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    use AuthorizesRequests;

    private const AUDIT_FIELDS = [
        'file_number',
        'full_name',
        'phone',
        'date_of_birth',
        'gender',
        'national_id',
        'address',
        'notes',
        'status',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scope = $this->scopedPatientQuery();

        $patientStats = [
            'total' => (clone $scope)->count(),
            'active' => (clone $scope)->where('status', 'active')->count(),
            'inactive' => (clone $scope)->where('status', 'inactive')->count(),
        ];

        $patients = PatientListQuery::apply(clone $scope, $request)
            ->orderBy('full_name')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('patients.page_title_index');

        if ($request->ajax()) {
            return view('patients.partials.content', compact('patients', 'pageTitle', 'patientStats'));
        }

        return view('patients.index', compact('patients', 'pageTitle', 'patientStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $pageTitle = __('patients.page_title_create');

        if ($request->ajax()) {
            return view('patients.partials.create', compact('pageTitle'));
        }

        return view('patients.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);
        $clinicId = TenantValidation::clinicIdForRules();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'national_id' => ['nullable', 'string', Rule::unique('patients', 'national_id')->where(fn ($q) => $q->where('clinic_id', $clinicId))],
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($clinicId) {
            $clinic = Clinic::query()->with('plan')->find($clinicId);
            if ($clinic && ! $clinic->canAddPatient()) {
                throw ValidationException::withMessages([
                    'full_name' => __('patients.subscription_patient_limit'),
                ]);
            }
        }

        $patient = Patient::create($request->only(array_diff(self::AUDIT_FIELDS, ['file_number'])));

        AuditLogger::log(
            'create',
            'patients',
            $patient->id,
            __('patients.audit_create_body', ['name' => $patient->full_name, 'file' => $patient->file_number]),
            null,
            $this->patientSnapshot($patient)
        );

        return redirect()->route('patients.index')->with('success', __('patients.flash_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $patient = Patient::query()->findOrFail($id);

        return app(PatientEmrController::class)->chart($request, $patient);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('update', $patient);
        $pageTitle = __('patients.page_title_edit');

        if ($request->ajax()) {
            return view('patients.partials.edit', compact('patient', 'pageTitle'));
        }

        return view('patients.edit', compact('patient', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('update', $patient);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'national_id' => ['nullable', 'string', Rule::unique('patients', 'national_id')->ignore($patient->id)->where(fn ($q) => $q->where('clinic_id', $patient->clinic_id))],
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $old = $this->patientSnapshot($patient);

        $patient->update($request->only(array_diff(self::AUDIT_FIELDS, ['file_number'])));

        AuditLogger::log(
            'update',
            'patients',
            $patient->id,
            __('patients.audit_update_body', ['name' => $patient->full_name, 'file' => $patient->file_number]),
            $old,
            $this->patientSnapshot($patient->fresh())
        );

        return redirect()->route('patients.index')->with('success', __('patients.flash_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('delete', $patient);
        $old = $this->patientSnapshot($patient);
        $pid = $patient->id;
        $label = $patient->full_name.' ('.$patient->file_number.')';
        $patient->delete();

        AuditLogger::log(
            'delete',
            'patients',
            $pid,
            __('patients.audit_delete_body', ['label' => $label]),
            $old,
            null
        );

        return redirect()->route('patients.index')->with('success', __('patients.flash_deleted'));
    }

    private function scopedPatientQuery(): Builder
    {
        $query = Patient::query();

        $user = Auth::user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where(function ($q) use ($doc) {
                    $q->whereHas('visits', fn ($visitQ) => $visitQ->where('doctor_id', $doc->id))
                        ->orWhereHas('appointments', fn ($apptQ) => $apptQ->where('doctor_id', $doc->id));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function patientSnapshot(Patient $patient): array
    {
        $data = $patient->only(self::AUDIT_FIELDS);
        $data['date_of_birth'] = $patient->date_of_birth?->format('Y-m-d');

        return $data;
    }
}
