<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('staff')->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('percentage_rate', 5, 2);
            $table->decimal('earning_amount', 10, 2);
            $table->string('status', 16)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'status']);
            $table->unique('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_earnings');
    }
};
