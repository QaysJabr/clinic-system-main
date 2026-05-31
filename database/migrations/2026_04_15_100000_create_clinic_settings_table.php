<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name')->default('');
            $table->string('clinic_logo')->nullable();
            $table->string('clinic_phone')->nullable();
            $table->string('clinic_email')->nullable();
            $table->text('clinic_address')->nullable();
            $table->string('currency')->nullable();
            $table->text('invoice_notes')->nullable();
            $table->text('report_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
