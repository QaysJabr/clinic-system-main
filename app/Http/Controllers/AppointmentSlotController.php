<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\Scheduling\AppointmentSlotService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AppointmentSlotController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AppointmentSlotService $slots,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return response()->json([
            'slots' => $this->slots->slotsForDoctor(
                (int) $validated['doctor_id'],
                $validated['date']
            ),
        ]);
    }
}
