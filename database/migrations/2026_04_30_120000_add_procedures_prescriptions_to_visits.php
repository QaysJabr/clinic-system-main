<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'procedures')) {
                $table->longText('procedures')->nullable()->after('treatment_plan');
            }
            if (! Schema::hasColumn('visits', 'prescriptions')) {
                $table->longText('prescriptions')->nullable()->after('procedures');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'prescriptions')) {
                $table->dropColumn('prescriptions');
            }
            if (Schema::hasColumn('visits', 'procedures')) {
                $table->dropColumn('procedures');
            }
        });
    }
};
