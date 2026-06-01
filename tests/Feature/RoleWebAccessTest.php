<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Ensures clinic roles reach allowed pages and are blocked elsewhere.
 */
final class RoleWebAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $doctorUser;

    private User $receptionistUser;

    private User $accountantUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $clinicId = (int) config('tenancy.default_clinic_id');

        $this->doctorUser = $this->makeClinicUser('doctor', 'doctor-role@test.local', $clinicId, linkDoctor: true);
        $this->receptionistUser = $this->makeClinicUser('receptionist', 'receptionist-role@test.local', $clinicId);
        $this->accountantUser = $this->makeClinicUser('accountant', 'accountant-role@test.local', $clinicId);
    }

    public function test_doctor_can_access_core_clinical_pages(): void
    {
        $allowed = [
            'dashboard',
            'clinical.dashboard',
            'patients.index',
            'appointments.index',
            'visits.index',
            'doctor-earnings.index',
        ];

        foreach ($allowed as $route) {
            $this->actingAs($this->doctorUser)
                ->followingRedirects()
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_doctor_cannot_access_admin_finance_and_staff_pages(): void
    {
        $forbidden = [
            'doctors.index',
            'invoices.index',
            'expenses.index',
            'staff.index',
            'reports.index',
            'users.index',
            'settings.edit',
            'inventory.dashboard',
        ];

        foreach ($forbidden as $route) {
            $this->actingAs($this->doctorUser)
                ->get(route($route))
                ->assertForbidden();
        }
    }

    public function test_receptionist_can_access_front_desk_and_operations(): void
    {
        $allowed = [
            'dashboard',
            'reception.dashboard',
            'patients.index',
            'doctors.index',
            'appointments.index',
            'visits.index',
            'invoices.index',
            'inventory.dashboard',
        ];

        foreach ($allowed as $route) {
            $this->actingAs($this->receptionistUser)
                ->followingRedirects()
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_receptionist_cannot_access_system_admin_pages(): void
    {
        $forbidden = [
            'users.index',
            'settings.edit',
            'audit-logs.index',
            'backups.index',
        ];

        foreach ($forbidden as $route) {
            $this->actingAs($this->receptionistUser)
                ->get(route($route))
                ->assertForbidden();
        }
    }

    public function test_accountant_can_access_finance_pages(): void
    {
        $allowed = [
            'dashboard',
            'patients.index',
            'invoices.index',
            'expenses.index',
            'reports.index',
            'doctor-earnings.index',
            'staff-payments.index',
            'inventory.dashboard',
        ];

        foreach ($allowed as $route) {
            $this->actingAs($this->accountantUser)
                ->followingRedirects()
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_accountant_cannot_access_clinical_desk_or_system_admin(): void
    {
        $forbidden = [
            'clinical.dashboard',
            'users.index',
            'settings.edit',
            'backups.index',
        ];

        foreach ($forbidden as $route) {
            $this->actingAs($this->accountantUser)
                ->get(route($route))
                ->assertForbidden();
        }
    }

    public function test_doctor_login_redirects_to_dashboard(): void
    {
        $this->post('/login', [
            'email' => $this->doctorUser->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    private function makeClinicUser(string $roleName, string $email, int $clinicId, bool $linkDoctor = false): User
    {
        $role = Role::findByName($roleName, 'web');

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password'),
            'clinic_id' => $clinicId,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        if ($linkDoctor) {
            $staff = Staff::query()->create([
                'clinic_id' => $clinicId,
                'user_id' => $user->id,
                'full_name' => 'طبيب اختبار',
                'role_type' => 'doctor',
                'status' => 'active',
            ]);
            Doctor::query()->create([
                'clinic_id' => $clinicId,
                'staff_id' => $staff->id,
                'full_name' => 'طبيب اختبار',
                'status' => 'active',
            ]);
        }

        return $user->fresh();
    }
}
