<?php

namespace App\Services\Platform;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use App\Support\PlatformClinicIndexQuery;
use App\Support\PlatformClinicPresentation;
use App\Support\PlatformSubscriptionPaymentQuery;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PlatformCsvExportService
{
    public function clinics(Request $request): StreamedResponse
    {
        $rows = PlatformClinicIndexQuery::fromRequest($request)->get();
        $filename = 'platform-clinics-'.now()->format('Ymd-His').'.csv';

        return $this->streamCsv($filename, function ($out) use ($rows): void {
            fputcsv($out, [
                __('platform.col_id'),
                __('platform.col_name'),
                __('platform.col_owner'),
                __('platform.col_plan'),
                __('platform.col_subscription'),
                __('platform.col_expires'),
                __('platform.col_total_paid'),
                __('platform.col_is_active'),
            ]);

            foreach ($rows as $clinic) {
                $tier = PlatformClinicPresentation::subscriptionTier($clinic);
                fputcsv($out, [
                    $clinic->id,
                    $clinic->name,
                    $clinic->owner?->email ?? '',
                    $clinic->plan?->name ?? '',
                    PlatformClinicPresentation::tierLabel($tier),
                    $clinic->subscription_expires_at?->format('Y-m-d H:i:s') ?? '',
                    number_format((float) ($clinic->total_paid ?? 0), 2, '.', ''),
                    $clinic->is_active ? __('platform.yes') : __('platform.no'),
                ]);
            }
        });
    }

    public function payments(Request $request, ?int $clinicId = null): StreamedResponse
    {
        $rows = PlatformSubscriptionPaymentQuery::succeededFromRequest($request, $clinicId)->get();
        $suffix = $clinicId ? 'clinic-'.$clinicId.'-' : '';
        $filename = 'platform-payments-'.$suffix.now()->format('Ymd-His').'.csv';

        return $this->streamCsv($filename, function ($out) use ($rows): void {
            fputcsv($out, [
                __('platform.col_id'),
                __('platform.col_clinic'),
                __('platform.col_amount'),
                __('platform.col_source'),
                __('platform.col_paid_at'),
                __('platform.notes'),
            ]);

            foreach ($rows as $payment) {
                fputcsv($out, [
                    $payment->id,
                    $payment->clinic?->name ?? '',
                    number_format((float) $payment->amount, 2, '.', ''),
                    $payment->source === SubscriptionPayment::SOURCE_STRIPE
                        ? __('platform.source_stripe')
                        : __('platform.source_manual'),
                    $payment->paid_at?->format('Y-m-d H:i:s') ?? '',
                    $payment->notes ?? '',
                ]);
            }
        });
    }

    public function clinicPayments(Request $request, Clinic $clinic): StreamedResponse
    {
        $request->merge(['clinic_id' => $clinic->id]);

        return $this->payments($request, $clinic->id);
    }

    /**
     * @param  callable(resource): void  $writer
     */
    private function streamCsv(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            $writer($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
