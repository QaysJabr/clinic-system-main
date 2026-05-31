<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentBookingToken;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\Scheduling\AppointmentConflictService;
use App\Services\Scheduling\AppointmentSlotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public self-booking via tokenized clinic links.
 */
final class PublicAppointmentBookingController extends Controller
{
    public function __construct(
        private readonly AppointmentSlotService $slots,
        private readonly AppointmentConflictService $conflicts,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $booking = $this->resolveToken($token);

        if (! $booking) {
            abort(404);
        }

        $clinic = Clinic::query()->findOrFail($booking->clinic_id);
        $doctors = Doctor::withoutGlobalScopes()
            ->where('clinic_id', $clinic->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('booking.public', [
            'token' => $token,
            'clinic' => $clinic,
            'doctors' => $doctors,
            'preselectedDoctorId' => $booking->doctor_id,
        ]);
    }

    public function slots(Request $request, string $token)
    {
        $booking = $this->resolveToken($token);
        abort_unless($booking, 404);

        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return response()->json([
            'slots' => $this->slots->slotsForDoctor((int) $validated['doctor_id'], $validated['date']),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $booking = $this->resolveToken($token);
        abort_unless($booking, 404);

        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $patient = $this->resolvePatient((int) $booking->clinic_id, $validated['full_name'], $validated['phone']);

        $range = $this->conflicts->assertCanBook(
            (int) $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['start_time'],
            null,
            null
        );

        $appointment = Appointment::withoutGlobalScopes()->create([
            'clinic_id' => $booking->clinic_id,
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $range->start->format('H:i'),
            'end_time' => $range->end->format('H:i'),
            'duration_minutes' => (int) $range->start->diffInMinutes($range->end),
            'status' => AppointmentStatus::Scheduled->value,
            'reason' => $validated['reason'] ?? null,
            'booking_source' => 'online',
            'public_booking_token' => $token,
        ]);

        if (! $booking->reusable) {
            $booking->forceFill([
                'used_at' => now(),
                'patient_id' => $patient->id,
                'doctor_id' => $validated['doctor_id'],
            ])->save();
        }

        return redirect()
            ->route('booking.public.confirmation', ['token' => $token, 'appointment' => $appointment->id])
            ->with('success', __('appointments.booking_confirmed'));
    }

    public function confirmation(string $token, int $appointment): View
    {
        $booking = $this->resolveToken($token, allowUsed: true);
        $appointment = Appointment::withoutGlobalScopes()->findOrFail($appointment);
        abort_unless($booking && (int) $appointment->clinic_id === (int) $booking->clinic_id, 404);

        return view('booking.confirmation', compact('appointment', 'token'));
    }

    public static function issueToken(int $clinicId, ?int $doctorId = null, bool $reusable = false): string
    {
        $plain = Str::random(48);

        AppointmentBookingToken::query()->create([
            'clinic_id' => $clinicId,
            'token' => hash('sha256', $plain),
            'reusable' => $reusable,
            'doctor_id' => $doctorId,
            'expires_at' => now()->addHours($reusable
                ? (int) config('scheduling.public_booking_clinic_token_ttl_hours', 8760)
                : (int) config('scheduling.public_booking_token_ttl_hours', 48)),
        ]);

        return $plain;
    }

    private function resolvePatient(int $clinicId, string $fullName, string $phone): Patient
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: $phone;

        $existing = Patient::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($phone, $digits) {
                $query->where('phone', $phone);
                if ($digits !== '') {
                    $query->orWhere('phone', 'like', '%'.$digits);
                }
            })
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $fileNumber = 'WEB-'.now()->format('ymdHis');

        return Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'file_number' => $fileNumber,
            'full_name' => $fullName,
            'phone' => $phone,
            'status' => 'active',
        ]);
    }

    private function resolveToken(string $plain, bool $allowUsed = false): ?AppointmentBookingToken
    {
        $hash = hash('sha256', $plain);

        $query = AppointmentBookingToken::withoutGlobalScopes()
            ->where('token', $hash)
            ->where('expires_at', '>', now());

        if (! $allowUsed) {
            $query->where(function ($q) {
                $q->where('reusable', true)->orWhereNull('used_at');
            });
        }

        return $query->first();
    }
}
