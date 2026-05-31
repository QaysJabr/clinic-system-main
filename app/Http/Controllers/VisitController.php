<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\InventoryStockMovement;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\DoctorEarningSyncService;
use App\Services\Emr\PatientDiagnosisService;
use App\Services\Emr\VisitSoapService;
use App\Services\VisitStructuredEmrService;
use App\Support\AuditLogger;
use App\Support\ClinicBusinessRules;
use App\Support\Queries\VisitListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VisitController extends Controller
{
    use AuthorizesRequests;

    private const AUDIT_FIELDS = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_date',
        'chief_complaint',
        'diagnosis',
        'treatment_plan',
        'procedures',
        'prescriptions',
        'notes',
        'status',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Visit::class);

        $scope = Visit::query()->with([
            'patient:id,full_name,file_number',
            'doctor:id,full_name',
            'appointment:id,appointment_date',
        ]);

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $scope->where('doctor_id', $doc->id);
            } else {
                $scope->whereRaw('1 = 0');
            }
        }

        $today = now()->toDateString();
        $visitStats = [
            'today' => (clone $scope)->whereDate('visit_date', $today)->count(),
            'active' => (clone $scope)->whereIn('status', [Visit::STATUS_WAITING, Visit::STATUS_IN_PROGRESS])->count(),
            'completed' => (clone $scope)->where('status', Visit::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $scope)->where('status', Visit::STATUS_CANCELLED)->count(),
        ];

        $visits = VisitListQuery::apply(clone $scope, $request)
            ->orderByDesc('visit_date')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('visits.page_title_index');

        if ($request->ajax()) {
            return view('visits.partials.content', compact('visits', 'pageTitle', 'visitStats'));
        }

        return view('visits.index', compact('visits', 'pageTitle', 'visitStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Visit::class);

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'full_name', 'file_number']);
        $user = auth()->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $linked = $user->linkedDoctor();
            $doctors = $linked ? collect([$linked]) : collect();
        } else {
            $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        }
        $appointments = Appointment::query()
            ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'start_time'])
            ->with(['patient:id,full_name'])
            ->orderByDesc('appointment_date')
            ->get();

        $soap = ['subjective' => '', 'objective' => '', 'assessment' => '', 'plan' => '', 'notes' => ''];
        $pageTitle = __('visits.page_title_create');

        if ($request->ajax()) {
            return view('visits.partials.create', compact('patients', 'doctors', 'appointments', 'pageTitle', 'soap'));
        }

        return view('visits.create', compact('patients', 'doctors', 'appointments', 'pageTitle', 'soap'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Visit::class);

        $request->validate($this->visitValidationRules(), [], $this->visitValidationAttributes());

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if (! $doc || (int) $request->input('doctor_id') !== (int) $doc->id) {
                throw ValidationException::withMessages([
                    'doctor_id' => __('visits.error_doctor_must_be_self'),
                ]);
            }
        }

        $this->assertAppointmentMatchesVisitSelection($request);

        $this->assertInvoiceRequiredWhenCompletingVisitOnCreate($request);

        $visit = Visit::create($request->only(self::AUDIT_FIELDS));

        app(VisitStructuredEmrService::class)->syncFromRequest($visit, $request);
        app(VisitSoapService::class)->syncFromRequest($visit->fresh(), $request);
        app(PatientDiagnosisService::class)->recordFromVisit($visit->fresh());

        AuditLogger::log(
            'create',
            'visits',
            $visit->id,
            __('visits.audit_create', ['id' => $visit->id]),
            null,
            $this->visitSnapshot($visit)
        );

        if ($visit->isCompleted()) {
            app(DoctorEarningSyncService::class)->syncInvoicesForVisit((int) $visit->id);
        }

        return redirect()->route('visits.index')->with('success', __('visits.flash_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): View
    {
        $visit = Visit::query()
            ->with([
                'patient',
                'doctor',
                'appointment',
                'structuredProcedures',
                'structuredPrescriptions',
                'soapNote',
            ])
            ->findOrFail($id);

        $this->authorize('view', $visit);

        $attachments = $visit->attachments()
            ->select([
                'id',
                'patient_id',
                'visit_id',
                'file_name',
                'file_type',
                'file_size',
                'notes',
                'uploaded_by',
                'created_at',
            ])
            ->latest()
            ->get();

        $soap = app(VisitSoapService::class)->resolveForForm($visit);
        $pageTitle = __('visits.page_title_show');

        $inventoryMovements = collect();
        if ($request->user()?->can('view inventory') || $request->user()?->can('manage inventory')) {
            $inventoryMovements = InventoryStockMovement::query()
                ->where('visit_id', $visit->id)
                ->with('item:id,name')
                ->latest('movement_at')
                ->get();
        }

        if ($request->ajax()) {
            return view('visits.partials.show', compact('visit', 'attachments', 'pageTitle', 'soap', 'inventoryMovements'));
        }

        return view('visits.show', compact('visit', 'attachments', 'pageTitle', 'soap', 'inventoryMovements'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $visit = Visit::findOrFail($id);
        $this->authorize('update', $visit);

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'full_name', 'file_number']);
        $user = auth()->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $linked = $user->linkedDoctor();
            $doctors = $linked ? collect([$linked]) : collect();
        } else {
            $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        }
        $appointments = Appointment::query()
            ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'start_time'])
            ->with(['patient:id,full_name'])
            ->orderByDesc('appointment_date')
            ->get();
        $visit->load(['structuredProcedures', 'structuredPrescriptions', 'soapNote']);
        $soap = app(VisitSoapService::class)->resolveForForm($visit);

        $pageTitle = __('visits.page_title_edit');

        if ($request->ajax()) {
            return view('visits.partials.edit', compact('visit', 'patients', 'doctors', 'appointments', 'pageTitle', 'soap'));
        }

        return view('visits.edit', compact('visit', 'patients', 'doctors', 'appointments', 'pageTitle', 'soap'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $visit = Visit::findOrFail($id);

        $this->authorize('update', $visit);

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $request->merge([
                'patient_id' => $visit->patient_id,
                'doctor_id' => $visit->doctor_id,
                'appointment_id' => $visit->appointment_id,
                'visit_date' => $visit->visit_date?->format('Y-m-d'),
            ]);
        }

        $request->validate($this->visitValidationRules(), [], $this->visitValidationAttributes());

        $this->assertAppointmentMatchesVisitSelection($request);

        $this->assertInvoiceRequiredWhenCompletingVisitOnUpdate($request, $visit);

        $old = $this->visitSnapshot($visit);
        $visit->update($request->only(self::AUDIT_FIELDS));

        app(VisitStructuredEmrService::class)->syncFromRequest($visit->fresh(), $request);
        app(VisitSoapService::class)->syncFromRequest($visit->fresh(), $request);
        app(PatientDiagnosisService::class)->recordFromVisit($visit->fresh());

        if ($visit->wasChanged(['doctor_id', 'patient_id'])) {
            $this->resyncInvoicesLinkedToVisit($visit);
        }

        $visit->refresh();

        AuditLogger::log(
            'update',
            'visits',
            $visit->id,
            __('visits.audit_update', ['id' => $visit->id]),
            $old,
            $this->visitSnapshot($visit)
        );

        if ($visit->isCompleted()) {
            app(DoctorEarningSyncService::class)->syncInvoicesForVisit((int) $visit->id);
        }

        return redirect()->route('visits.index')->with('success', __('visits.flash_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $visit = Visit::findOrFail($id);

        $this->authorize('delete', $visit);

        $old = $this->visitSnapshot($visit);
        $vid = $visit->id;
        $visit->delete();

        AuditLogger::log(
            'delete',
            'visits',
            $vid,
            __('visits.audit_delete', ['id' => $vid]),
            $old,
            null
        );

        return redirect()->route('visits.index')->with('success', __('visits.flash_deleted'));
    }

    /**
     * Re-align invoices linked to this visit after patient or doctor changes on the visit
     * (updates invoice patient_id and triggers invoice observers including doctor earnings).
     */
    private function resyncInvoicesLinkedToVisit(Visit $visit): void
    {
        Invoice::query()
            ->where('visit_id', $visit->id)
            ->get()
            ->each(function (Invoice $invoice) use ($visit): void {
                $invoice->patient_id = $visit->patient_id;
                $invoice->save();
            });
    }

    private function assertInvoiceRequiredWhenCompletingVisitOnCreate(Request $request): void
    {
        if ($request->input('status') !== Visit::STATUS_COMPLETED) {
            return;
        }

        if (! ClinicBusinessRules::settings()->require_invoice_for_visit) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => __('visits.error_complete_new_visit_invoice'),
        ]);
    }

    private function assertInvoiceRequiredWhenCompletingVisitOnUpdate(Request $request, Visit $visit): void
    {
        if ($request->input('status') !== Visit::STATUS_COMPLETED) {
            return;
        }

        if (! ClinicBusinessRules::settings()->require_invoice_for_visit) {
            return;
        }

        if (! ClinicBusinessRules::visitHasInvoice((int) $visit->id)) {
            throw ValidationException::withMessages([
                'status' => __('visits.error_complete_visit_without_invoice'),
            ]);
        }
    }

    private function assertAppointmentMatchesVisitSelection(Request $request): void
    {
        if (! $request->filled('appointment_id')) {
            return;
        }

        $appointment = Appointment::query()
            ->select(['id', 'patient_id', 'doctor_id'])
            ->find((int) $request->input('appointment_id'));

        if (! $appointment) {
            return;
        }

        if ((int) $appointment->patient_id !== (int) $request->input('patient_id')) {
            throw ValidationException::withMessages([
                'appointment_id' => __('appointments.error_visit_appointment_patient_mismatch'),
            ]);
        }

        if ((int) $appointment->doctor_id !== (int) $request->input('doctor_id')) {
            throw ValidationException::withMessages([
                'appointment_id' => __('appointments.error_visit_appointment_doctor_mismatch'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function visitSnapshot(Visit $visit): array
    {
        $data = $visit->only(self::AUDIT_FIELDS);
        if (! empty($data['visit_date'])) {
            $data['visit_date'] = $visit->visit_date?->format('Y-m-d');
        }

        return $data;
    }

    /**
     * @return array<string, string|array<int|string, string|array<int, string>>>
     */
    private function visitValidationRules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'procedures' => 'nullable|string',
            'prescriptions' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:waiting,in_progress,completed,cancelled',
            'procedure_rows' => 'nullable|array',
            'procedure_rows.*.name' => 'nullable|string|max:500',
            'procedure_rows.*.notes' => 'nullable|string|max:5000',
            'rx_rows' => 'nullable|array',
            'rx_rows.*.medication_name' => 'nullable|string|max:500',
            'rx_rows.*.dosage' => 'nullable|string|max:255',
            'rx_rows.*.frequency' => 'nullable|string|max:255',
            'rx_rows.*.duration' => 'nullable|string|max:255',
            'rx_rows.*.notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function visitValidationAttributes(): array
    {
        return [
            'patient_id' => __('visits.attr_patient_id'),
            'doctor_id' => __('visits.attr_doctor_id'),
            'appointment_id' => __('visits.attr_appointment_id'),
            'visit_date' => __('visits.attr_visit_date'),
            'chief_complaint' => __('visits.attr_chief_complaint'),
            'diagnosis' => __('visits.attr_diagnosis'),
            'treatment_plan' => __('visits.attr_treatment_plan'),
            'procedures' => __('visits.attr_procedures'),
            'prescriptions' => __('visits.attr_prescriptions'),
            'notes' => __('visits.attr_notes'),
            'status' => __('visits.attr_status'),
            'procedure_rows' => __('visits.attr_procedure_rows'),
            'procedure_rows.*.name' => __('visits.attr_procedure_row_name'),
            'procedure_rows.*.notes' => __('visits.attr_procedure_row_notes'),
            'rx_rows' => __('visits.attr_rx_rows'),
            'rx_rows.*.medication_name' => __('visits.attr_rx_medication_name'),
            'rx_rows.*.dosage' => __('visits.attr_rx_dosage'),
            'rx_rows.*.frequency' => __('visits.attr_rx_frequency'),
            'rx_rows.*.duration' => __('visits.attr_rx_duration'),
            'rx_rows.*.notes' => __('visits.attr_rx_notes'),
        ];
    }
}
