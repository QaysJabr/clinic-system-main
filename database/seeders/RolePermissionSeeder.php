<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use App\Support\ClinicPermissions;
use App\Support\SecureSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (ClinicPermissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        $receptionist = Role::findOrCreate('receptionist', 'web');
        $receptionist->syncPermissions([
            ClinicPermissions::VIEW_DASHBOARD,
            ClinicPermissions::VIEW_NOTIFICATIONS,
            ClinicPermissions::VIEW_ATTACHMENTS,
            ClinicPermissions::MANAGE_PATIENTS,
            ClinicPermissions::MANAGE_DOCTORS,
            ClinicPermissions::MANAGE_APPOINTMENTS,
            ClinicPermissions::MANAGE_VISITS,
            ClinicPermissions::MANAGE_INVOICES,
            ClinicPermissions::MANAGE_PAYMENTS,
            ClinicPermissions::VIEW_REPORTS,
            ClinicPermissions::MANAGE_EXPENSES,
            ClinicPermissions::MANAGE_EXPENSE_CATEGORIES,
            ClinicPermissions::MANAGE_STAFF,
            ClinicPermissions::MANAGE_STAFF_PAYROLL,
            ClinicPermissions::MANAGE_PAYROLL,
            ClinicPermissions::VIEW_DOCTOR_EARNINGS,
            ClinicPermissions::MANAGE_DOCTOR_EARNINGS,
            ClinicPermissions::MANAGE_INVENTORY,
            ClinicPermissions::VIEW_INVENTORY,
        ]);

        $doctor = Role::findOrCreate('doctor', 'web');
        $doctor->syncPermissions([
            ClinicPermissions::VIEW_DASHBOARD,
            ClinicPermissions::VIEW_NOTIFICATIONS,
            ClinicPermissions::MANAGE_ATTACHMENTS,
            ClinicPermissions::MANAGE_PATIENTS,
            ClinicPermissions::MANAGE_APPOINTMENTS,
            ClinicPermissions::MANAGE_VISITS,
            ClinicPermissions::VIEW_DOCTOR_EARNINGS,
        ]);

        $accountant = Role::findOrCreate('accountant', 'web');
        $accountant->syncPermissions([
            ClinicPermissions::VIEW_DASHBOARD,
            ClinicPermissions::VIEW_NOTIFICATIONS,
            ClinicPermissions::MANAGE_INVOICES,
            ClinicPermissions::MANAGE_PAYMENTS,
            ClinicPermissions::VIEW_REPORTS,
            ClinicPermissions::MANAGE_EXPENSES,
            ClinicPermissions::MANAGE_EXPENSE_CATEGORIES,
            ClinicPermissions::MANAGE_STAFF,
            ClinicPermissions::MANAGE_STAFF_PAYROLL,
            ClinicPermissions::MANAGE_PAYROLL,
            ClinicPermissions::VIEW_DOCTOR_EARNINGS,
            ClinicPermissions::MANAGE_DOCTOR_EARNINGS,
            ClinicPermissions::VIEW_ATTACHMENTS,
            ClinicPermissions::MANAGE_PATIENTS,
            ClinicPermissions::MANAGE_DOCTORS,
            ClinicPermissions::MANAGE_APPOINTMENTS,
            ClinicPermissions::MANAGE_VISITS,
            ClinicPermissions::VIEW_INVENTORY,
        ]);

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

        $adminEmail = config('seeding.admin_email', 'admin@clinic.local');
        $adminPassword = SecureSeeder::password(
            is_string(config('seeding.admin_password')) ? config('seeding.admin_password') : null,
            'clinic admin',
        );

        $adminUser = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'مدير العيادة',
                'password' => $adminPassword,
                'clinic_id' => config('tenancy.default_clinic_id'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->forceFill([
            'password' => $adminPassword,
            'clinic_id' => $adminUser->clinic_id ?? config('tenancy.default_clinic_id'),
            'email_verified_at' => $adminUser->email_verified_at ?? now(),
        ])->save();
        if ($adminUser->clinic_id === null) {
            $adminUser->forceFill(['clinic_id' => config('tenancy.default_clinic_id')])->save();
        }
        $adminUser->syncRoles(['admin']);

        Clinic::query()
            ->whereKey(config('tenancy.default_clinic_id'))
            ->update([
                'is_active' => true,
                'subscription_status' => Clinic::STATUS_ACTIVE,
                'subscription_expires_at' => now()->addYear(),
            ]);

        $ownerEmail = config('platform.owner_email');
        if (! is_string($ownerEmail) || $ownerEmail === '') {
            if (isset($this->command)) {
                $this->command->warn('PLATFORM_OWNER_EMAIL is not set; skipping platform super-admin user seed.');
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $ownerPassword = SecureSeeder::password(
            is_string(config('platform.owner_password')) ? config('platform.owner_password') : null,
            'platform owner',
        );
        $ownerName = config('platform.owner_name', 'Platform Owner');

        $platformSuper = User::query()->firstOrCreate(
            ['email' => $ownerEmail],
            [
                'name' => $ownerName,
                'password' => $ownerPassword,
                'clinic_id' => null,
                'email_verified_at' => now(),
            ]
        );

        $updates = ['clinic_id' => null, 'name' => $ownerName, 'email_verified_at' => $platformSuper->email_verified_at ?? now()];
        $updates['password'] = $ownerPassword;
        $platformSuper->forceFill($updates)->save();
        $platformSuper->syncRoles(['super_admin']);

        User::query()
            ->where('id', '!=', $platformSuper->id)
            ->role('super_admin')
            ->get()
            ->each(function (User $user): void {
                $user->removeRole('super_admin');
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
