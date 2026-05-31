<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('payment_method', 32);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'paid_at']);
            $table->index(['expense_id', 'paid_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('settlement_type', 16)->default('full');
        });

        foreach (DB::table('expenses')->orderBy('id')->cursor() as $row) {
            $cid = $row->clinic_id ?? config('tenancy.default_clinic_id');
            if ($cid === null) {
                continue;
            }
            DB::table('expense_payments')->insert([
                'clinic_id' => $cid,
                'expense_id' => $row->id,
                'amount' => $row->amount,
                'paid_at' => $row->expense_date,
                'payment_method' => $row->payment_method,
                'notes' => null,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('settlement_type');
        });
    }
};
