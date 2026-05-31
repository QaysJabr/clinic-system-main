<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Invoice;
use App\Services\Api\PaymentApiService;
use App\Support\PaymentMethods;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PaymentController extends ApiController
{
    use AuthorizesRequests;

    public function store(Request $request, PaymentApiService $payments): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethods::codes())],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $invoice = Invoice::query()->findOrFail($validated['invoice_id']);
        $this->authorize('view', $invoice);

        $payment = $payments->record($request->user(), $validated);

        return $this->created(new PaymentResource($payment));
    }
}
