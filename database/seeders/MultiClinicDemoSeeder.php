<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use App\Support\SecureSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Optional: second clinic + reception user for manual isolation checks (Clinic A vs B).
 * Run: php artisan db:seed --class=MultiClinicDemoSeeder
 */
class MultiClinicDemoSeeder extends Seeder
{
    public function run(): void
    {
        SecureSeeder::blockProductionDemoSeed();

        $demoPassword = SecureSeeder::password(
            is_string(config('seeding.admin_password')) ? config('seeding.admin_password') : null,
            'demo reception user',
        );

        $clinic = Clinic::query()->firstOrCreate(
            ['name' => 'عيادة تجريبية B'],
            [
                'owner_id' => null,
                'subscription_plan' => 'demo',
                'subscription_status' => Clinic::STATUS_ACTIVE,
                'subscription_expires_at' => now()->addYear(),
            ]
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'reception@clinic-b.local'],
            [
                'name' => 'استقبال عيادة B',
                'password' => $demoPassword,
                'clinic_id' => $clinic->id,
                'email_verified_at' => now(),
            ]
        );

        if ((int) $user->clinic_id !== (int) $clinic->id) {
            $user->forceFill(['clinic_id' => $clinic->id])->save();
        }

        $role = Role::query()->where('name', 'receptionist')->where('guard_name', 'web')->first();
        if ($role) {
            $user->syncRoles([$role]);
        }
    }
}
