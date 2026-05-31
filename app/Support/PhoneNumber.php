<?php

namespace App\Support;

final class PhoneNumber
{
    /**
     * Normalize a local phone string to E.164 digits (no leading +) for SMS gateways.
     */
    public static function normalizeForSms(?string $raw, ?string $defaultCountryCode = null): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === null || $digits === '') {
            return null;
        }

        $country = preg_replace('/\D+/', '', (string) ($defaultCountryCode ?? config('reminders.sms.default_country_code', '972')));
        if ($country === '') {
            $country = '972';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, $country)) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return $country.substr($digits, 1);
        }

        if (strlen($digits) <= 10) {
            return $country.$digits;
        }

        return $digits;
    }

    public static function toE164(?string $normalizedDigits): ?string
    {
        if ($normalizedDigits === null || $normalizedDigits === '') {
            return null;
        }

        return '+'.$normalizedDigits;
    }
}
