<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'doctor_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('doctor_id')->nullable()->after('visit_id')->constrained('doctors')->nullOnDelete();
                $table->index('doctor_id');
            });

            if (Schema::hasTable('visits')) {
                DB::table('invoices')->whereNotNull('visit_id')->orderBy('id')->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $doctorId = DB::table('visits')->where('id', $row->visit_id)->value('doctor_id');
                        if ($doctorId !== null) {
                            DB::table('invoices')->where('id', $row->id)->update(['doctor_id' => $doctorId]);
                        }
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'doctor_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            });
        }
    }
};
