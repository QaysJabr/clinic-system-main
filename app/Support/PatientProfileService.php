<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Collection;

final class PatientProfileService
{
    /**
     * @return array{
     *     date_from: \Carbon\CarbonInterface|null,
     *     date_to: \Carbon\CarbonInterface|null,
     *     doctor_id: int|null,
     *     invoice_status: string|null,
     *     visit_status: string|null,
     *     appointment_status: string|null
     * }
     */
    public static function filtersFromRequest(Request $request): array
    {
        $inv = $request->input('invoice_status');
        $invOk = is_string($inv) && in_array($inv, ['unpaid', 'partial', 'paid'], true) ? $inv : null;

        $vs = $request->input('visit_status');
        $vsOk = is_string($vs) && in_array($vs, [
            Visit::STATUS_WAITING,
            Visit::STATUS_IN_PROGRESS,
            Visit::STATUS_COMPLETED,
            Visit::STATUS_CANCELLED,
        ], true) ? $vs : null;

        $as = $request->input('appointment_status');
        $asOk = is_string($as) && in_array($as, ['scheduled', 'completed', 'cancelled'], true) ? $as : null;

        return [
            'date_from' => $request->date('date_from'),
            'date_to' => $request->date('date_to'),
            'doctor_id' => $request->filled('doctor_id') ? $request->integer('doctor_id') : null,
            'invoice_status' => $invOk,
            'visit_status' => $vsOk,
            'appointment_status' => $asOk,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     invoice_count: int,
     *     invoice_total: float,
     *     paid_total: float,
     *     balance: float,
     *     visit_count: int,
     *     last_visit_date: \Carbon\CarbonInterface|string|null
     * }
     */
    public static function financialSummary(Patient $patient, array $filters): array
    {
        $invQ = self::invoiceBaseQuery($patient, $filters);

        $invoiceTotal = (float) (clone $invQ)->sum('total');
        $paidTotal = (float) (clone $invQ)->sum('paid');
        $balance = round($invoiceTotal - $paidTotal, 2);

        $visitQ = self::visitBaseQuery($patient, $filters);
        $visitCount = (int) (clone $visitQ)->count();
        $lastVisit = (clone $visitQ)->max('visit_date');

        return [
            'invoice_count' => (int) (clone $invQ)->count(),
            'invoice_total' => $invoiceTotal,
            'paid_total' => $paidTotal,
            'balance' => $balance,
            'visit_count' => $visitCount,
            'last_visit_date' => $lastVisit,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function visitsPaginator(Patient $patient, array $filters, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $q = self::visitBaseQuery($patient, $filters)
            ->with([
                'doctor:id,full_name',
                'appointment:id,appointment_date,status,notes',
                'invoices' => fn ($iq) => $iq->select(['id', 'visit_id', 'invoice_number', 'total', 'paid', 'status', 'created_at'])->orderByDesc('id')->limit(3),
            ])
            ->orderByDesc('visit_date')
            ->orderByDesc('id');

        return $q->paginate($perPage, ['*'], 'visits_page')->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function appointmentsPaginator(Patient $patient, array $filters, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $q = self::appointmentBaseQuery($patient, $filters)
            ->with(['doctor:id,full_name'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id');

        return $q->paginate($perPage, ['*'], 'appointments_page')->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function invoicesPaginator(Patient $patient, array $filters, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $q = self::invoiceBaseQuery($patient, $filters)
            ->with(['treatingDoctor:id,full_name', 'visit:id,visit_date,status'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return $q->paginate($perPage, ['*'], 'invoices_page')->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function paymentsPaginator(Patient $patient, array $filters, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $q = self::paymentBaseQuery($patient, $filters)
            ->with(['invoice:id,invoice_number,patient_id,doctor_id,status'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        return $q->paginate($perPage, ['*'], 'payments_page')->withQueryString();
    }

    public static function emptyPaginator(Request $request, string $pageName, int $perPage = 12): LengthAwarePaginator
    {
        $current = max(1, (int) $request->input($pageName, 1));

        return (new ConcretePaginator(new Collection, 0, $perPage, $current, [
            'path' => $request->url(),
            'pageName' => $pageName,
        ]))->appends($request->query());
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function visitBaseQuery(Patient $patient, array $filters): Builder
    {
        return Visit::query()
            ->where('patient_id', $patient->id)
            ->when($filters['doctor_id'], fn ($q) => $q->where('doctor_id', $filters['doctor_id']))
            ->when($filters['visit_status'], fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] instanceof CarbonInterface, fn ($q) => $q->whereDate('visit_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] instanceof CarbonInterface, fn ($q) => $q->whereDate('visit_date', '<=', $filters['date_to']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function appointmentBaseQuery(Patient $patient, array $filters): Builder
    {
        return Appointment::query()
            ->where('patient_id', $patient->id)
            ->when($filters['doctor_id'], fn ($q) => $q->where('doctor_id', $filters['doctor_id']))
            ->when($filters['appointment_status'], fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date_from'] instanceof CarbonInterface, fn ($q) => $q->whereDate('appointment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] instanceof CarbonInterface, fn ($q) => $q->whereDate('appointment_date', '<=', $filters['date_to']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function invoiceBaseQuery(Patient $patient, array $filters): Builder
    {
        return Invoice::query()
            ->where('patient_id', $patient->id)
            ->when($filters['doctor_id'], fn ($q) => $q->where('doctor_id', $filters['doctor_id']))
            ->when($filters['invoice_status'], fn ($q, $s) => $q->where('status', $s))
            ->when($filters['visit_status'], fn ($q, $s) => $q->whereHas('visit', fn ($vq) => $vq->where('status', $s)))
            ->when($filters['appointment_status'], fn ($q, $s) => $q->whereHas('visit.appointment', fn ($aq) => $aq->where('status', $s)))
            ->when($filters['date_from'] instanceof CarbonInterface, fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] instanceof CarbonInterface, fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function paymentBaseQuery(Patient $patient, array $filters): Builder
    {
        return Payment::query()
            ->whereHas('invoice', function ($q) use ($patient, $filters) {
                $q->where('patient_id', $patient->id);
                if ($filters['doctor_id']) {
                    $q->where('doctor_id', $filters['doctor_id']);
                }
                if ($filters['invoice_status']) {
                    $q->where('status', $filters['invoice_status']);
                }
                if ($filters['visit_status']) {
                    $q->whereHas('visit', fn ($vq) => $vq->where('status', $filters['visit_status']));
                }
                if ($filters['appointment_status']) {
                    $q->whereHas('visit.appointment', fn ($aq) => $aq->where('status', $filters['appointment_status']));
                }
                if ($filters['date_from'] instanceof CarbonInterface) {
                    $q->whereDate('created_at', '>=', $filters['date_from']);
                }
                if ($filters['date_to'] instanceof CarbonInterface) {
                    $q->whereDate('created_at', '<=', $filters['date_to']);
                }
            })
            ->when($filters['date_from'] instanceof CarbonInterface, fn ($q) => $q->whereDate('payment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] instanceof CarbonInterface, fn ($q) => $q->whereDate('payment_date', '<=', $filters['date_to']));
    }
}
