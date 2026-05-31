<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('end_time');
            $table->timestamp('checked_in_at')->nullable()->after('status');
            $table->foreignId('visit_id')->nullable()->after('doctor_id')->constrained('visits')->nullOnDelete();
            $table->string('booking_source', 32)->default('staff')->after('notes');
            $table->string('public_booking_token', 64)->nullable()->unique()->after('booking_source');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE appointments ALTER COLUMN status TYPE VARCHAR(32) USING status::text');
            DB::statement("ALTER TABLE appointments ALTER COLUMN status SET DEFAULT 'scheduled'");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY status VARCHAR(32) NOT NULL DEFAULT 'scheduled'");
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status', 32)->default('scheduled')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_id');
            $table->dropColumn([
                'duration_minutes',
                'checked_in_at',
                'booking_source',
                'public_booking_token',
            ]);
        });
    }
};
