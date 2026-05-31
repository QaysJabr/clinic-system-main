<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('role_type', 32);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('compensation_type', 16);
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->decimal('percentage_rate', 5, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->string('status', 16)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'role_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
