<?php

namespace App\Support;

use App\Models\ClinicSetting;

/**
 * نقطة دخول موحّدة لإعدادات العيادة (للاستخدام في الفواتير، PDF، الطباعة، التقارير).
 */
final class ClinicSettings
{
    public static function current(): ClinicSetting
    {
        return ClinicSetting::current();
    }
}
