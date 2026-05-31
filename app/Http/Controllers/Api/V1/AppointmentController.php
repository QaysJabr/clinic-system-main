<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\AppointmentResource;
use App\Http\Resources\Api\VisitResource;
use App\Models\Appointment;
use App\Services\Api\AppointmentApiService;
use App\Services\Scheduling\AppointmentLifecycleService;
use App\Support\Queries\AppointmentListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AppointmentController extends ApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $query = AppointmentListQuery::fromRequest($request);
        $user = $request->user();

        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where('doctor_id', $doc->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (! $request->filled('date')) {
            $query->whereDate('appointment_date', now()->toDateString());
        }

        $paginator = $query
            ->orderBy('start_time')
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        return $this->ok(
            AppointmentResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);
        $appointment->load(['patient:id,full_name', 'doctor:id,full_name']);

        return $this->ok(new AppointmentResource($appointment));
    }

    public function store(Request $request, AppointmentApiService $appointments): JsonResponse
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validate(
            AppointmentApiService::validationRules(),
            [],
            $this->validationAttributes(),
        );

        $user = $request->user();
        abort_if($user === null, 401);

        $appointment = $appointments->create($user, $validated);

        return $this->created(new AppointmentResource($appointment));
    }

    public function update(Request $request, Appointment $appointment, AppointmentApiService $appointments): JsonResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate(
            AppointmentApiService::validationRules($appointment),
            [],
            $this->validationAttributes(),
        );

        $user = $request->user();
        abort_if($user === null, 401);

        $appointment = $appointments->update($user, $appointment, $validated);

        return $this->ok(new AppointmentResource($appointment));
    }

    public function checkIn(Appointment $appointment, AppointmentLifecycleService $lifecycle): JsonResponse
    {
        $this->authorize('update', $appointment);

        $visit = $lifecycle->checkIn($appointment);
        $visit->load(['patient:id,full_name,file_number', 'doctor:id,full_name']);
        $appointment->load(['patient:id,full_name', 'doctor:id,full_name']);

        return $this->ok([
            'appointment' => new AppointmentResource($appointment->fresh(['patient:id,full_name', 'doctor:id,full_name'])),
            'visit' => new VisitResource($visit),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
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
