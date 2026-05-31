<?php

namespace App\Services\Api;

use App\Http\Resources\Api\AppointmentResource;
use App\Http\Resources\Api\InvoiceResource;
use App\Http\Resources\Api\PaymentResource;
use App\Http\Resources\Api\VisitResource;
use App\Models\User;
use App\Services\ClinicDashboardService;
use App\Support\ClinicFinancialStats;
use App\Support\ClinicPermissions;

/**
 * Slim dashboard payload for the mobile home screen.
 */
final class MobileDashboardService
{
    public function __construct(
        private readonly ClinicDashboardService $dashboard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $data = $this->dashboard->buildForUser($user);
        $persona = (string) ($data['dashboardPersona'] ?? 'admin');
        $alerts = $this->formatAlerts($data['dashAlerts'] ?? []);

        $payload = [
            'persona' => $persona,
            'persona_label' => (string) ($data['dashboardPersonaLabel'] ?? ''),
            'currency' => $data['cur'] ?? '',
            'clinic' => [
                'name' => $data['clinic']->clinic_name ?? '',
                'logo_url' => $data['clinic']->logoPublicUrl(),
            ],
            'stats' => [
                'today_appointments' => (int) ($data['todayAppointments'] ?? 0),
                'today_visits' => (int) ($data['todayVisits'] ?? 0),
                'today_visits_waiting' => (int) ($data['todayVisitsWaiting'] ?? 0),
                'open_invoices' => (int) ($data['openInvoicesCount'] ?? 0),
            ],
            'today_appointments' => AppointmentResource::collection($data['todayAppointmentsList'] ?? collect()),
            'visit_queue' => VisitResource::collection($data['todayVisitsQueue'] ?? collect()),
            'alerts' => $alerts,
            'recent_invoices' => InvoiceResource::collection(
                ($data['recentInvoices'] ?? collect())
                    ->filter(fn ($invoice) => in_array($invoice->status, ['unpaid', 'partial'], true))
                    ->values()
            ),
            'recent_payments' => PaymentResource::collection($data['recentPayments'] ?? collect()),
        ];

        if ($user->can(ClinicPermissions::VIEW_REPORTS)) {
            $payload['financial'] = [
                'today_cash_collected' => (float) ($data['todayCashCollected'] ?? ClinicFinancialStats::todayCashCollected()),
                'accounts_receivable' => (float) ClinicFinancialStats::totalAccountsReceivable(),
                'today_net_cash_flow' => (float) ClinicFinancialStats::todayNetCashFlow(),
            ];
        }

        if (! empty($data['doctorEarning']) && is_array($data['doctorEarning'])) {
            $payload['doctor_earning'] = [
                'total' => (float) ($data['doctorEarning']['total'] ?? 0),
                'pending' => (float) ($data['doctorEarning']['pending'] ?? 0),
                'paid' => (float) ($data['doctorEarning']['paid'] ?? 0),
            ];
        }

        return $payload;
    }

    /**
     * @param  array<string, int|float>  $raw
     * @return list<array<string, string|number>>
     */
    private function formatAlerts(array $raw): array
    {
        $alerts = [];

        if (isset($raw['open_invoice_count']) && (int) $raw['open_invoice_count'] > 0) {
            $count = (int) $raw['open_invoice_count'];
            $alerts[] = [
                'type' => 'warning',
                'title' => 'فواتير مفتوحة',
                'message' => "لديك {$count} فاتورة تحتاج متابعة",
            ];
        }

        if (isset($raw['pending_doctor_amount']) && (float) $raw['pending_doctor_amount'] > 0) {
            $amount = number_format((float) $raw['pending_doctor_amount'], 2);
            $alerts[] = [
                'type' => 'info',
                'title' => 'أرباح أطباء معلّقة',
                'message' => "مبلغ معلّق: {$amount}",
            ];
        }

        return $alerts;
    }
}
