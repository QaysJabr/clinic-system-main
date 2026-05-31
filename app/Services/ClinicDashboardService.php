<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Visit;
use App\Support\ClinicDoctorEarningStats;
use App\Support\ClinicFinancialStats;
use App\Support\ClinicPermissions;
use App\Support\ClinicReportCache;
use App\Support\ClinicReportStats;
use App\Support\ClinicSettings;
use Illuminate\Support\Collection;

/**
 * Assembles all data for the main clinic dashboard (keeps controllers thin).
 */
final class ClinicDashboardService
{
    public function __construct(
        private readonly ClinicDashboardAnalyticsService $analytics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildForUser(User $user): array
    {
        $clinic = ClinicSettings::current();
        $persona = $this->resolvePersona($user);
        $dashboardStatsPersonal = $persona === ClinicDashboardPersona::Doctor;
        $personalDoctorId = $dashboardStatsPersonal ? optional($user->linkedDoctor())->id : null;
        $earningScopeDoctorId = $dashboardStatsPersonal ? $personalDoctorId : null;

        $operational = ClinicReportCache::rememberDashboardOperational(
            $dashboardStatsPersonal,
            $personalDoctorId,
            fn (): array => $this->operationalCounts($dashboardStatsPersonal, $personalDoctorId),
        );
        $recentOperational = $this->recentOperationalLists($dashboardStatsPersonal, $personalDoctorId);

        $financial = [];
        if ($user->can(ClinicPermissions::VIEW_REPORTS)) {
            $financial = ClinicReportCache::rememberDashboardFinancial(
                static fn (): array => ClinicFinancialStats::allForDashboard()
            );
        }

        $weeklyChart = null;
        if ($persona !== ClinicDashboardPersona::Receptionist) {
            $weeklyChart = ClinicReportCache::rememberDashboardAnalytics(
                $personalDoctorId,
                fn (): array => $dashboardStatsPersonal
                    ? $this->analytics->lastDaysVisits(7, $personalDoctorId)
                    : ($user->can(ClinicPermissions::VIEW_REPORTS)
                        ? $this->analytics->lastDaysCashIn(7, null)
                        : $this->analytics->lastDaysVisits(7, null))
            );
        }

        $activityStream = $this->analytics->activityStream($personalDoctorId);

        $doctorEarning = null;
        if ($user->can('view doctor earnings') || $user->can('manage doctor earnings')) {
            $doctorEarning = [
                'total' => ClinicDoctorEarningStats::totalEarnings($earningScopeDoctorId),
                'pending' => ClinicDoctorEarningStats::pendingTotal($earningScopeDoctorId),
                'paid' => ClinicDoctorEarningStats::paidTotal($earningScopeDoctorId),
                'monthly' => ClinicDoctorEarningStats::monthlyTrend(12, null, $earningScopeDoctorId),
            ];
        }

        $dashAlerts = $this->buildAlerts($user, $earningScopeDoctorId);

        return array_merge([
            'clinic' => $clinic,
            'cur' => $clinic->currency ?? '',
            'dashboardPersona' => $persona->value,
            'dashboardPersonaLabel' => __($persona->labelKey()),
            'doctorEarning' => $doctorEarning,
            'dashboardStatsPersonal' => $dashboardStatsPersonal,
            'dashAlerts' => $dashAlerts,
            'weeklyChart' => $weeklyChart,
            'activityStream' => $activityStream,
            'pageTitle' => __('dashboard.title'),
            'recentInvoices' => $this->recentInvoices($earningScopeDoctorId),
            'recentPayments' => $this->recentPayments($earningScopeDoctorId),
            'quickActions' => $this->quickActions($user, $dashboardStatsPersonal),
            'todayAppointmentsList' => $this->todayAppointmentsList($dashboardStatsPersonal, $personalDoctorId),
            'todayVisitsQueue' => $this->todayVisitsQueue($dashboardStatsPersonal, $personalDoctorId),
            'showFinancialSnapshot' => $user->can(ClinicPermissions::VIEW_REPORTS),
            'todayCashCollected' => ClinicFinancialStats::todayCashCollected(),
        ], $operational, $recentOperational, $financial);
    }

    public function resolvePersona(User $user): ClinicDashboardPersona
    {
        if ($user->hasRole('admin')) {
            return ClinicDashboardPersona::Admin;
        }
        if ($user->hasRole('accountant')) {
            return ClinicDashboardPersona::Accountant;
        }
        if ($user->hasRole('receptionist')) {
            return ClinicDashboardPersona::Receptionist;
        }
        if ($user->hasRole('doctor')) {
            return ClinicDashboardPersona::Doctor;
        }

        return ClinicDashboardPersona::Admin;
    }

    /**
     * Scalar operational KPIs only (safe for dashboard operational cache).
     *
     * @return array<string, mixed>
     */
    private function operationalCounts(bool $personal, ?int $doctorId): array
    {
        if ($personal && $doctorId) {
            $invoiceCounts = ClinicReportStats::invoiceCountsByStatus($doctorId);

            return [
                'totalPatients' => Patient::query()
                    ->whereHas('visits', fn ($q) => $q->where('doctor_id', $doctorId))
                    ->count(),
                'totalDoctors' => null,
                'totalAppointments' => Appointment::query()->where('doctor_id', $doctorId)->count(),
                'totalVisits' => Visit::query()->where('doctor_id', $doctorId)->count(),
                'totalInvoices' => Invoice::query()->where('doctor_id', $doctorId)->count(),
                'unpaidInvoices' => $invoiceCounts['unpaid'],
                'partialInvoices' => $invoiceCounts['partial'],
                'paidInvoices' => $invoiceCounts['paid'],
                'todayAppointments' => Appointment::query()
                    ->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', today())
                    ->count(),
                'todayVisits' => Visit::query()
                    ->where('doctor_id', $doctorId)
                    ->whereDate('visit_date', today())
                    ->count(),
                'todayVisitsWaiting' => Visit::query()
                    ->where('doctor_id', $doctorId)
                    ->whereDate('visit_date', today())
                    ->whereIn('status', [Visit::STATUS_WAITING, Visit::STATUS_IN_PROGRESS])
                    ->count(),
                'openInvoicesCount' => ($invoiceCounts['unpaid'] ?? 0) + ($invoiceCounts['partial'] ?? 0),
            ];
        }

        if ($personal) {
            return [
                'totalPatients' => 0,
                'totalDoctors' => null,
                'totalAppointments' => 0,
                'totalVisits' => 0,
                'totalInvoices' => 0,
                'unpaidInvoices' => 0,
                'partialInvoices' => 0,
                'paidInvoices' => 0,
                'todayAppointments' => 0,
                'todayVisits' => 0,
                'todayVisitsWaiting' => 0,
                'openInvoicesCount' => 0,
            ];
        }

        $invoiceCounts = ClinicReportStats::invoiceCountsByStatus();

        return [
            'totalPatients' => Patient::count(),
            'totalDoctors' => Doctor::count(),
            'totalAppointments' => Appointment::count(),
            'totalVisits' => Visit::count(),
            'totalInvoices' => Invoice::count(),
            'unpaidInvoices' => $invoiceCounts['unpaid'],
            'partialInvoices' => $invoiceCounts['partial'],
            'paidInvoices' => $invoiceCounts['paid'],
            'todayAppointments' => Appointment::query()->whereDate('appointment_date', today())->count(),
            'todayVisits' => Visit::query()->whereDate('visit_date', today())->count(),
            'todayVisitsWaiting' => Visit::query()
                ->whereDate('visit_date', today())
                ->whereIn('status', [Visit::STATUS_WAITING, Visit::STATUS_IN_PROGRESS])
                ->count(),
            'openInvoicesCount' => $invoiceCounts['unpaid'] + $invoiceCounts['partial'],
        ];
    }

    /**
     * @return Collection<int, Appointment>
     */
    private function todayAppointmentsList(bool $personal, ?int $doctorId): Collection
    {
        $query = Appointment::query()
            ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'start_time', 'end_time', 'status', 'reason', 'visit_id', 'created_at'])
            ->with(['patient:id,full_name', 'doctor:id,full_name'])
            ->whereDate('appointment_date', today())
            ->orderBy('start_time');

