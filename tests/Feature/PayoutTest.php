<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_payout_marks_pending_rows_and_sets_paid_at(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $accountant = User::factory()->create(['email' => 'acc-payout@test.local']);
        $accountant->assignRole(Role::findByName('accountant'));

        $doctor = Doctor::query()->create([
            'staff_id' => null,
            'full_name' => 'بدون موظف',
            'status' => 'active',
        ]);

        $e1 = DoctorEarning::query()->create([
            'doctor_id' => $doctor->id,
            'invoice_id' => null,
            'visit_id' => null,
            'total_amount' => 50,
            'percentage_rate' => 10,
            'earning_amount' => 5,
            'status' => DoctorEarning::STATUS_PENDING,
        ]);

        $e2 = DoctorEarning::query()->create([
            'doctor_id' => $doctor->id,
            'invoice_id' => null,
            'visit_id' => null,
            'total_amount' => 80,
            'percentage_rate' => 10,
            'earning_amount' => 8,
            'status' => DoctorEarning::STATUS_PENDING,
        ]);

        $this->actingAs($accountant)->post(route('doctor-earnings.batch-pay'), [
            'doctor_id' => $doctor->id,
        ])->assertRedirect(route('doctor-earnings.index', ['doctor_id' => $doctor->id]));

        $e1->refresh();
        $e2->refresh();

        $this->assertSame(DoctorEarning::STATUS_PAID, $e1->status);
        $this->assertSame(DoctorEarning::STATUS_PAID, $e2->status);
        $this->assertNotNull($e1->paid_at);
        $this->assertNotNull($e2->paid_at);
    }
}
