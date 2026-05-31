<?php

namespace App\Services\Inventory;

use App\Models\Expense;
use App\Models\InventoryPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Links inventory stock purchases to clinic expenses (finance module).
 */
final class InventoryPurchaseExpenseService
{
    public function createFromPurchase(InventoryPurchase $purchase, ?int $expenseCategoryId = null): ?Expense
    {
        $total = (float) $purchase->total_amount;
        if ($total <= 0 || $purchase->expense_id !== null) {
            return null;
        }

        return DB::transaction(function () use ($purchase, $expenseCategoryId, $total): Expense {
            $categoryId = $expenseCategoryId
                ?? app(ClinicInventorySetupService::class)->ensureInventoryExpenseCategory((int) $purchase->clinic_id);

            $supplierName = $purchase->supplier?->name;
            $title = __('inventory.expense_title', ['ref' => $purchase->documentNumber()]);
            if ($supplierName) {
                $title .= ' — '.$supplierName;
            }

            $expense = Expense::query()->create([
                'expense_category_id' => $categoryId,
                'title' => $title,
                'amount' => $total,
                'expense_date' => $purchase->purchase_date,
                'payment_method' => 'cash',
                'settlement_type' => Expense::SETTLEMENT_FULL,
                'notes' => trim(implode("\n", array_filter([
                    __('inventory.expense_note', ['ref' => $purchase->documentNumber()]),
                    $purchase->notes,
                ]))),
                'created_by' => Auth::id(),
            ]);

            $expense->payments()->create([
                'amount' => $total,
                'paid_at' => $purchase->purchase_date,
                'payment_method' => 'cash',
                'notes' => null,
                'created_by' => Auth::id(),
            ]);

            $purchase->update(['expense_id' => $expense->id]);

            return $expense;
        });
    }
}
