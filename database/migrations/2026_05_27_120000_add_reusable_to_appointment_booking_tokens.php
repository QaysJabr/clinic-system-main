<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_booking_tokens', function (Blueprint $table) {
            $table->boolean('reusable')->default(false)->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_booking_tokens', function (Blueprint $table) {
            $table->dropColumn('reusable');
        });
    }
};
