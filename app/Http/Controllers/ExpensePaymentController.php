<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Support\PaymentMethods;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExpensePaymentController extends Controller
{
    public function store(Request $request, Expense $expense): RedirectResponse
    {
        if (! $expense->isInstallments()) {
            throw ValidationException::withMessages([
                'amount' => __('expenses.payments_validation_installments_only'),
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', 'string', Rule::in(PaymentMethods::codes())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $paidAfter = round($expense->paidTotal() + (float) $validated['amount'], 2);
        if ($paidAfter > (float) $expense->amount + 0.009) {
            throw ValidationException::withMessages([
                'amount' => __('expenses.payments_validation_sum_exceeds', ['remaining' => number_format($expense->remainingAmount(), 2)]),
            ]);
        }

        $expense->payments()->create([
            'amount' => $validated['amount'],
            'paid_at' => $validated['paid_at'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $expense->refreshHeaderPaymentMethodFromPayments();

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', __('expenses.payments_flash_recorded'));
    }

    public function destroy(Request $request, Expense $expense, ExpensePayment $expensePayment): RedirectResponse
    {
        if ((int) $expensePayment->expense_id !== (int) $expense->id) {
            abort(404);
        }

        if (! $expense->isInstallments()) {
            return redirect()
                ->route('expenses.show', $expense)
                ->withErrors(['payment' => __('expenses.payments_validation_cannot_delete_full')]);
        }

        $expense->deletePayment($expensePayment);

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', __('expenses.payments_flash_deleted'));
    }
}
