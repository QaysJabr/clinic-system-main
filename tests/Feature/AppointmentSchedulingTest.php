<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\Scheduling\AppointmentConflictService;
use App\Services\Scheduling\AppointmentLifecycleService;
use App\Services\Scheduling\AppointmentSlotService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private function seedPatientAndDoctor(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $patient = Patient::query()->create([
            'file_number' => 'SCH-001',
            'full_name' => 'مريض اختبار',
            'status' => 'active',
        ]);

        $doctor = Doctor::query()->create([
            'full_name' => 'طبيب اختبار',
            'status' => 'active',
        ]);

        return [$patient, $doctor];
    }

    public function test_conflict_service_rejects_overlapping_appointments(): void
    {
        [$patient, $doctor] = $this->seedPatientAndDoctor();
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $date = now()->addDay()->toDateString();

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => AppointmentStatus::Scheduled->value,
        ]);

        $this->expectException(ValidationException::class);

        app(AppointmentConflictService::class)->assertCanBook(
            (int) $doctor->id,
            $date,
            '10:15',
            '10:45',
            null
        );
    }

    public function test_slot_service_marks_overlapping_slots_unavailable(): void
    {
        [$patient, $doctor] = $this->seedPatientAndDoctor();
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $date = now()->addDay()->toDateString();

        Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '10:30',
            'status' => AppointmentStatus::Scheduled->value,
        ]);

        $slots = app(AppointmentSlotService::class)->slotsForDoctor((int) $doctor->id, $date);
        $ten = collect($slots)->firstWhere('start', '10:00');

        $this->assertNotNull($ten);
        $this->assertFalse($ten['available']);
    }

    public function test_lifecycle_allows_scheduled_to_confirmed_and_check_in(): void
    {
        [$patient, $doctor] = $this->seedPatientAndDoctor();
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '11:00',
            'end_time' => '11:30',
            'status' => AppointmentStatus::Scheduled->value,
        ]);

        $lifecycle = app(AppointmentLifecycleService::class);
        $confirmed = $lifecycle->transition($appointment, AppointmentStatus::Confirmed);
        $this->assertSame(AppointmentStatus::Confirmed->value, $confirmed->status);

        $visit = $lifecycle->checkIn($confirmed->fresh());
        $this->assertNotNull($visit->id);
        $this->assertSame(AppointmentStatus::CheckedIn->value, $appointment->fresh()->status);
    }

    public function test_lifecycle_rejects_invalid_transition(): void
    {
        [$patient, $doctor] = $this->seedPatientAndDoctor();

        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '12:30',
            'status' => AppointmentStatus::Completed->value,
        ]);

        $this->expectException(ValidationException::class);

        app(AppointmentLifecycleService::class)->transition($appointment, AppointmentStatus::Scheduled);
    }

    public function test_calendar_events_endpoint_returns_json(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('appointments.calendar.events', [
                'start' => now()->startOfMonth()->toDateString(),
                'end' => now()->endOfMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertJsonIsArray();
    }
}
