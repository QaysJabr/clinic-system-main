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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $clinicId = (int) $booking->clinic_id;

        $validated = $request->validate([
            'doctor_id' => $this->doctorIdRules($clinicId),
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

        $clinicId = (int) $booking->clinic_id;

        $validated = $request->validate([
            'doctor_id' => $this->doctorIdRules($clinicId),
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $doctorId = (int) $validated['doctor_id'];

        $appointment = DB::transaction(function () use ($booking, $validated, $clinicId, $doctorId, $token) {
            Appointment::withoutGlobalScopes()
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $validated['appointment_date'])
                ->lockForUpdate()
                ->get();

            $patient = $this->resolvePatient($clinicId, $validated['full_name'], $validated['phone']);

            $range = $this->conflicts->assertCanBook(
                $doctorId,
                $validated['appointment_date'],
                $validated['start_time'],
                null,
                null
            );

            return Appointment::withoutGlobalScopes()->create([
                'clinic_id' => $clinicId,
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $range->start->format('H:i'),
                'end_time' => $range->end->format('H:i'),
                'duration_minutes' => (int) $range->start->diffInMinutes($range->end),
                'status' => AppointmentStatus::Scheduled->value,
                'reason' => $validated['reason'] ?? null,
                'booking_source' => 'online',
                'public_booking_token' => $token,
            ]);
        });

        if (! $booking->reusable) {
            $booking->forceFill([
                'used_at' => now(),
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
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

    /**
     * @return array<int, mixed>
     */
    private function doctorIdRules(int $clinicId): array
    {
        return [
            'required',
            'integer',
            Rule::exists('doctors', 'id')->where(fn ($q) => $q
                ->where('clinic_id', $clinicId)
                ->where('status', 'active')),
        ];
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

        $clinic = Clinic::query()->with('plan')->find($clinicId);
        if ($clinic && ! $clinic->canAddPatient()) {
            throw ValidationException::withMessages([
                'full_name' => [__('patients.subscription_patient_limit')],
            ]);
        }

        return Patient::withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'file_number' => 'WEB-'.now()->format('ymdHis'),
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
