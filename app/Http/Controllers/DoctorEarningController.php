<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Services\InAppNotificationService;
use App\Support\AuditLogger;
use App\Support\Queries\DoctorEarningListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorEarningController extends Controller
{
    public function index(Request $request): View
    {
        $scopedQuery = DoctorEarningListQuery::applyDoctorScope(DoctorEarning::query(), Auth::user());
        $filterQuery = DoctorEarningListQuery::apply(clone $scopedQuery, $request, applyStatusFilter: true);

        $earnings = (clone $filterQuery)
            ->with([
                'doctor:id,full_name,staff_id',
                'doctor.staff:id,full_name',
                'invoice:id,invoice_number,visit_id',
                'visit:id,visit_date',
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = DoctorEarningListQuery::apply(clone $scopedQuery, $request, applyStatusFilter: false);

        $earningStats = [
            'pending_total' => (float) (clone $summaryQuery)->where('status', DoctorEarning::STATUS_PENDING)->sum('earning_amount'),
            'paid_total' => (float) (clone $summaryQuery)->where('status', DoctorEarning::STATUS_PAID)->sum('earning_amount'),
            'pending_count' => (clone $summaryQuery)->where('status', DoctorEarning::STATUS_PENDING)->count(),
            'records_count' => (clone $filterQuery)->count(),
        ];
        $lastPaymentDate = (clone $summaryQuery)->where('status', DoctorEarning::STATUS_PAID)->max('paid_at');

        $user = Auth::user();
        $doctorFilterList = ($user && $user->hasRole('doctor') && ! $user->hasRole('admin'))
            ? collect()
            : Doctor::query()
                ->orderBy('full_name')
                ->get(['id', 'full_name']);

        $batchPayDoctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $linked = $user->linkedDoctor();
            $batchPayDoctors = $linked ? collect([$linked]) : collect();
        }

        $pageTitle = (Auth::user()?->hasRole('doctor') && ! Auth::user()?->hasRole('admin'))
            ? __('doctors.earnings_page_title_own')
            : __('doctors.earnings_page_title_all');

        $viewData = [
            'earnings' => $earnings,
            'doctorFilterList' => $doctorFilterList,
            'filterDoctorId' => $request->input('doctor_id'),
            'filterStatus' => $request->input('status'),
            'filterDateFrom' => $request->input('date_from'),
            'filterDateTo' => $request->input('date_to'),
            'earningStats' => $earningStats,
            'lastPaymentDate' => $lastPaymentDate,
            'batchPayDoctors' => $batchPayDoctors,
            'pageTitle' => $pageTitle,
        ];

        if ($request->ajax()) {
            return view('doctor-earnings.partials.content', $viewData);
        }

        return view('doctor-earnings.index', $viewData);
    }

    public function exportCsv(Request $request)
    {
        $scopedQuery = DoctorEarningListQuery::applyDoctorScope(DoctorEarning::query(), Auth::user());
        $filterQuery = DoctorEarningListQuery::apply(clone $scopedQuery, $request, applyStatusFilter: true);
        $rows = (clone $filterQuery)
            ->with([
                'doctor:id,full_name,staff_id',
                'doctor.staff:id,full_name',
                'invoice:id,invoice_number',
                'visit:id,visit_date',
            ])
            ->orderByDesc('created_at')
            ->get();

        $callback = static function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'doctor', 'invoice_id', 'visit_id', 'total_amount', 'percentage_rate', 'earning_amount', 'status', 'paid_at', 'created_at']);
            foreach ($rows as $e) {
                $doctorLabel = optional(optional($e->doctor)->staff)->full_name
                    ?? optional($e->doctor)->full_name
                    ?? '';
                fputcsv($out, [
                    $e->id,
                    $doctorLabel,
                    $e->invoice_id,
                    $e->visit_id,
                    $e->total_amount,
                    $e->percentage_rate,
                    $e->earning_amount,
                    $e->status,
                    $e->paid_at?->format('Y-m-d H:i:s'),
                    $e->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        $filename = 'doctor-earnings-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function batchPay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $user = Auth::user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if (! $doc || (int) $doc->id !== (int) $validated['doctor_id']) {
                return redirect()
                    ->route('doctor-earnings.index', $request->only(['doctor_id', 'status', 'date_from', 'date_to']))
                    ->with('error', __('doctors.error_batch_other_doctor'));
            }
        }

        $paidCount = 0;
        $batchTotal = 0.0;
        $now = now();

        DB::transaction(function () use ($validated, $now, &$paidCount, &$batchTotal): void {
            $q = DoctorEarning::query()
                ->where('doctor_id', (int) $validated['doctor_id'])
                ->where('status', DoctorEarning::STATUS_PENDING)
                ->lockForUpdate();

            if (! empty($validated['date_from'])) {
                $q->whereDate('created_at', '>=', $validated['date_from']);
            }
            if (! empty($validated['date_to'])) {
                $q->whereDate('created_at', '<=', $validated['date_to']);
            }

            $pending = $q->get();
            $batchTotal = (float) $pending->sum('earning_amount');

            DoctorEarning::withoutEvents(function () use ($pending, $now, &$paidCount): void {
                foreach ($pending as $earning) {
                    $earning->update([
                        'status' => DoctorEarning::STATUS_PAID,
                        'paid_at' => $now,
                    ]);
                    $paidCount++;
                }
            });
        });

        if ($paidCount > 0) {
            $doctor = Doctor::query()->with('staff')->find((int) $validated['doctor_id']);
            $clinicId = (int) ($doctor?->clinic_id ?? 0);
            $doctorName = optional(optional($doctor)->staff)->full_name
                ?? optional($doctor)->full_name
                ?? __('doctors.fallback_doctor_number', ['id' => $validated['doctor_id']]);
            if ($clinicId > 0) {
                app(InAppNotificationService::class)->notifyDoctorEarningsBatchSettled(
                    $clinicId,
                    (int) $validated['doctor_id'],
                    $doctorName,
                    $paidCount,
                    $batchTotal
                );
            }
        }

        AuditLogger::log(
            'update',
            'doctor_earnings',
            (int) $validated['doctor_id'],
            __('doctors.audit_batch_pay', ['count' => $paidCount]),
            null,
            ['doctor_id' => (int) $validated['doctor_id'], 'paid_count' => $paidCount],
        );

        return redirect()
            ->route('doctor-earnings.index', $request->only(['doctor_id', 'status', 'date_from', 'date_to']))
            ->with('success', __('doctors.flash_batch_paid', ['count' => $paidCount]));
    }

    public function markAsPaid(Request $request, DoctorEarning $doctorEarning): RedirectResponse
    {
        if ($doctorEarning->status !== DoctorEarning::STATUS_PENDING) {
            return redirect()
                ->route('doctor-earnings.index', $request->only(['doctor_id', 'status', 'date_from', 'date_to']))
                ->with('error', __('doctors.error_not_pending'));
        }

        $doctorEarning->loadMissing(['doctor.staff']);

        $doctorEarning->update([
            'status' => DoctorEarning::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $doctorEarning->refresh();

        $doctorLabel = optional(optional($doctorEarning->doctor)->staff)->full_name
            ?? optional($doctorEarning->doctor)->full_name
            ?? __('doctors.fallback_doctor_number', ['id' => $doctorEarning->doctor_id]);

        $invoiceRef = $doctorEarning->invoice_id !== null
            ? (string) $doctorEarning->invoice_id
            : __('common.em_dash');

        AuditLogger::log(
            'update',
            'doctor_earnings',
            $doctorEarning->id,
            __('doctors.audit_mark_paid', ['doctor' => $doctorLabel, 'invoice' => $invoiceRef]),
            ['status' => DoctorEarning::STATUS_PENDING, 'paid_at' => null],
            ['status' => DoctorEarning::STATUS_PAID, 'paid_at' => $doctorEarning->paid_at?->format('Y-m-d H:i:s')],
        );

        return redirect()
            ->route('doctor-earnings.index', $request->only(['doctor_id', 'status', 'date_from', 'date_to']))
            ->with('success', __('doctors.flash_marked_paid'));
    }

}
