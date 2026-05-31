<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\InAppNotificationService;
use App\Services\Scheduling\AppointmentConflictService;
use App\Support\AuditLogger;
use App\Support\Queries\AppointmentListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AppointmentConflictService $conflicts,
    ) {}

    private const AUDIT_FIELDS = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'reason',
        'notes',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scope = Appointment::query()->with(['patient:id,full_name', 'doctor:id,full_name']);
        $user = Auth::user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $scope->where('doctor_id', $doc->id);
            } else {
                $scope->whereRaw('1 = 0');
            }
        }

        $today = now()->toDateString();
        $appointmentStats = [
            'today' => (clone $scope)->whereDate('appointment_date', $today)->count(),
            'active' => (clone $scope)->whereIn('status', AppointmentStatus::blocking())->count(),
            'completed' => (clone $scope)->where('status', AppointmentStatus::Completed->value)->count(),
            'cancelled' => (clone $scope)->whereIn('status', [AppointmentStatus::Cancelled->value, AppointmentStatus::NoShow->value])->count(),
        ];

        $appointments = AppointmentListQuery::apply(clone $scope, $request)
            ->orderByDesc('appointment_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('appointments.page_title_index');

        if ($request->ajax()) {
            return view('appointments.partials.content', compact('appointments', 'pageTitle', 'appointmentStats'));
        }

        return view('appointments.index', compact('appointments', 'pageTitle', 'appointmentStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('full_name')->get();
        $doctors = Doctor::orderBy('full_name')->get();

        $pageTitle = __('appointments.page_title_create');

        if ($request->ajax()) {
            return view('appointments.partials.create', compact('patients', 'doctors', 'pageTitle'));
        }

        return view('appointments.create', compact('patients', 'doctors', 'pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validate($this->appointmentRules(), [], $this->appointmentValidationAttributes());

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if (! $doc || (int) $validated['doctor_id'] !== (int) $doc->id) {
                abort(403, __('appointments.error_assign_other_doctor_store'));
            }
        }

        $range = $this->conflicts->assertCanBook(
            (int) $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['start_time'],
            $validated['end_time'] ?? null,
            null
        );

        $payload = $request->only(self::AUDIT_FIELDS);
        $payload['start_time'] = $range->start->format('H:i');
        $payload['end_time'] = $range->end->format('H:i');
        $payload['duration_minutes'] = (int) $range->start->diffInMinutes($range->end);

        $appointment = Appointment::create($payload);

        AuditLogger::log(
            'create',
            'appointments',
            $appointment->id,
            __('appointments.audit_create', ['id' => $appointment->id]),
            null,
            $this->appointmentSnapshot($appointment)
        );

        $appointment->load(['patient', 'doctor']);
        app(InAppNotificationService::class)->pushForAppointmentIfRelevant($appointment);

        return redirect()->route('appointments.index')->with('success', __('appointments.flash_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('update', $appointment);
        $patients = Patient::orderBy('full_name')->get();
        $doctors = Doctor::orderBy('full_name')->get();

        $pageTitle = __('appointments.page_title_edit');

        if ($request->ajax()) {
            return view('appointments.partials.edit', compact('appointment', 'patients', 'doctors', 'pageTitle'));
        }

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('update', $appointment);

        $validated = $request->validate($this->appointmentRules(), [], $this->appointmentValidationAttributes());

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if (! $doc || (int) $validated['doctor_id'] !== (int) $doc->id) {
                abort(403, __('appointments.error_assign_other_doctor_update'));
            }
        }

        $range = $this->conflicts->assertCanBook(
            (int) $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['start_time'],
            $validated['end_time'] ?? null,
            null,
            (int) $appointment->id,
        );

        $old = $this->appointmentSnapshot($appointment);
        $payload = $request->only(self::AUDIT_FIELDS);
        $payload['start_time'] = $range->start->format('H:i');
        $payload['end_time'] = $range->end->format('H:i');
        $payload['duration_minutes'] = (int) $range->start->diffInMinutes($range->end);
        $appointment->update($payload);

        AuditLogger::log(
            'update',
            'appointments',
            $appointment->id,
            __('appointments.audit_update', ['id' => $appointment->id]),
            $old,
            $this->appointmentSnapshot($appointment->fresh())
        );

        $appointment->refresh()->load(['patient', 'doctor']);
        app(InAppNotificationService::class)->pushForAppointmentIfRelevant($appointment);

        return redirect()->route('appointments.index')->with('success', __('appointments.flash_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('delete', $appointment);
        $old = $this->appointmentSnapshot($appointment);
        $aid = $appointment->id;
        $appointment->delete();

        AuditLogger::log(
            'delete',
            'appointments',
            $aid,
            __('appointments.audit_delete', ['id' => $aid]),
            $old,
            null
        );

        return redirect()->route('appointments.index')->with('success', __('appointments.flash_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function appointmentSnapshot(Appointment $a): array
    {
        $data = $a->only(self::AUDIT_FIELDS);
        if (! empty($data['appointment_date'])) {
            $data['appointment_date'] = $a->appointment_date?->format('Y-m-d');
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function appointmentRules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => ['required', 'string', 'in:'.implode(',', AppointmentStatus::values())],
            'reason' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function appointmentValidationAttributes(): array
    {
        return [
            'patient_id' => __('appointments.attr_patient_id'),
            'doctor_id' => __('appointments.attr_doctor_id'),
            'appointment_date' => __('appointments.attr_appointment_date'),
            'start_time' => __('appointments.attr_start_time'),
            'end_time' => __('appointments.attr_end_time'),
            'status' => __('appointments.attr_status'),
            'reason' => __('appointments.attr_reason'),
            'notes' => __('appointments.attr_notes'),
        ];
    }
}
