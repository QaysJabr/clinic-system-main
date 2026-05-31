<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس إضافية للاستعلامات والتقارير (additive فقط).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_earnings')) {
            return;
        }

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('visit_id');
            $table->index('created_at');
            $table->index(['status', 'paid_at']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctor_earnings')) {
            return;
        }

        Schema::table('doctor_earnings', function (Blueprint $table) {
            $table->dropIndex(['status', 'paid_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['visit_id']);
            $table->dropIndex(['invoice_id']);
        });
    }
};
