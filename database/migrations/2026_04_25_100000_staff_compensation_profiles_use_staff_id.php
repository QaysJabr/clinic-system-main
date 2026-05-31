<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $table->unique('user_id');
            }
        });

        if (! Schema::hasColumn('staff_compensation_profiles', 'staff_id')) {
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->foreignId('staff_id')->nullable()->after('id')->constrained('staff')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('staff_compensation_profiles', 'user_id')) {
            $profiles = DB::table('staff_compensation_profiles')->whereNotNull('user_id')->get();

            foreach ($profiles as $row) {
                $user = DB::table('users')->where('id', $row->user_id)->first();
                if (! $user || $user->email === null || trim((string) $user->email) === '') {
                    DB::table('staff_compensation_profiles')->where('id', $row->id)->delete();

                    continue;
                }

                $emailNorm = strtolower(trim((string) $user->email));
                $staffId = DB::table('staff')
                    ->whereRaw('LOWER(TRIM(email)) = ?', [$emailNorm])
                    ->orderBy('id')
                    ->value('id');

                if (! $staffId) {
                    DB::table('staff_compensation_profiles')->where('id', $row->id)->delete();

                    continue;
                }

                DB::table('staff_compensation_profiles')->where('id', $row->id)->update(['staff_id' => $staffId]);

                DB::table('staff')
                    ->where('id', $staffId)
                    ->whereNull('user_id')
                    ->update(['user_id' => $row->user_id]);
            }

            DB::table('staff_compensation_profiles')->whereNull('staff_id')->delete();

            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (! $this->hasUniqueIndex('staff_compensation_profiles', 'staff_id')) {
            Schema::table('staff_compensation_profiles', function (Blueprint $table) {
                $table->unique('staff_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('staff_compensation_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('staff_compensation_profiles', 'staff_id')) {
                $this->dropUniqueIfExists('staff_compensation_profiles', 'staff_id');
                $table->dropForeign(['staff_id']);
                $table->dropColumn('staff_id');
            }
        });

        Schema::table('staff_compensation_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('staff_compensation_profiles', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });

        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'user_id')) {
                $this->dropUniqueIfExists('staff', 'user_id');
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }

    private function hasUniqueIndex(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && in_array($column, $index['columns'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    private function dropUniqueIfExists(string $table, string $column): void
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === [$column]) {
                Schema::table($table, function (Blueprint $t) use ($index) {
                    $t->dropUnique($index['name']);
                });
                break;
            }
        }
    }
};
