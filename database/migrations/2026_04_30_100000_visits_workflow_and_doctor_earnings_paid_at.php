<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Visits: replace legacy enum with string workflow (waiting / in_progress / completed / cancelled).
 * Maps pending → waiting. Doctor earnings: record when clinic marks settlement (paid_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->migrateVisitStatuses();

        if (Schema::hasTable('doctor_earnings')) {
            Schema::table('doctor_earnings', function (Blueprint $table) {
                if (! Schema::hasColumn('doctor_earnings', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('status');
                }
            });

            DB::table('doctor_earnings')
                ->where('status', 'paid')
                ->whereNull('paid_at')
                ->update(['paid_at' => DB::raw('COALESCE(updated_at, created_at)')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('doctor_earnings') && Schema::hasColumn('doctor_earnings', 'paid_at')) {
            Schema::table('doctor_earnings', function (Blueprint $table) {
                $table->dropColumn('paid_at');
            });
        }

        if (! Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'workflow_status')) {
                $table->string('workflow_status', 32)->nullable();
            }
        });

        DB::table('visits')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                $current = $row->status ?? 'completed';
                $mapped = match ($current) {
                    'waiting', 'in_progress' => 'pending',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                };
                DB::table('visits')->where('id', $row->id)->update(['workflow_status' => $mapped]);
            }
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
        });

        foreach (DB::table('visits')->select(['id', 'workflow_status'])->cursor() as $row) {
            DB::table('visits')->where('id', $row->id)->update(['status' => $row->workflow_status]);
        }

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('workflow_status');
        });
    }

    private function migrateVisitStatuses(): void
    {
        if (! Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'workflow_status')) {
                $table->string('workflow_status', 32)->nullable();
            }
        });

        DB::table('visits')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                $current = $row->status ?? 'completed';
                $mapped = match ($current) {
                    'pending' => 'waiting',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    default => 'waiting',
                };
                DB::table('visits')->where('id', $row->id)->update(['workflow_status' => $mapped]);
            }
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->string('status', 32)->default('waiting');
        });

        foreach (DB::table('visits')->select(['id', 'workflow_status'])->cursor() as $row) {
            DB::table('visits')->where('id', $row->id)->update(['status' => $row->workflow_status]);
        }

        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('workflow_status');
        });
    }
};
