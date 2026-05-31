<?php

namespace Tests\Feature;

use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\Scheduling\AppointmentReminderPlanner;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentSmsReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'reminders.sms.enabled' => true,
            'reminders.sms.driver' => 'log',
        ]);
    }

    public function test_planner_creates_sms_reminder_and_log_driver_records_send(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $patient = Patient::query()->create([
            'file_number' => 'SMS-001',
            'full_name' => 'مريض SMS',
            'phone' => '0597360027',
            'status' => 'active',
        ]);

        $doctor = Doctor::query()->create(['full_name' => 'Dr SMS', 'status' => 'active']);

        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:15',
            'status' => 'scheduled',
        ]);

        app(AppointmentReminderPlanner::class)->syncForAppointment($appointment);

        $reminder = AppointmentReminder::query()
            ->where('appointment_id', $appointment->id)
            ->where('channel', AppointmentReminder::CHANNEL_SMS)
            ->first();

        $this->assertNotNull($reminder);

        $reminder->update(['scheduled_for' => now()->subMinute()]);

        (new SendAppointmentReminderJob($reminder->id))->handle(app(\App\Services\Reminders\ReminderChannelRegistry::class));

        $reminder->refresh();
        $this->assertSame(AppointmentReminder::STATUS_SENT, $reminder->status);
    }
}
