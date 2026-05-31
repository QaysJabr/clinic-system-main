<?php

namespace App\Support;

/**
 * طرق الدفع الموحّدة في النظام (فواتير، مصروفات، تقارير).
 */
final class PaymentMethods
{
    public const CASH = 'cash';

    public const CARD = 'card';

    public const BANK_TRANSFER = 'bank_transfer';

    public const OTHER = 'other';

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return [self::CASH, self::CARD, self::BANK_TRANSFER, self::OTHER];
    }

    /**
     * @return array<string, string> code → localized label for current locale
     *
     * @deprecated Use {@see self::options()}
     */
    public static function labelsAr(): array
    {
        return self::options();
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::codes() as $c) {
            $out[$c] = self::label($c);
        }

        return $out;
    }

    public static function label(string $code): string
    {
        $code = trim($code);
        $key = 'payments.methods.'.$code;
        $trans = __($key);

        return $trans !== $key ? $trans : ($code !== '' ? $code : __('common.em_dash'));
    }

    /**
     * قاعدة تحقق Laravel: in:cash,card,...
     */
    public static function validationInRule(): string
    {
        return 'in:'.implode(',', self::codes());
    }

    /**
     * دمج مبالغ موجودة مع كل الرموز المعتمدة (قيم 0 للمفقود) مع الحفاظ على ترتيب ثابت ثم أي مفاتيح غير معروفة.
     *
     * @param  array<string, float|int|string>  $totals
     * @return array<string, float>
     */
    public static function orderedTotalsWithZeros(array $totals): array
    {
        $out = [];
        foreach (self::codes() as $c) {
            $out[$c] = round((float) ($totals[$c] ?? 0), 2);
        }
        foreach ($totals as $k => $v) {
            if (! in_array((string) $k, self::codes(), true)) {
                $out[(string) $k] = round((float) $v, 2);
            }
        }

        return $out;
    }
}
