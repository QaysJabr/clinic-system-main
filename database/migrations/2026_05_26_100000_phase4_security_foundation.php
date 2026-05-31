<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });

        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('device_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'successful']);
        });

        if (Schema::hasTable('clinic_settings') && ! Schema::hasColumn('clinic_settings', 'require_two_factor')) {
            Schema::table('clinic_settings', function (Blueprint $table) {
                $table->boolean('require_two_factor')->default(false);
            });
        }

        if (Schema::hasTable('attachments') && ! Schema::hasColumn('attachments', 'storage_disk')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->string('storage_disk', 32)->default('public');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attachments') && Schema::hasColumn('attachments', 'storage_disk')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropColumn('storage_disk');
            });
        }

        if (Schema::hasTable('clinic_settings') && Schema::hasColumn('clinic_settings', 'require_two_factor')) {
            Schema::table('clinic_settings', function (Blueprint $table) {
                $table->dropColumn('require_two_factor');
            });
        }

        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('trusted_devices');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};
