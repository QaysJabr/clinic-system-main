<?php

namespace App\Support\Excel;

use App\Support\ClinicSettings;
use Illuminate\Support\Carbon;

final readonly class ClinicExportMeta
{
    /**
     * @param  list<string>  $filterLines
     */
    public function __construct(
        public string $reportTitle,
        public string $clinicName,
        public ?string $currency,
        public Carbon $generatedAt,
        public array $filterLines = [],
    ) {}

    /**
     * @param  list<string>  $filterLines
     */
    public static function make(string $reportTitle, array $filterLines = []): self
    {
        $clinic = ClinicSettings::current();

        return new self(
            reportTitle: $reportTitle,
            clinicName: (string) ($clinic->clinic_name ?: config('app.name')),
            currency: $clinic->currency ?: null,
            generatedAt: now(),
            filterLines: array_values(array_filter($filterLines, static fn ($line) => filled($line))),
        );
    }

    public function generatedAtLabel(): string
    {
        return __('excel.generated_at', [
            'datetime' => $this->generatedAt->timezone(config('app.timezone'))->format('d/m/Y H:i'),
        ]);
    }

    public function subtitle(): string
    {
        return $this->generatedAtLabel().' — '.$this->clinicName;
    }
}
