<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $clinicId = (int) config('tenancy.default_clinic_id');

        $defaults = [
            ['name' => 'رواتب', 'description' => 'رواتب الموظفين والأطباء', 'status' => 'active'],
            ['name' => 'إيجار', 'description' => 'إيجار العيادة أو المخزن', 'status' => 'active'],
            ['name' => 'مرافق (كهرباء، ماء، إنترنت)', 'description' => 'فواتير الخدمات', 'status' => 'active'],
            ['name' => 'مستلزمات طبية', 'description' => 'معدات ومستهلكات', 'status' => 'active'],
            ['name' => 'صيانة', 'description' => 'صيانة الأجهزة والمرافق', 'status' => 'active'],
            ['name' => 'تسويق', 'description' => 'إعلانات وحملات', 'status' => 'active'],
            ['name' => 'نقل', 'description' => 'مواصلات وشحن', 'status' => 'active'],
            ['name' => 'مصروفات أخرى', 'description' => null, 'status' => 'active'],
        ];

        $scopeWasEnabled = TenantScope::$enabled;
        TenantScope::$enabled = false;

        try {
            foreach ($defaults as $row) {
                ExpenseCategory::withoutGlobalScopes()->updateOrCreate(
                    [
                        'name' => $row['name'],
                        'clinic_id' => $clinicId,
                    ],
                    [
                        'description' => $row['description'],
                        'status' => $row['status'],
                    ]
                );
            }
        } finally {
            TenantScope::$enabled = $scopeWasEnabled;
        }
    }
}
