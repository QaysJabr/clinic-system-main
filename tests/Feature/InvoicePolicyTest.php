<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_with_invoice_permission_only_sees_own_doctor_invoices(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $doctorRole = Role::findByName('doctor');
        $doctorRole->givePermissionTo('manage invoices');

        $patient = Patient::query()->create([
            'file_number' => 'INV-001',
            'full_name' => 'مريض',
            'status' => 'active',
        ]);

        $staffA = Staff::query()->create(['full_name' => 'طبيب أ', 'role_type' => 'doctor', 'status' => 'active']);
        $userA = User::factory()->create(['email' => 'doc-inv-a@test.local']);
        $staffA->update(['user_id' => $userA->id]);
        $userA->assignRole($doctorRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $userA = $userA->fresh();
        $docA = Doctor::query()->create(['staff_id' => $staffA->id, 'full_name' => 'طبيب أ', 'status' => 'active']);

        $staffB = Staff::query()->create(['full_name' => 'طبيب ب', 'role_type' => 'doctor', 'status' => 'active']);
        $docB = Doctor::query()->create(['staff_id' => $staffB->id, 'full_name' => 'طبيب ب', 'status' => 'active']);

        $visitA = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $docA->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_COMPLETED,
        ]);

        $visitB = Visit::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $docB->id,
            'appointment_id' => null,
            'visit_date' => now()->toDateString(),
            'status' => Visit::STATUS_COMPLETED,
        ]);

        $admin = User::query()->where('email', 'admin@clinic.local')->firstOrFail();
        $this->actingAs($admin);

        $ownInvoice = Invoice::query()->create([
            'patient_id' => $patient->id,
            'visit_id' => $visitA->id,
            'invoice_number' => 'INV-OWN-1',
            'total' => 100,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $otherInvoice = Invoice::query()->create([
            'patient_id' => $patient->id,
            'visit_id' => $visitB->id,
            'invoice_number' => 'INV-OTHER-1',
            'total' => 200,
            'paid' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($userA)->get(route('invoices.show', $ownInvoice))->assertOk();
        $this->actingAs($userA)->get(route('invoices.show', $otherInvoice))->assertForbidden();
    }
}
