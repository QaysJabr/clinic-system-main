<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\VisitPrescription;
use App\Models\VisitProcedure;
use Illuminate\Http\Request;

/**
 * EMR منظّم بالإضافة إلى الحقول النصية procedures / prescriptions.
 */
final class VisitStructuredEmrService
{
    private const MARK_PROC = '[جدول الإجراءات]';

    private const MARK_RX = '[جدول الوصفات]';

    public function syncFromRequest(Visit $visit, Request $request): void
    {
        $visit->structuredProcedures()->delete();
        $visit->structuredPrescriptions()->delete();

        foreach ($request->input('procedure_rows', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            VisitProcedure::query()->create([
                'visit_id' => $visit->id,
                'name' => $name,
                'notes' => isset($row['notes']) ? trim((string) $row['notes']) : null,
            ]);
        }

        foreach ($request->input('rx_rows', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $med = trim((string) ($row['medication_name'] ?? ''));
            if ($med === '') {
                continue;
            }
            VisitPrescription::query()->create([
                'visit_id' => $visit->id,
                'medication_name' => $med,
                'dosage' => isset($row['dosage']) ? trim((string) $row['dosage']) : null,
                'frequency' => isset($row['frequency']) ? trim((string) $row['frequency']) : null,
                'duration' => isset($row['duration']) ? trim((string) $row['duration']) : null,
                'notes' => isset($row['notes']) ? trim((string) $row['notes']) : null,
            ]);
        }

        $visit->refresh();
        $this->mergeStructuredIntoLegacyText($visit);
    }

    private function mergeStructuredIntoLegacyText(Visit $visit): void
    {
        $visit->load(['structuredProcedures', 'structuredPrescriptions']);

        $procLines = $visit->structuredProcedures->map(function (VisitProcedure $p): string {
            $line = '• '.$p->name;
            if (filled($p->notes)) {
                $line .= ' — '.$p->notes;
            }

            return $line;
        })->filter()->implode("\n");

        $rxLines = $visit->structuredPrescriptions->map(function (VisitPrescription $r): string {
            $parts = ['• '.$r->medication_name];
            $parts[] = 'جرعة: '.($r->dosage ?: '—');
            $parts[] = 'تكرار: '.($r->frequency ?: '—');
            $parts[] = 'مدة: '.($r->duration ?: '—');
            $line = implode(' | ', $parts);
            if (filled($r->notes)) {
                $line .= "\n  ".$r->notes;
            }

            return $line;
        })->filter()->implode("\n");

        $procBase = $this->stripMarkedSection((string) ($visit->procedures ?? ''), self::MARK_PROC);
        $rxBase = $this->stripMarkedSection((string) ($visit->prescriptions ?? ''), self::MARK_RX);

        $newProc = $procBase;
        if ($procLines !== '') {
            $suffix = "\n\n".self::MARK_PROC."\n".$procLines;
            $newProc = trim($procBase.$suffix);
        }

        $newRx = $rxBase;
        if ($rxLines !== '') {
            $suffix = "\n\n".self::MARK_RX."\n".$rxLines;
            $newRx = trim($rxBase.$suffix);
        }

        $visit->forceFill([
            'procedures' => $newProc !== '' ? $newProc : null,
            'prescriptions' => $newRx !== '' ? $newRx : null,
        ])->saveQuietly();
    }

    private function stripMarkedSection(string $text, string $marker): string
    {
        $text = trim($text);
        $pos = mb_strpos($text, $marker);
        if ($pos === false) {
            return $text;
        }

        return trim(mb_substr($text, 0, $pos));
    }
}
