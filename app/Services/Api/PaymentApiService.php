<?php

namespace App\Services\Api;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\InAppNotificationService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PaymentApiService
{
    public function record(User $user, array $validated): Payment
    {
        abort_unless($user->can('manage payments'), 403);

        return DB::transaction(function () use ($validated): Payment {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($validated['invoice_id']);

            $alreadyPaid = round((float) $invoice->payments()->sum('amount'), 2);
            $total = round((float) $invoice->total, 2);
            $remaining = round(max(0, $total - $alreadyPaid), 2);
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $remaining + 0.009) {
                throw ValidationException::withMessages([
                    'amount' => $remaining <= 0
                        ? __('payments.validation_invoice_fully_paid')
                        : __('payments.validation_amount_exceeds_remaining', ['remaining' => number_format($remaining, 2)]),
                ]);
            }

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $paid = round((float) $invoice->payments()->sum('amount'), 2);
            $status = $this->calculateStatus($total, $paid);

            $invoice->update([
                'paid' => number_format($paid, 2, '.', ''),
                'status' => $status,
            ]);

            $invoice->refresh()->load('patient');

            AuditLogger::log(
                'payment',
                'payments',
                $payment->id,
                __('payments.audit_payment_recorded', [
                    'amount' => number_format((float) $payment->amount, 2),
                    'invoice_number' => $invoice->invoice_number,
                ]),
                null,
                [
                    'invoice_id' => $payment->invoice_id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => (string) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date?->format('Y-m-d'),
                    'notes' => $payment->notes,
                    'invoice_paid_after' => (string) $invoice->paid,
                    'invoice_status_after' => $invoice->status,
                ],
                Payment::class
            );

            app(InAppNotificationService::class)->notifyPaymentRecorded($payment, $invoice);

            return $payment->fresh();
        });
    }

    private function calculateStatus(float $total, float $paid): string
    {
        $t = round($total, 2);
        $p = round($paid, 2);

        if ($p <= 0) {
            return 'unpaid';
        }
        if ($p < $t) {
            return 'partial';
        }

        return 'paid';
    }
}
