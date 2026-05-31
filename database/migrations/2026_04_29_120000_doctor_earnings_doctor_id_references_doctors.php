<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * doctor_earnings.doctor_id يشير إلى doctors.id (وليس staff.id).
 * ترحيل البيانات: doctors.staff_id = القيمة القديمة → doctor_id الجديد = doctors.id
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_earnings')) {
            return;
        }

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        DB::table('doctor_earnings')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $legacyStaffId = (int) $row->doctor_id;
                $doctorPk = DB::table('doctors')->where('staff_id', $legacyStaffId)->value('id');

                if ($doctorPk) {
                    DB::table('doctor_earnings')->where('id', $row->id)->update(['doctor_id' => $doctorPk]);
                } else {
                    DB::table('doctor_earnings')->where('id', $row->id)->delete();
                }
            }
        });

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_earnings')) {
            return;
        }

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        DB::table('doctor_earnings')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $doctorPk = (int) $row->doctor_id;
                $staffId = DB::table('doctors')->where('id', $doctorPk)->value('staff_id');

                if ($staffId) {
                    DB::table('doctor_earnings')->where('id', $row->id)->update(['doctor_id' => $staffId]);
                }
            }
        });

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('staff')->restrictOnDelete();
        });
    }
};
