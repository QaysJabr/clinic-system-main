<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicAppointmentBookingController;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Staff;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicAppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_book_with_doctor_from_another_clinic(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $clinicA = (int) config('tenancy.default_clinic_id');

        $clinicB = Clinic::query()->create([
            'name' => 'عيادة حجز ب',
            'subscription_status' => Clinic::STATUS_ACTIVE,
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        $staffB = Staff::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'full_name' => 'طبيب ب',
            'role_type' => 'doctor',
            'status' => 'active',
        ]);

        $doctorB = Doctor::withoutGlobalScopes()->create([
            'clinic_id' => $clinicB->id,
            'staff_id' => $staffB->id,
            'full_name' => 'طبيب ب',
            'status' => 'active',
        ]);

        $plainToken = PublicAppointmentBookingController::issueToken($clinicA, null, true);

        $date = now()->addDays(2)->toDateString();

        $this->post(route('booking.public.store', ['token' => $plainToken]), [
            'doctor_id' => $doctorB->id,
            'full_name' => 'زائر',
            'phone' => '0590000001',
            'appointment_date' => $date,
            'start_time' => '10:00',
        ])->assertSessionHasErrors('doctor_id');
    }

    public function test_booking_creates_appointment_for_valid_doctor(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $clinicId = (int) config('tenancy.default_clinic_id');

        $staff = Staff::query()->create([
            'full_name' => 'طبيب حجز',
            'role_type' => 'doctor',
            'status' => 'active',
            'clinic_id' => $clinicId,
        ]);

        $doctor = Doctor::query()->create([
            'staff_id' => $staff->id,
            'full_name' => 'طبيب حجز',
            'status' => 'active',
            'clinic_id' => $clinicId,
        ]);

        $plainToken = PublicAppointmentBookingController::issueToken($clinicId, $doctor->id, true);

        $date = now()->addDays(3)->toDateString();

        $response = $this->post(route('booking.public.store', ['token' => $plainToken]), [
            'doctor_id' => $doctor->id,
            'full_name' => 'مريض حجز عام',
            'phone' => '0590000002',
            'appointment_date' => $date,
            'start_time' => '11:00',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicId,
            'booking_source' => 'online',
        ]);

        $this->assertTrue(
            Patient::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->where('phone', '0590000002')
                ->exists()
        );
    }
}
