<?php

namespace App\Support;

use ArPHP\I18N\Arabic;

/**
 * DomPDF fallback only: reshape Arabic letter runs; leave Latin/digits in logical order.
 *
 * Do not use with Browsershot — the browser handles RTL/LTR natively.
 */
class PdfArabic
{
    private static ?Arabic $arabic = null;

    /**
     * @param  int  $maxCharsPerLine  Line-wrap hint for ar-php (wide value for PDF columns).
     */
    public static function glyphs(?string $text, int $maxCharsPerLine = 800): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (! self::shouldShape()) {
            return $text;
        }

        if (preg_match('/\p{Arabic}/u', $text) !== 1) {
            return $text;
        }

        if (! preg_match('/[A-Za-z0-9]/', $text)) {
            return self::shapeRun($text, $maxCharsPerLine);
        }

        return (string) preg_replace_callback(
            '/[\p{Arabic}\p{M}]+(?:\s+[\p{Arabic}\p{M}]+)*/u',
            fn (array $m): string => self::shapeRun($m[0], $maxCharsPerLine),
            $text
        );
    }

    private static function shapeRun(string $run, int $maxCharsPerLine): string
    {
        self::$arabic ??= new Arabic;

        $shaped = self::$arabic->utf8Glyphs($run, $maxCharsPerLine, false, false);

        $origQm = substr_count($run, '?');
        $shapedQm = substr_count($shaped, '?');
        if ($shapedQm > $origQm) {
            return $run;
        }

        return $shaped;
    }

    private static function shouldShape(): bool
    {
        if (is_file(public_path('fonts/NotoSansArabic-Regular.ttf'))) {
            return false;
        }

        return in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    }
}
