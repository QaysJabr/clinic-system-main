<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
final class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total = round((float) $this->total, 2);
        $paid = round((float) $this->paid, 2);

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'patient_id' => $this->patient_id,
            'visit_id' => $this->visit_id,
            'doctor_id' => $this->doctor_id,
            'total' => (string) $this->total,
            'paid' => (string) $this->paid,
            'remaining' => number_format(max(0, $total - $paid), 2, '.', ''),
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
            'notes' => $this->notes,
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient?->id,
                'full_name' => $this->patient?->full_name,
            ]),
            'doctor' => $this->whenLoaded('treatingDoctor', fn () => [
                'id' => $this->treatingDoctor?->id,
                'full_name' => $this->treatingDoctor?->full_name,
            ]),
        ];
    }
}
