<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_reminder_dispatches')) {
            return;
        }

        Schema::create('subscription_reminder_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('days_before');
            $table->string('channel', 16);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['clinic_id', 'days_before', 'channel'], 'subscription_reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reminder_dispatches');
    }
};
