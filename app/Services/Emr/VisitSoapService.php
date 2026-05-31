<?php

namespace App\Services\Emr;

use App\Models\Visit;
use App\Models\VisitSoapNote;
use Illuminate\Http\Request;

/**
 * Structured SOAP notes with backward compatibility to legacy visit fields.
 */
final class VisitSoapService
{
    /**
     * @return array{subjective: string, objective: string, assessment: string, plan: string, notes: string}
     */
    public function resolveForForm(Visit $visit): array
    {
        $visit->loadMissing('soapNote');

        $soap = $visit->soapNote;

        return [
            'subjective' => (string) ($soap?->subjective ?? $visit->chief_complaint ?? ''),
            'objective' => (string) ($soap?->objective ?? ''),
            'assessment' => (string) ($soap?->assessment ?? $visit->diagnosis ?? ''),
            'plan' => (string) ($soap?->plan ?? $visit->treatment_plan ?? ''),
            'notes' => (string) ($visit->notes ?? ''),
        ];
    }

    public function syncFromRequest(Visit $visit, Request $request): void
    {
        $subjective = $this->text($request, 'soap_subjective', 'chief_complaint');
        $objective = $this->text($request, 'soap_objective');
        $assessment = $this->text($request, 'soap_assessment', 'diagnosis');
        $plan = $this->text($request, 'soap_plan', 'treatment_plan');
        $notes = $this->text($request, 'notes');

        VisitSoapNote::query()->updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'subjective' => $subjective ?: null,
                'objective' => $objective ?: null,
                'assessment' => $assessment ?: null,
                'plan' => $plan ?: null,
            ]
        );

        $visit->forceFill([
            'chief_complaint' => $subjective ?: null,
            'diagnosis' => $assessment ?: null,
            'treatment_plan' => $plan ?: null,
            'notes' => $notes ?: null,
        ])->saveQuietly();
    }

    private function text(Request $request, string $soapKey, ?string $legacyKey = null): string
    {
        $soap = trim((string) $request->input($soapKey, ''));
        if ($soap !== '') {
            return $soap;
        }

        if ($legacyKey !== null) {
            return trim((string) $request->input($legacyKey, ''));
        }

        return '';
    }
}
