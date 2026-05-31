<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'price_monthly')) {
                $table->decimal('price_monthly', 12, 2)->default(0)->after('slug');
            }
            if (! Schema::hasColumn('plans', 'price_yearly')) {
                $table->decimal('price_yearly', 12, 2)->default(0)->after('price_monthly');
            }
            if (! Schema::hasColumn('plans', 'max_patients')) {
                $table->unsignedInteger('max_patients')->nullable()->after('price_yearly');
            }
            if (! Schema::hasColumn('plans', 'max_users')) {
                $table->unsignedInteger('max_users')->nullable()->after('max_patients');
            }
            if (! Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable()->after('max_users');
            }
            if (! Schema::hasColumn('plans', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')->default(0)->after('features');
            }
            if (! Schema::hasColumn('plans', 'stripe_price_yearly_id')) {
                $table->string('stripe_price_yearly_id')->nullable()->after('stripe_price_id');
            }
        });

        if (Schema::hasColumn('plans', 'price_cents')) {
            $plans = DB::table('plans')->select(['id', 'price_cents', 'billing_cycle'])->get();
            foreach ($plans as $row) {
                $amount = round(((int) $row->price_cents) / 100, 2);
                $monthly = $row->billing_cycle === 'yearly' ? 0 : $amount;
                $yearly = $row->billing_cycle === 'yearly' ? $amount : 0;
                DB::table('plans')->where('id', $row->id)->update([
                    'price_monthly' => $monthly,
                    'price_yearly' => $yearly,
                    'features' => json_encode([]),
                ]);
            }
        }

        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'price_cents')) {
                $table->dropColumn('price_cents');
            }
            if (Schema::hasColumn('plans', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
        });

        if (! Schema::hasTable('clinic_subscriptions')) {
            Schema::create('clinic_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
                $table->string('status', 24)->default('active');
                $table->string('billing_cycle', 16);
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->index(['clinic_id', 'status']);
                $table->index(['plan_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_subscriptions');

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'price_cents')) {
                $table->unsignedInteger('price_cents')->default(0)->after('slug');
            }
            if (! Schema::hasColumn('plans', 'billing_cycle')) {
                $table->string('billing_cycle', 16)->default('monthly')->after('price_cents');
            }
        });

        if (Schema::hasColumn('plans', 'price_monthly')) {
            $plans = DB::table('plans')->select(['id', 'price_monthly', 'price_yearly'])->get();
            foreach ($plans as $row) {
                $monthly = (float) $row->price_monthly;
                $yearly = (float) $row->price_yearly;
                if ($yearly > 0 && $monthly <= 0) {
                    $cents = (int) round($yearly * 100);
                    $cycle = 'yearly';
                } else {
                    $cents = (int) round(max($monthly, 0) * 100);
                    $cycle = 'monthly';
                }
                DB::table('plans')->where('id', $row->id)->update([
                    'price_cents' => $cents,
                    'billing_cycle' => $cycle,
                ]);
            }
        }

        Schema::table('plans', function (Blueprint $table) {
            foreach (['stripe_price_yearly_id', 'trial_days', 'features', 'max_users', 'max_patients', 'price_yearly', 'price_monthly'] as $col) {
                if (Schema::hasColumn('plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
