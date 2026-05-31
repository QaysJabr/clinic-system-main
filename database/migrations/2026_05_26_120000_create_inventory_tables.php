<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'name']);
            $table->index(['clinic_id', 'is_active']);
        });

        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'code']);
        });

        Schema::create('inventory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 64)->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('inventory_unit_id')->nullable()->constrained('inventory_units')->nullOnDelete();
            $table->foreignId('inventory_supplier_id')->nullable()->constrained('inventory_suppliers')->nullOnDelete();
            $table->string('name');
            $table->string('sku', 64)->nullable();
            $table->decimal('quantity_on_hand', 14, 3)->default(0);
            $table->decimal('minimum_quantity', 14, 3)->default(0);
            $table->string('status', 24)->default('active');
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 64)->nullable();
            $table->decimal('unit_cost', 14, 4)->nullable();
            $table->string('barcode', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'sku']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'expiry_date']);
            $table->index(['clinic_id', 'quantity_on_hand']);
        });

        Schema::create('inventory_procedure_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_normalized');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_consume')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'name_normalized']);
        });

        Schema::create('inventory_procedure_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('inventory_procedure_template_id')->constrained('inventory_procedure_templates')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->timestamps();

            $table->unique(['inventory_procedure_template_id', 'inventory_item_id'], 'inv_proc_tpl_item_unique');
        });

        Schema::create('inventory_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('inventory_supplier_id')->nullable()->constrained('inventory_suppliers')->nullOnDelete();
            $table->string('reference_number', 64)->nullable();
            $table->date('purchase_date');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status', 24)->default('received');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'purchase_date']);
        });

        Schema::create('inventory_purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('inventory_purchase_id')->constrained('inventory_purchases')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 4)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('type', 32);
            $table->decimal('quantity', 14, 3);
            $table->decimal('quantity_before', 14, 3);
            $table->decimal('quantity_after', 14, 3);
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->foreignId('inventory_purchase_id')->nullable()->constrained('inventory_purchases')->nullOnDelete();
            $table->string('reference_type', 64)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('movement_at');
            $table->timestamps();

            $table->index(['clinic_id', 'movement_at']);
            $table->index(['inventory_item_id', 'movement_at']);
            $table->index(['visit_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_movements');
        Schema::dropIfExists('inventory_purchase_lines');
        Schema::dropIfExists('inventory_purchases');
        Schema::dropIfExists('inventory_procedure_template_items');
        Schema::dropIfExists('inventory_procedure_templates');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_suppliers');
        Schema::dropIfExists('inventory_units');
        Schema::dropIfExists('inventory_categories');
    }
};
