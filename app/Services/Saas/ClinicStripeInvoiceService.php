<?php

namespace App\Services\Saas;

use App\Models\Clinic;
use Illuminate\Support\Carbon;
use Stripe\StripeClient;

/**
 * @phpstan-type StripeInvoiceRow array{
 *     id: string,
 *     number: string,
 *     amount: float,
 *     currency: string,
 *     status: string,
 *     date: Carbon,
 *     hosted_url: ?string,
 *     pdf_url: ?string,
 * }
 */
final class ClinicStripeInvoiceService
{
    /**
     * @return list<StripeInvoiceRow>
     */
    public function recentForClinic(Clinic $clinic, ?int $limit = null): array
    {
        $stripeId = $clinic->stripe_id;
        $secret = config('cashier.secret');

        if (! is_string($stripeId) || $stripeId === '' || ! is_string($secret) || $secret === '') {
            return [];
        }

        $limit = $limit ?? (int) config('saas.stripe_invoices_limit', 12);
        $limit = max(1, min(24, $limit));

        try {
            $client = new StripeClient($secret);
            $list = $client->invoices->all([
                'customer' => $stripeId,
                'limit' => $limit,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return [];
        }

        $rows = [];
        foreach ($list->data as $invoice) {
            $amountCents = $invoice->amount_paid > 0
                ? $invoice->amount_paid
                : ($invoice->amount_due ?? 0);

            $rows[] = [
                'id' => (string) $invoice->id,
                'number' => (string) ($invoice->number ?? $invoice->id),
                'amount' => round($amountCents / 100, 2),
                'currency' => strtoupper((string) ($invoice->currency ?? 'usd')),
                'status' => (string) ($invoice->status ?? 'unknown'),
                'date' => Carbon::createFromTimestamp((int) $invoice->created),
                'hosted_url' => $invoice->hosted_invoice_url ?: null,
                'pdf_url' => $invoice->invoice_pdf ?: null,
            ];
        }

        return $rows;
    }
}
