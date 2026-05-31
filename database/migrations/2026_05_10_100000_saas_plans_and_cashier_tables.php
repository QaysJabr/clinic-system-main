<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('billing_cycle', 16);
            $table->string('stripe_price_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('clinics', 'pm_type')) {
                $table->string('pm_type')->nullable()->after('stripe_id');
            }
            if (! Schema::hasColumn('clinics', 'pm_last_four')) {
                $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            }
            if (! Schema::hasColumn('clinics', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('pm_last_four');
            }
            if (! Schema::hasColumn('clinics', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('owner_id')->constrained('plans')->nullOnDelete();
            }
            if (! Schema::hasColumn('clinics', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('subscription_expires_at');
            }
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->string('meter_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('meter_event_name')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'plan_id')) {
                $table->dropConstrainedForeignId('plan_id');
            }
        });

        Schema::table('clinics', function (Blueprint $table) {
            $cols = ['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at', 'is_active'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('clinics', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('plans');
    }
};
