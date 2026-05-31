<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class PatientLedgerService
{
    /**
     * @param  array<string, mixed>  $filters  من {@see PatientProfileService::filtersFromRequest()}
     * @return Collection<int, array{
     *     date: string,
     *     type: string,
     *     ref: string,
     *     description: string,
     *     debit: string|null,
     *     credit: string|null,
     *     balance: string
     * }>
     */
    public static function buildFromFilters(Patient $patient, array $filters): Collection
    {
        return self::build(
            $patient,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
            $filters['doctor_id'] ?? null,
            $filters['invoice_status'] ?? null,
            $filters['visit_status'] ?? null,
            $filters['appointment_status'] ?? null,
        );
    }

    /**
     * @return Collection<int, array{
     *     date: string,
     *     type: string,
     *     ref: string,
     *     description: string,
     *     debit: string|null,
     *     credit: string|null,
     *     balance: string
     * }>
     */
    public static function build(
        Patient $patient,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        ?int $doctorId = null,
        ?string $invoiceStatus = null,
        ?string $visitStatus = null,
        ?string $appointmentStatus = null,
    ): Collection {
        $invoices = Invoice::query()
            ->where('patient_id', $patient->id)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->when(
                $invoiceStatus && in_array($invoiceStatus, ['unpaid', 'partial', 'paid'], true),
                fn ($q) => $q->where('status', $invoiceStatus)
            )
            ->when($visitStatus, fn ($q, $s) => $q->whereHas('visit', fn ($vq) => $vq->where('status', $s)))
            ->when($appointmentStatus, fn ($q, $s) => $q->whereHas('visit.appointment', fn ($aq) => $aq->where('status', $s)))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $payments = Payment::query()
            ->whereHas('invoice', function ($q) use ($patient, $doctorId, $invoiceStatus, $visitStatus, $appointmentStatus) {
                $q->where('patient_id', $patient->id);
                if ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                }
                if ($invoiceStatus && in_array($invoiceStatus, ['unpaid', 'partial', 'paid'], true)) {
                    $q->where('status', $invoiceStatus);
                }
                if ($visitStatus) {
                    $q->whereHas('visit', fn ($vq) => $vq->where('status', $visitStatus));
                }
                if ($appointmentStatus) {
                    $q->whereHas('visit.appointment', fn ($aq) => $aq->where('status', $appointmentStatus));
                }
            })
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))
            ->with(['invoice:id,invoice_number'])
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($invoices as $inv) {
            $ts = (int) ($inv->created_at?->timestamp ?? 0);
            $rows->push([
                'sort_key' => sprintf('%012d-i-%010d', $ts, $inv->id),
                'date' => $inv->created_at?->format('Y-m-d') ?? '',
                'type' => __('patients.ledger_type_invoice'),
                'ref' => (string) $inv->invoice_number,
                'description' => __('patients.ledger_invoice_description', ['number' => $inv->invoice_number]),
                'debit' => number_format((float) $inv->total, 2, '.', ''),
                'credit' => null,
            ]);
        }

        foreach ($payments as $pay) {
            $invNum = $pay->invoice?->invoice_number ?? '#'.$pay->invoice_id;
            $dayTs = $pay->payment_date
                ? (int) $pay->payment_date->copy()->startOfDay()->timestamp
                : (int) ($pay->created_at?->copy()->startOfDay()->timestamp ?? 0);
            $rows->push([
                'sort_key' => sprintf('%012d-p-%010d', $dayTs, $pay->id),
                'date' => $pay->payment_date?->format('Y-m-d') ?? '',
                'type' => __('patients.ledger_type_payment'),
                'ref' => 'PAY-'.$pay->id,
                'description' => __('patients.ledger_payment_description', ['number' => $invNum]),
                'debit' => null,
                'credit' => number_format((float) $pay->amount, 2, '.', ''),
            ]);
        }

        $sorted = $rows->sortBy('sort_key')->values();

        $balance = 0.0;

        return $sorted->map(function (array $row) use (&$balance): array {
            if ($row['debit'] !== null) {
                $balance += (float) $row['debit'];
            }
            if ($row['credit'] !== null) {
                $balance -= (float) $row['credit'];
            }
            unset($row['sort_key']);
            $row['balance'] = number_format($balance, 2, '.', '');

            return $row;
        })->values();
    }

    /**
     * @param  Collection<int, array{debit: string|null, credit: string|null, balance: string}>  $ledger
     * @return array{total_invoiced: float, total_paid: float, ending_balance: float}
     */
    public static function statementTotals(Collection $ledger): array
    {
        $totalInvoiced = 0.0;
        $totalPaid = 0.0;
        foreach ($ledger as $row) {
            if (($row['debit'] ?? null) !== null) {
                $totalInvoiced += (float) $row['debit'];
            }
            if (($row['credit'] ?? null) !== null) {
                $totalPaid += (float) $row['credit'];
            }
        }
        $last = $ledger->last();
        $ending = $last ? (float) $last['balance'] : 0.0;

        return [
            'total_invoiced' => round($totalInvoiced, 2),
            'total_paid' => round($totalPaid, 2),
            'ending_balance' => round($ending, 2),
        ];
    }
}
