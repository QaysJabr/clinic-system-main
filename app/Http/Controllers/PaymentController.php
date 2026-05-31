<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InAppNotificationService;
use App\Support\AuditLogger;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethods::codes())],
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        return DB::transaction(function () use ($request) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($request->invoice_id);

            $alreadyPaid = round((float) $invoice->payments()->sum('amount'), 2);
            $total = round((float) $invoice->total, 2);
            $remaining = round(max(0, $total - $alreadyPaid), 2);
            $amount = round((float) $request->input('amount'), 2);

            if ($amount > $remaining + 0.009) {
                throw ValidationException::withMessages([
                    'amount' => $remaining <= 0
                        ? __('payments.validation_invoice_fully_paid')
                        : __('payments.validation_amount_exceeds_remaining', ['remaining' => number_format($remaining, 2)]),
                ]);
            }

            $payment = Payment::query()->create($request->only(['invoice_id', 'amount', 'payment_method', 'payment_date', 'notes']));

            $paid = round((float) $invoice->payments()->sum('amount'), 2);
            $status = $this->calculateStatus((float) $invoice->total, $paid);

            $invoice->update([
                'paid' => number_format($paid, 2, '.', ''),
                'status' => $status,
            ]);

            $invoice->refresh();

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

            $invoice->load('patient');
            app(InAppNotificationService::class)->notifyPaymentRecorded($payment, $invoice);

            return redirect()->back()->with('success', __('payments.flash_added'));
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
