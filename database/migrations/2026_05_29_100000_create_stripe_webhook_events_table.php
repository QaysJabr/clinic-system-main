<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stripe_webhook_events')) {
            return;
        }

        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id', 64)->unique();
            $table->string('event_type', 64);
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_customer_id', 64)->nullable();
            $table->string('summary', 255)->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['clinic_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');
    }
};
