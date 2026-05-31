<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            try {
                DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check');
            } catch (\Throwable) {
                // قد لا يوجد قيد بهذا الاسم حسب إصدار Laravel/PostgreSQL
            }
            DB::statement('ALTER TABLE payments ALTER COLUMN payment_method TYPE VARCHAR(32) USING (TRIM(BOTH FROM payment_method::text))');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE payments MODIFY payment_method VARCHAR(32) NOT NULL');

            return;
        }

        if ($driver === 'sqlite') {
            // غالباً عمود نصي بالفعل — لا شيء
        }
    }

    public function down(): void
    {
        // لا نُعيد قيد enum القديم — قد تكون بيانات تحتوي bank_transfer وغيرها.
    }
};
