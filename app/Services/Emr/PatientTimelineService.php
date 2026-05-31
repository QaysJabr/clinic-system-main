<?php

namespace App\Services\Emr;

use App\Enums\AttachmentCategory;
use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Unified patient medical timeline from existing records.
 */
final class PatientTimelineService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function build(Patient $patient, ?User $user = null, int $limit = 50): Collection
    {
        $items = collect();

        $doctorId = null;
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doctorId = optional($user->linkedDoctor())->id;
        }

        $visitQuery = Visit::query()
            ->where('patient_id', $patient->id)
            ->with(['doctor:id,full_name', 'soapNote'])
            ->withCount(['structuredPrescriptions', 'structuredProcedures'])
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));

        foreach ($visitQuery->orderByDesc('visit_date')->orderByDesc('id')->limit($limit)->get() as $visit) {
            $items->push($this->visitItem($visit));
        }

        if (! $doctorId) {
            foreach (
                Appointment::query()
                    ->where('patient_id', $patient->id)
                    ->with('doctor:id,full_name')
                    ->orderByDesc('appointment_date')
                    ->limit(30)
                    ->get() as $appointment
            ) {
                $items->push($this->appointmentItem($appointment));
            }

            foreach (
                Invoice::query()
                    ->where('patient_id', $patient->id)
                    ->orderByDesc('created_at')
                    ->limit(30)
                    ->get() as $invoice
            ) {
                $items->push($this->invoiceItem($invoice));
            }

            foreach (
                Payment::query()
                    ->whereIn(
                        'invoice_id',
                        Invoice::query()->where('patient_id', $patient->id)->select('id')
                    )
                    ->with('invoice:id,invoice_number')
                    ->orderByDesc('payment_date')
                    ->limit(30)
                    ->get() as $payment
            ) {
                $items->push($this->paymentItem($payment));
            }
        }

        foreach (
            PatientDiagnosis::query()
                ->where('patient_id', $patient->id)
                ->with('doctor:id,full_name')
                ->orderByDesc('diagnosed_at')
                ->limit(40)
                ->get() as $diagnosis
        ) {
            $items->push($this->diagnosisItem($diagnosis));
        }

        foreach (
            Attachment::query()
                ->where('patient_id', $patient->id)
                ->orderByDesc('created_at')
                ->limit(40)
                ->get() as $attachment
        ) {
            $items->push($this->attachmentItem($attachment));
        }

        return $items
            ->sortByDesc(fn (array $item) => $item['occurred_at'])
            ->take($limit)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function visitItem(Visit $visit): array
    {
        $at = Carbon::parse($visit->visit_date->format('Y-m-d').' 12:00:00');
        $summary = collect([
            $visit->chief_complaint,
            $visit->diagnosis,
        ])->filter()->implode(' · ');

        $rxCount = (int) ($visit->structured_prescriptions_count ?? 0);

        return [
            'type' => 'visit',
            'occurred_at' => $at,
            'title' => __('emr.timeline_visit', ['doctor' => optional($visit->doctor)->full_name ?? '—']),
            'summary' => $summary ?: __('emr.timeline_visit_no_summary'),
            'url' => route('visits.show', $visit),
            'badge' => $visit->statusLabel(),
            'badge_class' => $visit->statusBadgeClasses(),
            'meta' => [
                'status' => $visit->status,
                'prescriptions' => $rxCount,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appointmentItem(Appointment $appointment): array
    {
        $at = Carbon::parse($appointment->appointment_date->format('Y-m-d').' '.($appointment->start_time ?? '09:00'));

        return [
            'type' => 'appointment',
            'occurred_at' => $at,
            'title' => __('emr.timeline_appointment'),
            'summary' => optional($appointment->doctor)->full_name.' · '.$appointment->start_time,
            'url' => route('appointments.edit', $appointment),
            'badge' => $appointment->status,
            'badge_class' => 'bg-blue-100 text-blue-800',
            'meta' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceItem(Invoice $invoice): array
    {
        return [
            'type' => 'invoice',
            'occurred_at' => $invoice->created_at,
            'title' => __('emr.timeline_invoice', ['number' => $invoice->invoice_number]),
            'summary' => number_format((float) $invoice->total, 2).' · '.$invoice->status,
            'url' => route('invoices.show', $invoice),
            'badge' => $invoice->status,
            'badge_class' => 'bg-slate-100 text-slate-800',
            'meta' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentItem(Payment $payment): array
    {
        return [
            'type' => 'payment',
            'occurred_at' => Carbon::parse($payment->payment_date->format('Y-m-d').' 12:00:00'),
            'title' => __('emr.timeline_payment'),
            'summary' => number_format((float) $payment->amount, 2),
            'url' => route('invoices.show', $payment->invoice_id),
            'badge' => null,
            'badge_class' => null,
            'meta' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function diagnosisItem(PatientDiagnosis $diagnosis): array
    {
        $label = $diagnosis->description;
        if ($diagnosis->icd_code) {
            $label .= ' ('.$diagnosis->icd_code.')';
        }

        return [
            'type' => 'diagnosis',
            'occurred_at' => Carbon::parse($diagnosis->diagnosed_at->format('Y-m-d').' 12:00:00'),
            'title' => __('emr.timeline_diagnosis'),
            'summary' => $label,
            'url' => $diagnosis->visit_id ? route('visits.show', $diagnosis->visit_id) : null,
            'badge' => $diagnosis->is_primary ? __('emr.primary_diagnosis') : null,
            'badge_class' => 'bg-violet-100 text-violet-800',
            'meta' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentItem(Attachment $attachment): array
    {
        $category = AttachmentCategory::tryFrom($attachment->category ?? 'document') ?? AttachmentCategory::Document;
        $type = match ($category) {
            AttachmentCategory::Lab => 'lab',
            AttachmentCategory::Radiology => 'radiology',
            AttachmentCategory::Prescription => 'prescription',
            default => 'attachment',
        };

        return [
            'type' => $type,
            'occurred_at' => $attachment->created_at,
            'title' => __($category->labelKey()),
            'summary' => $attachment->file_name,
            'url' => route('attachments.download', $attachment),
            'preview_url' => $category->isPreviewable() ? route('attachments.preview', $attachment) : null,
            'badge' => null,
            'badge_class' => null,
            'meta' => ['category' => $category->value],
        ];
    }
}
