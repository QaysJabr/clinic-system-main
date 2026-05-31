<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production') && config('seeding.allow_insecure_defaults')) {
            throw new \RuntimeException('SEED_ALLOW_INSECURE must be false in production.');
        }

        $this->call(RolePermissionSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(ExpenseCategorySeeder::class);

        if (config('seeding.demo_enabled')) {
            $this->call(MultiClinicDemoSeeder::class);
        }
    }
}
