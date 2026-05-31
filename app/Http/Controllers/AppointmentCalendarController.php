<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\Scheduling\AppointmentCalendarService;
use App\Services\Scheduling\AppointmentLifecycleService;
use App\Support\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AppointmentCalendarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AppointmentCalendarService $calendar,
        private readonly AppointmentLifecycleService $lifecycle,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $statuses = AppointmentStatus::cases();
        $pageTitle = __('appointments.page_title_calendar');

        if ($request->ajax()) {
            return view('appointments.partials.calendar', compact('doctors', 'statuses', 'pageTitle'));
        }

        return view('appointments.calendar', compact('doctors', 'statuses', 'pageTitle'));
    }

    public function events(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
        ]);

        $user = $request->user();
        $doctorId = isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null;

        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doctorId = optional($user->linkedDoctor())->id;
        }

        return response()->json(
            $this->calendar->events(
                $validated['start'],
                $validated['end'],
                $doctorId
            )
        );
    }

    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['nullable', 'date', 'after:start'],
        ]);

        $updated = $this->calendar->reschedule(
            $appointment,
            $validated['start'],
            $validated['end'] ?? null
        );

        AuditLogger::log(
            'update',
            'appointments',
            $appointment->id,
            __('appointments.audit_calendar_reschedule', ['id' => $appointment->id]),
            null,
            ['start' => $validated['start'], 'end' => $validated['end'] ?? null]
        );

        return response()->json([
            'ok' => true,
            'event' => $this->calendar->toEvent($updated),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', AppointmentStatus::values())],
        ]);

        $status = AppointmentStatus::from($validated['status']);
        $updated = $this->lifecycle->transition($appointment, $status);

        return response()->json([
            'ok' => true,
            'status' => $updated->status,
            'event' => $this->calendar->toEvent($updated->load(['patient', 'doctor'])),
        ]);
    }

    public function checkIn(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $visit = $this->lifecycle->checkIn($appointment);

        return response()->json([
            'ok' => true,
            'visit_id' => $visit->id,
            'visit_url' => route('visits.show', $visit),
            'event' => $this->calendar->toEvent($appointment->fresh(['patient', 'doctor'])),
        ]);
    }
}
