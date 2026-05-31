<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use App\Support\ClinicFinancialStats;
use App\Support\MonthBucket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Cached chart series and unified activity feed for the clinic dashboard.
 */
final class ClinicDashboardAnalyticsService
{
    public const CHART_MONTHS = 6;

    /**
     * Daily patient cash-in for the last N days (dashboard sparkline).
     *
     * @return array{labels: list<string>, amounts: list<float>}
     */
    public function lastDaysCashIn(int $days = 7, ?int $doctorId = null): array
    {
        $days = max(3, min(14, $days));
        $at = now();
        $labels = [];
        $amounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $at->copy()->subDays($i);
            $labels[] = $day->copy()->locale(app()->getLocale())->translatedFormat('d/m');
            $query = Payment::query()->whereDate('payment_date', $day);
            if ($doctorId !== null) {
                $query->whereHas('invoice', fn ($inv) => $inv->where('doctor_id', $doctorId));
            }
            $amounts[] = round((float) ($query->sum('amount') ?? 0), 2);
        }

        return ['labels' => $labels, 'amounts' => $amounts];
    }

    /**
     * Daily visit counts for the last N days (doctor / operational view).
     *
     * @return array{labels: list<string>, counts: list<int>}
     */
    public function lastDaysVisits(int $days = 7, ?int $doctorId = null): array
    {
        $days = max(3, min(14, $days));
        $at = now();
        $labels = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $at->copy()->subDays($i);
            $labels[] = $day->copy()->locale(app()->getLocale())->translatedFormat('d/m');
            $query = Visit::query()->whereDate('visit_date', $day);
            if ($doctorId !== null) {
                $query->where('doctor_id', $doctorId);
            }
            $counts[] = (int) $query->count();
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    /**
     * @return array{
     *   revenue: array{labels: list<string>, accrual: list<float>, cash: list<float>},
     *   appointments: array{labels: list<string>, counts: list<int>},
     *   patients: array{labels: list<string>, counts: list<int>},
     *   payments: array{labels: list<string>, amounts: list<float>},
     *   doctors: array{labels: list<string>, visits: list<int>},
     * }
     */
    public function chartSeries(?int $doctorId = null, int $months = self::CHART_MONTHS): array
    {
        $months = max(1, min(12, $months));
        $at = now();
        $start = $at->copy()->subMonths($months - 1)->startOfMonth();

        $monthKeys = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $monthKeys[] = [
                'ym' => $m->format('Y-m'),
                'label' => $m->copy()->locale(app()->getLocale())->translatedFormat(__('dashboard.chart_month_format')),
            ];
        }

        $labels = array_column($monthKeys, 'label');
        $ymList = array_column($monthKeys, 'ym');

        $financial = ClinicFinancialStats::monthlyFinancialTrend($months, $at);
        $financialByYm = collect($financial)->keyBy('ym');

        $accrual = [];
        $cash = [];
        foreach ($ymList as $ym) {
            $row = $financialByYm->get($ym, []);
            $accrual[] = round((float) ($row['accrual_revenue'] ?? 0), 2);
            $cash[] = round((float) ($row['cash_in_patients'] ?? 0), 2);
        }

        $appointmentCounts = MonthBucket::counts(
            Appointment::query()
                ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId)),
            'appointment_date',
            $start,
            $at,
            $ymList,
        );

        $patientCounts = $doctorId !== null
            ? MonthBucket::distinctPatientsByVisitMonth($doctorId, $start, $at, $ymList)
            : MonthBucket::counts(
                Patient::query(),
                'created_at',
                $start,
                $at,
                $ymList,
            );

        $paymentAmounts = MonthBucket::sums(
            Payment::query()
                ->when($doctorId !== null, function ($q) use ($doctorId): void {
                    $q->whereHas('invoice', fn ($inv) => $inv->where('doctor_id', $doctorId));
                }),
            'payment_date',
            'amount',
            $start,
            $at,
            $ymList,
        );

        $doctorPerformance = ['labels' => [], 'visits' => []];
        if ($doctorId === null) {
            $doctorPerformance = $this->topDoctorsByVisits(5, $start, $at);
        }

        return [
            'revenue' => [
                'labels' => $labels,
                'accrual' => $accrual,
                'cash' => $cash,
            ],
            'appointments' => [
                'labels' => $labels,
                'counts' => $appointmentCounts,
            ],
            'patients' => [
                'labels' => $labels,
                'counts' => $patientCounts,
            ],
            'payments' => [
                'labels' => $labels,
                'amounts' => $paymentAmounts,
            ],
            'doctors' => $doctorPerformance,
        ];
    }

    /**
     * Unified recent activity for the dashboard sidebar.
     *
     * @return list<array{type: string, title: string, meta: string, at: string, url: string|null}>
     */
    public function activityStream(?int $doctorId = null, int $limit = 12): array
    {
        $items = collect();

        $patients = Patient::query()
            ->select(['id', 'full_name', 'created_at'])
            ->when($doctorId !== null, fn ($q) => $q->whereHas('visits', fn ($v) => $v->where('doctor_id', $doctorId)))
            ->latest()
            ->limit(5)
            ->get();

        foreach ($patients as $patient) {
            $items->push([
                'type' => 'patient',
                'title' => $patient->full_name,
                'meta' => __('dashboard.activity_patient_registered'),
                'at' => $patient->created_at?->toIso8601String() ?? '',
                'url' => route('patients.show', $patient),
            ]);
        }

        $appointments = Appointment::query()
            ->select(['id', 'patient_id', 'appointment_date', 'created_at'])
            ->with(['patient:id,full_name'])
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->latest()
            ->limit(5)
            ->get();

        foreach ($appointments as $appointment) {
            $items->push([
                'type' => 'appointment',
                'title' => optional($appointment->patient)->full_name ?? __('common.em_dash'),
                'meta' => __('dashboard.activity_appointment', ['date' => optional($appointment->appointment_date)?->format('d/m/Y') ?? '—']),
                'at' => $appointment->created_at?->toIso8601String() ?? '',
                'url' => route('appointments.edit', $appointment),
            ]);
        }

        $invoices = Invoice::query()
            ->select(['id', 'invoice_number', 'patient_id', 'total', 'created_at'])
            ->with(['patient:id,full_name'])
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->latest()
            ->limit(5)
            ->get();

        foreach ($invoices as $invoice) {
            $items->push([
                'type' => 'invoice',
                'title' => $invoice->invoice_number,
                'meta' => (optional($invoice->patient)->full_name ?? __('common.em_dash')).' · '.number_format((float) $invoice->total, 2),
                'at' => $invoice->created_at?->toIso8601String() ?? '',
                'url' => route('invoices.show', $invoice),
            ]);
        }

        $payments = Payment::query()
            ->select(['id', 'invoice_id', 'amount', 'payment_date', 'created_at'])
            ->with(['invoice:id,invoice_number,patient_id', 'invoice.patient:id,full_name'])
            ->when($doctorId !== null, function ($q) use ($doctorId): void {
                $q->whereHas('invoice', fn ($inv) => $inv->where('doctor_id', $doctorId));
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($payments as $payment) {
            $items->push([
                'type' => 'payment',
                'title' => optional(optional($payment->invoice)->patient)->full_name ?? __('common.em_dash'),
                'meta' => __('dashboard.activity_payment', ['amount' => number_format((float) $payment->amount, 2)]),
                'at' => ($payment->created_at ?? $payment->payment_date)?->toIso8601String() ?? '',
                'url' => $payment->invoice ? route('invoices.show', $payment->invoice) : null,
            ]);
        }

        return $items
            ->filter(fn (array $row): bool => $row['at'] !== '')
            ->sortByDesc('at')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $ymList
     * @return list<int>
     */
    private function distinctPatientsSeenByMonth(int $doctorId, Carbon $start, Carbon $end, array $ymList): array
    {
        $rows = Visit::query()
            ->where('doctor_id', $doctorId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->get(['patient_id', 'visit_date']);

        $seen = array_fill_keys($ymList, []);
        foreach ($rows as $row) {
            $ym = Carbon::parse($row->visit_date)->format('Y-m');
            if (isset($seen[$ym]) && $row->patient_id) {
                $seen[$ym][$row->patient_id] = true;
            }
        }

        $counts = [];
        foreach ($ymList as $ym) {
            $counts[] = count($seen[$ym] ?? []);
        }

        return $counts;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $ymList
     * @return list<int>
     */
    private function countsByMonth(
        $query,
        string $dateColumn,
        Carbon $start,
        Carbon $end,
        array $ymList,
        bool $useCreatedAt = false
    ): array {
        $column = $useCreatedAt ? 'created_at' : $dateColumn;

        $rows = (clone $query)
            ->whereBetween($column, [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get([$column]);

        $counts = array_fill_keys($ymList, 0);
        foreach ($rows as $row) {
            $date = $row->{$column} ?? null;
            if (! $date) {
                continue;
            }
            $ym = Carbon::parse($date)->format('Y-m');
            if (isset($counts[$ym])) {
                $counts[$ym]++;
            }
        }

        return array_values($counts);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $ymList
     * @return list<float>
     */
    private function sumByMonth(
        $query,
        string $dateColumn,
        string $amountColumn,
        Carbon $start,
        Carbon $end,
        array $ymList
    ): array {
        $rows = (clone $query)
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->get([$dateColumn, $amountColumn]);

        $sums = array_fill_keys($ymList, 0.0);
        foreach ($rows as $row) {
            $date = $row->{$dateColumn} ?? null;
            if (! $date) {
                continue;
            }
            $ym = Carbon::parse($date)->format('Y-m');
            if (isset($sums[$ym])) {
                $sums[$ym] += (float) $row->{$amountColumn};
            }
        }

        return array_map(fn (float $v): float => round($v, 2), array_values($sums));
    }

    /**
     * @return array{labels: list<string>, visits: list<int>}
     */
    private function topDoctorsByVisits(int $limit, Carbon $start, Carbon $end): array
    {
        $rows = Visit::query()
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('doctor_id, COUNT(*) as visit_count')
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->orderByDesc('visit_count')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return ['labels' => [], 'visits' => []];
        }

        $doctors = Doctor::query()
            ->whereIn('id', $rows->pluck('doctor_id'))
            ->pluck('full_name', 'id');

        $labels = [];
        $visits = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($doctors[$row->doctor_id] ?? __('dashboard.chart_unknown_doctor'));
            $visits[] = (int) $row->visit_count;
        }

        return ['labels' => $labels, 'visits' => $visits];
    }
}