        if ($personal && $doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->take(10)->get();
    }

    /**
     * @return Collection<int, Visit>
     */
    private function todayVisitsQueue(bool $personal, ?int $doctorId): Collection
    {
        $query = Visit::query()
            ->select(['id', 'patient_id', 'doctor_id', 'visit_date', 'status', 'created_at'])
            ->with(['patient:id,full_name', 'doctor:id,full_name'])
            ->whereDate('visit_date', today())
            ->whereIn('status', [Visit::STATUS_WAITING, Visit::STATUS_IN_PROGRESS])
            ->orderByRaw("CASE status WHEN '".Visit::STATUS_WAITING."' THEN 0 ELSE 1 END")
            ->orderBy('created_at');

        if ($personal && $doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query->take(10)->get();
    }

    /**
     * Live lists for dashboard panels (never cached — Eloquent models do not survive cache reliably).
     *
     * @return array{recentPatients: Collection<int, Patient>, recentAppointments: Collection<int, Appointment>}
     */
    private function recentOperationalLists(bool $personal, ?int $doctorId): array
    {
        if ($personal && $doctorId) {
            return [
                'recentPatients' => Patient::query()
                    ->select(['id', 'full_name', 'phone', 'created_at'])
                    ->whereHas('visits', fn ($q) => $q->where('doctor_id', $doctorId))
                    ->latest()
                    ->take(5)
                    ->get(),
                'recentAppointments' => Appointment::query()
                    ->where('doctor_id', $doctorId)
                    ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'created_at'])
                    ->with(['patient:id,full_name', 'doctor:id,full_name'])
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        }

        if ($personal) {
            return [
                'recentPatients' => collect(),
                'recentAppointments' => collect(),
            ];
        }

        return [
            'recentPatients' => Patient::query()
                ->select(['id', 'full_name', 'phone', 'created_at'])
                ->latest()
                ->take(5)
                ->get(),
            'recentAppointments' => Appointment::query()
                ->select(['id', 'patient_id', 'doctor_id', 'appointment_date', 'created_at'])
                ->with(['patient:id,full_name', 'doctor:id,full_name'])
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function buildAlerts(User $user, ?int $earningScopeDoctorId): array
    {
        $dashAlerts = [];

        if ($user->can(ClinicPermissions::MANAGE_INVOICES)) {
            $openInvQ = Invoice::query()->whereIn('status', ['unpaid', 'partial']);
            if ($earningScopeDoctorId !== null) {
                $openInvQ->where('doctor_id', $earningScopeDoctorId);
            }
            $dashAlerts['open_invoice_count'] = $openInvQ->count();
        }

        if ($user->can(ClinicPermissions::VIEW_DOCTOR_EARNINGS) || $user->can(ClinicPermissions::MANAGE_DOCTOR_EARNINGS)) {
            $pendingQ = DoctorEarning::query()->where('status', DoctorEarning::STATUS_PENDING);
            if ($earningScopeDoctorId !== null) {
                $pendingQ->where('doctor_id', $earningScopeDoctorId);
            }
            $dashAlerts['pending_doctor_amount'] = (float) $pendingQ->sum('earning_amount');
            $dashAlerts['pending_doctor_rows'] = (clone $pendingQ)->count();
        }

        return $dashAlerts;
    }

    /**
     * @return Collection<int, Invoice>
     */
    private function recentInvoices(?int $doctorId): Collection
    {
        return Invoice::query()
            ->select(['id', 'invoice_number', 'patient_id', 'total', 'status', 'created_at'])
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->with(['patient:id,full_name'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    private function recentPayments(?int $doctorId): Collection
    {
        return Payment::query()
            ->select(['id', 'invoice_id', 'amount', 'payment_date', 'created_at'])
            ->when($doctorId !== null, function ($q) use ($doctorId): void {
                $q->whereHas('invoice', fn ($inv) => $inv->where('doctor_id', $doctorId));
            })
            ->with(['invoice:id,invoice_number,patient_id', 'invoice.patient:id,full_name'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Permission-filtered quick actions for the dashboard panel.
     *
     * @return list<array{label: string, href: string, variant: string}>
     */
    private function quickActions(User $user, bool $personal): array
    {
        $actions = [];

        $add = function (string $permission, string $route, string $labelKey, string $variant = 'primary') use ($user, &$actions): void {
            if ($user->can($permission)) {
                $actions[] = [
                    'label' => __($labelKey),
                    'href' => route($route),
                    'variant' => $variant,
                ];
            }
        };

        $add(ClinicPermissions::MANAGE_PATIENTS, 'patients.create', 'dashboard.action_new_patient', 'primary');
        $add(ClinicPermissions::MANAGE_APPOINTMENTS, 'appointments.create', 'dashboard.action_new_appointment', 'amber');
        $add(ClinicPermissions::MANAGE_VISITS, 'visits.create', 'dashboard.action_new_visit', 'violet');
        $add(ClinicPermissions::MANAGE_INVOICES, 'invoices.create', 'dashboard.action_new_invoice', 'orange');
        $add(ClinicPermissions::MANAGE_PATIENTS, 'patients.index', 'patients.title', 'ghost');
        $add(ClinicPermissions::MANAGE_APPOINTMENTS, 'appointments.index', 'appointments.nav_appointments', 'ghost');
        $add(ClinicPermissions::MANAGE_EXPENSES, 'expenses.index', 'expenses.dashboard_nav_expenses', 'ghost');
        $add(ClinicPermissions::VIEW_REPORTS, 'reports.index', 'dashboard.reports_nav', 'ghost');

        if ($user->can(ClinicPermissions::VIEW_DOCTOR_EARNINGS) || $user->can(ClinicPermissions::MANAGE_DOCTOR_EARNINGS)) {
            $actions[] = [
                'label' => $personal ? __('dashboard.doctor_earn_nav_personal') : __('dashboard.doctor_earn_nav_all'),
                'href' => route('doctor-earnings.index'),
                'variant' => 'ghost',
            ];
        }

        return $actions;
    }
}
