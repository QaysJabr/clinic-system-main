<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic_settings')) {
            return;
        }

        Schema::table('clinic_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_settings', 'require_invoice_for_visit')) {
                $table->boolean('require_invoice_for_visit')->default(false)->after('report_footer');
            }
            if (! Schema::hasColumn('clinic_settings', 'enforce_one_invoice_per_visit')) {
                $table->boolean('enforce_one_invoice_per_visit')->default(false)->after('require_invoice_for_visit');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('clinic_settings')) {
            return;
        }

        Schema::table('clinic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_settings', 'enforce_one_invoice_per_visit')) {
                $table->dropColumn('enforce_one_invoice_per_visit');
            }
            if (Schema::hasColumn('clinic_settings', 'require_invoice_for_visit')) {
                $table->dropColumn('require_invoice_for_visit');
            }
        });
    }
};
