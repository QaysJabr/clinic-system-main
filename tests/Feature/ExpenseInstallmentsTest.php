<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExpenseInstallmentsTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::query()->where('email', 'admin@clinic.local')->firstOrFail();
    }

    public function test_store_full_settlement_creates_single_payment_row(): void
    {
        $user = $this->adminUser();
        $cat = ExpenseCategory::query()->create([
            'name' => 'Cat-'.Str::random(8),
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('expenses.store'), [
            'settlement_type' => Expense::SETTLEMENT_FULL,
            'expense_category_id' => $cat->id,
            'title' => 'مصروف تجريبي',
            'amount' => 100,
            'expense_date' => '2026-01-15',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $expense = Expense::query()->latest('id')->first();
        $this->assertNotNull($expense);
        $this->assertTrue($expense->isFullSettlement());
        $this->assertCount(1, $expense->payments);
        $this->assertEqualsWithDelta(100.0, (float) $expense->payments->first()->amount, 0.001);
    }

    public function test_store_installments_and_add_second_payment(): void
    {
        $user = $this->adminUser();
        $cat = ExpenseCategory::query()->create([
            'name' => 'CatInst-'.Str::random(8),
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('expenses.store'), [
            'settlement_type' => Expense::SETTLEMENT_INSTALLMENTS,
            'expense_category_id' => $cat->id,
            'title' => 'مستلزمات',
            'amount' => 300,
            'expense_date' => '2026-02-01',
            'first_payment_amount' => 100,
            'first_payment_paid_at' => '2026-02-01',
            'first_payment_method' => 'bank_transfer',
        ])->assertRedirect();

        $expense = Expense::query()->latest('id')->first();
        $this->assertNotNull($expense);
        $this->assertTrue($expense->isInstallments());
        $this->assertCount(1, $expense->payments);
        $this->assertEqualsWithDelta(200.0, $expense->remainingAmount(), 0.001);

        $this->actingAs($user)->post(route('expenses.payments.store', $expense), [
            'amount' => 200,
            'paid_at' => '2026-03-01',
            'payment_method' => 'cash',
        ])->assertRedirect(route('expenses.show', $expense));

        $expense->refresh();
        $this->assertEqualsWithDelta(0.0, $expense->remainingAmount(), 0.001);
        $this->assertCount(2, $expense->payments);
    }
}
