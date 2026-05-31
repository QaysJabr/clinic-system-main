<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_compensation_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('compensation_model', 32);
            $table->json('compensation_config');
            $table->date('effective_from')->nullable();
            $table->string('status', 16)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'compensation_model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_compensation_profiles');
    }
};
