<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subscription_plan')->nullable();
            $table->string('subscription_status', 32)->default('active');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamps();
        });

        DB::table('clinics')->insert([
            'id' => 1,
            'name' => 'العيادة الافتراضية',
            'owner_id' => null,
            'subscription_plan' => null,
            'subscription_status' => 'active',
            'subscription_expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('clinics', 'id'), (SELECT MAX(id) FROM clinics))");
        }

        $tenantTables = [
            'users',
            'patients',
            'staff',
            'doctors',
            'appointments',
            'visits',
            'invoices',
            'invoice_items',
            'payments',
            'doctor_earnings',
            'clinic_settings',
            'attachments',
            'expense_categories',
            'expenses',
            'services',
            'visit_procedures',
            'visit_prescriptions',
            'payroll_runs',
            'staff_payments',
            'staff_compensation_profiles',
            'app_notifications',
            'audit_logs',
        ];

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            $this->addClinicForeignId($tableName);
            DB::table($tableName)->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }

        $adminId = DB::table('users')->where('email', 'admin@clinic.local')->value('id');
        if ($adminId) {
            DB::table('clinics')->where('id', 1)->update(['owner_id' => $adminId]);
        }

        $this->dropUniqueIfExists('patients', ['file_number']);
        $this->dropUniqueIfExists('patients', ['national_id']);

        Schema::table('patients', function (Blueprint $table) {
            $table->unique(['clinic_id', 'file_number'], 'patients_clinic_file_number_unique');
            $table->unique(['clinic_id', 'national_id'], 'patients_clinic_national_id_unique');
        });

        $this->dropUniqueIfExists('doctors', ['email']);
        $this->dropUniqueIfExists('doctors', ['license_number']);

        Schema::table('doctors', function (Blueprint $table) {
            $table->unique(['clinic_id', 'email'], 'doctors_clinic_email_unique');
            $table->unique(['clinic_id', 'license_number'], 'doctors_clinic_license_unique');
        });

        $this->dropUniqueIfExists('invoices', ['invoice_number']);

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['clinic_id', 'invoice_number'], 'invoices_clinic_invoice_number_unique');
        });

        $this->dropUniqueIfExists('expense_categories', ['name']);

        Schema::table('expense_categories', function (Blueprint $table) {
            $table->unique(['clinic_id', 'name'], 'expense_categories_clinic_name_unique');
        });

        $this->backfillTenantColumns();

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            DB::table($tableName)->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }
    }

    public function down(): void
    {
        // Intentionally minimal — restore from backup if full rollback is required.
    }

    private function addClinicForeignId(string $tableName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'clinic_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $blueprint) use ($tableName): void {
            if (Schema::hasColumn($tableName, 'id')) {
                $blueprint->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->restrictOnDelete();
            } else {
                $blueprint->foreignId('clinic_id')->nullable()->constrained('clinics')->restrictOnDelete();
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropUniqueIfExists(string $table, array $columns): void
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === $columns) {
                Schema::table($table, function (Blueprint $t) use ($index) {
                    $t->dropUnique($index['name']);
                });
                break;
            }
        }
    }

    private function backfillTenantColumns(): void
    {
        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'clinic_id')) {
            DB::statement('
                UPDATE staff s
                SET clinic_id = u.clinic_id
                FROM users u
                WHERE s.user_id = u.id AND u.clinic_id IS NOT NULL
            ');
        }

        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'clinic_id')) {
            DB::statement('
                UPDATE doctors d
                SET clinic_id = s.clinic_id
                FROM staff s
                WHERE d.staff_id = s.id AND s.clinic_id IS NOT NULL
            ');
        }

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'patient_id')) {
            DB::statement('
                UPDATE appointments a
                SET clinic_id = p.clinic_id
                FROM patients p
                WHERE a.patient_id = p.id
            ');
        }

        if (Schema::hasTable('visits')) {
            DB::statement('
                UPDATE visits v
                SET clinic_id = p.clinic_id
                FROM patients p
                WHERE v.patient_id = p.id
            ');
        }

        if (Schema::hasTable('invoices')) {
            DB::statement('
                UPDATE invoices i
                SET clinic_id = p.clinic_id
                FROM patients p
                WHERE i.patient_id = p.id
            ');
        }

        if (Schema::hasTable('invoice_items')) {
            DB::statement('
                UPDATE invoice_items ii
                SET clinic_id = i.clinic_id
                FROM invoices i
                WHERE ii.invoice_id = i.id
            ');
        }

        if (Schema::hasTable('payments')) {
            DB::statement('
                UPDATE payments p
                SET clinic_id = i.clinic_id
                FROM invoices i
                WHERE p.invoice_id = i.id
            ');
        }

        if (Schema::hasTable('doctor_earnings')) {
            DB::statement('
                UPDATE doctor_earnings de
                SET clinic_id = i.clinic_id
                FROM invoices i
                WHERE de.invoice_id = i.id
            ');
        }

        if (Schema::hasTable('attachments')) {
            DB::statement('
                UPDATE attachments a
                SET clinic_id = COALESCE(
                    (SELECT v.clinic_id FROM visits v WHERE v.id = a.visit_id),
                    (SELECT p.clinic_id FROM patients p WHERE p.id = a.patient_id),
                    1
                )
            ');
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'expense_category_id')) {
            DB::statement('
                UPDATE expenses e
                SET clinic_id = ec.clinic_id
                FROM expense_categories ec
                WHERE e.expense_category_id = ec.id
            ');
        }

        if (Schema::hasTable('services')) {
            DB::table('services')->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }

        if (Schema::hasTable('visit_procedures')) {
            DB::statement('
                UPDATE visit_procedures vp
                SET clinic_id = v.clinic_id
                FROM visits v
                WHERE vp.visit_id = v.id
            ');
        }

        if (Schema::hasTable('visit_prescriptions')) {
            DB::statement('
                UPDATE visit_prescriptions vp
                SET clinic_id = v.clinic_id
                FROM visits v
                WHERE vp.visit_id = v.id
            ');
        }

        if (Schema::hasTable('staff_payments')) {
            DB::statement('
                UPDATE staff_payments sp
                SET clinic_id = s.clinic_id
                FROM staff s
                WHERE sp.staff_id = s.id
            ');
        }

        if (Schema::hasTable('staff_compensation_profiles')) {
            DB::statement('
                UPDATE staff_compensation_profiles scp
                SET clinic_id = s.clinic_id
                FROM staff s
                WHERE scp.staff_id = s.id
            ');
        }

        if (Schema::hasTable('payroll_runs')) {
            DB::table('payroll_runs')->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }

        if (Schema::hasTable('app_notifications')) {
            DB::statement('
                UPDATE app_notifications n
                SET clinic_id = u.clinic_id
                FROM users u
                WHERE n.user_id = u.id AND u.clinic_id IS NOT NULL
            ');
        }

        if (Schema::hasTable('audit_logs')) {
            DB::statement('
                UPDATE audit_logs al
                SET clinic_id = u.clinic_id
                FROM users u
                WHERE al.user_id = u.id AND u.clinic_id IS NOT NULL
            ');
            DB::table('audit_logs')->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }

        if (Schema::hasTable('clinic_settings')) {
            DB::table('clinic_settings')->whereNull('clinic_id')->update(['clinic_id' => 1]);
        }
    }
};
