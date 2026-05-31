<?php

use Illuminate\Database\Migrations\Migration;

/**
 * كان محتوى هذه الهجرة يُعدّل جدول plans قبل إنشائه.
 * أُعيد المحتوى إلى 2026_05_10_100001_enhance_plans_and_clinic_subscriptions.
 * تبقى هذه الهجرة فارغة لتوافق سجل migrate على القواعد التي نفّذت الاسم القديم.
 */
return new class extends Migration
{
    public function up(): void {}

    public function down(): void {}
};
