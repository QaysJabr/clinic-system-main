<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscription_payments', 'external_reference')) {
                $table->string('external_reference', 128)->nullable()->unique()->after('source');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table): void {
            if (Schema::hasColumn('subscription_payments', 'external_reference')) {
                $table->dropUnique(['external_reference']);
                $table->dropColumn('external_reference');
            }
        });
    }
};
