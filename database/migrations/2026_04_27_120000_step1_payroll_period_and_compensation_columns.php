<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createPayrollRunsTable();

        if (Schema::hasTable('staff_compensation_profiles')
            && Schema::hasColumn('staff_compensation_profiles', 'compensation_model')
            && ! Schema::hasColumn('staff_compensation_profiles', 'compensation_type')) {
            $this->migrateStaffCompensationProfilesToColumns();
        }

        if (Schema::hasTable('staff_payments')
            && Schema::hasColumn('staff_payments', 'month')
            && ! Schema::hasColumn('staff_payments', 'period_start')) {
            $this->migrateStaffPaymentsToPeriods();
        }
    }

    public function down(): void
    {
        // Reversing this migration safely across drivers is non-trivial; restore from backup if needed.
    }

    private function createPayrollRunsTable(): void
    {
        if (Schema::hasTable('payroll_runs')) {
            return;
        }

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('period_type', 16);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_type', 'period_start', 'period_end']);
            $table->index('status');
        });
    }

    private function migrateStaffCompensationProfilesToColumns(): void
    {
        Schema::table('staff_compensation_profiles', function (Blueprint $table) {
            $table->string('compensation_type', 32)->nullable()->after('staff_id');
            $table->string('payment_cycle', 16)->nullable()->after('compensation_type');
            $table->decimal('base_salary', 10, 2)->nullable()->after('payment_cycle');
            $table->decimal('percentage_rate', 5, 2)->nullable()->after('base_salary');
            $table->decimal('daily_wage', 10, 2)->nullable()->after('percentage_rate');
            $table->string('calculation_basis', 64)->nullable()->after('daily_wage');
        });

        DB::table('staff_compensation_profiles')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $cfg = [];
                if ($row->compensation_config) {
                    $cfg = json_decode((string) $row->compensation_config, true) ?: [];
                }

                $payPeriod = $cfg['pay_period'] ?? 'monthly';
                $cycle = match ($payPeriod) {
                    'weekly', 'biweekly' => 'weekly',
                    default => 'monthly',
                };

                $type = (string) $row->compensation_model;
                $baseSalary = null;
                $pct = null;
                $daily = null;
                $basis = null;

                if ($type === 'fixed') {
                    $v = $cfg['fixed_amount'] ?? null;
                    $baseSalary = $v !== null && $v !== '' ? (float) $v : null;
                } elseif ($type === 'percentage') {
                    $v = $cfg['percentage_rate'] ?? null;
                    $pct = $v !== null && $v !== '' ? (float) $v : null;
                    $basis = $cfg['percentage_basis'] ?? 'invoice_paid_total';
                } elseif ($type === 'daily') {
                    $v = $cfg['daily_rate'] ?? null;
                    $daily = $v !== null && $v !== '' ? (float) $v : null;
                }

                DB::table('staff_compensation_profiles')->where('id', $row->id)->update([
                    'compensation_type' => $type,
                    'payment_cycle' => $cycle,
                    'base_salary' => $baseSalary,
                    'percentage_rate' => $pct,
                    'daily_wage' => $daily,
                    'calculation_basis' => $basis,
                ]);
            }
        });

        Schema::table('staff_compensation_profiles', function (Blueprint $table) {
            $table->dropColumn(['compensation_model', 'compensation_config']);
        });

        Schema::table('staff_compensation_profiles', function (Blueprint $table) {
            $table->index(['status', 'compensation_type']);
        });
    }

    private function migrateStaffPaymentsToPeriods(): void
    {
        Schema::table('staff_payments', function (Blueprint $table) {
            $table->foreignId('payroll_run_id')->nullable()->after('id')->constrained('payroll_runs')->nullOnDelete();
            $table->foreignId('compensation_profile_id')->nullable()->after('staff_id')->constrained('staff_compensation_profiles')->nullOnDelete();
            $table->string('period_type', 16)->nullable()->after('compensation_profile_id');
            $table->date('period_start')->nullable()->after('period_type');
            $table->date('period_end')->nullable()->after('period_start');
            $table->unsignedSmallInteger('days_worked')->nullable()->after('remaining_amount');
            $table->string('source_type', 32)->nullable()->after('payment_method');
        });

        DB::table('staff_payments')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                $month = (int) $row->month;
                $year = (int) $row->year;
                $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $end = $start->copy()->endOfMonth();

                DB::table('staff_payments')->where('id', $row->id)->update([
                    'period_type' => 'monthly',
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                ]);
            }
        });

        $this->dropUniqueIfExists('staff_payments', ['staff_id', 'year', 'month']);

        Schema::table('staff_payments', function (Blueprint $table) {
            $table->dropColumn(['month', 'year']);
        });

        Schema::table('staff_payments', function (Blueprint $table) {
            $table->unique(['staff_id', 'period_start', 'period_end'], 'staff_payments_staff_period_unique');
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
};
