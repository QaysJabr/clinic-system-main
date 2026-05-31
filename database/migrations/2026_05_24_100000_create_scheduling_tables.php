<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('scheduling_slot_minutes')->default(15)->after('enforce_one_invoice_per_visit');
            $table->time('scheduling_day_start')->default('09:00:00')->after('scheduling_slot_minutes');
            $table->time('scheduling_day_end')->default('17:00:00')->after('scheduling_day_start');
            $table->unsignedSmallInteger('scheduling_buffer_minutes')->default(0)->after('scheduling_day_end');
            $table->boolean('scheduling_allow_overbooking')->default(false)->after('scheduling_buffer_minutes');
            $table->boolean('scheduling_reminders_enabled')->default(true)->after('scheduling_allow_overbooking');
        });

        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'doctor_id', 'day_of_week'], 'doctor_schedules_unique_day');
        });

        Schema::create('doctor_schedule_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_schedule_id')->constrained('doctor_schedules')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_unavailable_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->date('unavailable_date');
            $table->boolean('all_day')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('type', 32)->default('blocked');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'doctor_id', 'unavailable_date'], 'doctor_unavail_lookup');
        });

        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('channel', 32);
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'status']);
            $table->index(['scheduled_for', 'status']);
        });

        Schema::create('appointment_booking_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_booking_tokens');
        Schema::dropIfExists('appointment_reminders');
        Schema::dropIfExists('doctor_unavailable_dates');
        Schema::dropIfExists('doctor_schedule_breaks');
        Schema::dropIfExists('doctor_schedules');

        Schema::table('clinic_settings', function (Blueprint $table) {
            $table->dropColumn([
                'scheduling_slot_minutes',
                'scheduling_day_start',
                'scheduling_day_end',
                'scheduling_buffer_minutes',
                'scheduling_allow_overbooking',
                'scheduling_reminders_enabled',
            ]);
        });
    }
};
