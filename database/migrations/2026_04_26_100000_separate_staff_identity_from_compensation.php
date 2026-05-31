<?php

use App\Enums\StaffCompensationModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('staff_compensation_profiles', 'effective_from')
            && ! Schema::hasColumn('staff_compensation_profiles', 'start_date')) {
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('compensation_config');
            });
            DB::statement('UPDATE staff_compensation_profiles SET start_date = effective_from');
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->dropColumn('effective_from');
            });
        }

        if (Schema::hasColumn('staff', 'compensation_type')) {
            $this->migrateStaffFinancialsIntoProfiles();

            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn([
                    'compensation_type',
                    'base_salary',
                    'percentage_rate',
                    'daily_rate',
                ]);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('staff', 'compensation_type')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->string('compensation_type', 16)->default('fixed')->after('email');
                $table->decimal('base_salary', 10, 2)->nullable()->after('compensation_type');
                $table->decimal('percentage_rate', 5, 2)->nullable()->after('base_salary');
                $table->decimal('daily_rate', 10, 2)->nullable()->after('percentage_rate');
            });
        }

        if (Schema::hasColumn('staff_compensation_profiles', 'start_date')
            && ! Schema::hasColumn('staff_compensation_profiles', 'effective_from')) {
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->date('effective_from')->nullable()->after('compensation_config');
            });
            DB::statement('UPDATE staff_compensation_profiles SET effective_from = start_date');
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->dropColumn('start_date');
            });
        }
    }

    private function migrateStaffFinancialsIntoProfiles(): void
    {
        $staffRows = DB::table('staff')->get();

        foreach ($staffRows as $row) {
            $exists = DB::table('staff_compensation_profiles')
                ->where('staff_id', $row->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $type = (string) ($row->compensation_type ?? 'fixed');
            if (! in_array($type, StaffCompensationModel::values(), true)) {
                $type = StaffCompensationModel::Fixed->value;
            }

            $model = StaffCompensationModel::from($type);
            $config = match ($model) {
                StaffCompensationModel::Fixed => [
                    'fixed_amount' => $row->base_salary !== null ? (string) $row->base_salary : '0',
                    'pay_period' => 'monthly',
                ],
                StaffCompensationModel::Percentage => [
                    'percentage_rate' => $row->percentage_rate !== null ? (string) $row->percentage_rate : '0',
                    'percentage_basis' => 'invoice_paid_total',
                ],
                StaffCompensationModel::Daily => [
                    'daily_rate' => $row->daily_rate !== null ? (string) $row->daily_rate : '0',
                ],
            };

            DB::table('staff_compensation_profiles')->insert([
                'staff_id' => $row->id,
                'compensation_model' => $model->value,
                'compensation_config' => json_encode($config),
                'start_date' => null,
                'status' => ($row->status ?? 'active') === 'active' ? 'active' : 'inactive',
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
