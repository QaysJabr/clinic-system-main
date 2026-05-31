<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->index('phone', 'patients_phone_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('appointment_date', 'appointments_appointment_date_index');
            $table->index('status', 'appointments_status_index');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->index('visit_date', 'visits_visit_date_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'invoices_status_created_at_index');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'app_notifications_user_created_index');
            $table->index(['user_id', 'is_read'], 'app_notifications_user_is_read_index');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->index(['patient_id', 'created_at'], 'attachments_patient_created_index');
            $table->index(['visit_id', 'created_at'], 'attachments_visit_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('patients_phone_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_appointment_date_index');
            $table->dropIndex('appointments_status_index');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_visit_date_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_status_created_at_index');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex('app_notifications_user_created_index');
            $table->dropIndex('app_notifications_user_is_read_index');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('attachments_patient_created_index');
            $table->dropIndex('attachments_visit_created_index');
        });
    }
};
