<?php

namespace App\Support;

/**
 * تنسيق حجم الملف بدون الاعتماد على امتداد PHP intl (مطلوب لـ Illuminate\Support\Number::fileSize).
 */
final class FormatBytes
{
    public static function human(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $value = $bytes / (1024 ** $pow);

        return round($value, $precision).' '.$units[$pow];
    }
}
