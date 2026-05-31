<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Support\AuditLogger;
use App\Support\Queries\ExpenseListQuery;
use App\Support\ClinicSettings;
use App\Support\PaymentMethods;
use App\Support\ClinicPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $scope = Expense::query();
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $expenseStats = [
            'today' => (clone $scope)->whereDate('expense_date', $today)->count(),
            'month_total' => (float) (clone $scope)->whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount'),
            'unpaid' => (clone $scope)
                ->where('settlement_type', Expense::SETTLEMENT_INSTALLMENTS)
                ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM expense_payments WHERE expense_payments.expense_id = expenses.id) < expenses.amount - 0.009')
                ->count(),
            'installments' => (clone $scope)->where('settlement_type', Expense::SETTLEMENT_INSTALLMENTS)->count(),
        ];

        $expenses = ExpenseListQuery::apply(
            $scope->with(['category:id,name', 'creator:id,name'])->withSum('payments', 'amount'),
            $request
        )
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $categories = ExpenseCategory::query()->where('status', 'active')->orderBy('name')->get();

        $pageTitle = __('expenses.page_title_index');

        if ($request->ajax()) {
            return view('expenses.partials.content', compact('expenses', 'categories', 'pageTitle', 'expenseStats'));
        }

        return view('expenses.index', compact('expenses', 'categories', 'pageTitle', 'expenseStats'));
    }

    public function create(Request $request): View
    {
        $categories = ExpenseCategory::query()->where('status', 'active')->orderBy('name')->get();

        $pageTitle = __('expenses.page_title_create');

        if ($request->ajax()) {
            return view('expenses.partials.create', compact('categories', 'pageTitle'));
        }

        return view('expenses.create', compact('categories', 'pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settlement_type' => ['required', 'string', 'in:'.Expense::SETTLEMENT_FULL.','.Expense::SETTLEMENT_INSTALLMENTS],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required_if:settlement_type,'.Expense::SETTLEMENT_FULL, 'nullable', 'string', PaymentMethods::validationInRule()],
            'notes' => ['nullable', 'string'],
            'first_payment_amount' => ['nullable', 'numeric', 'min:0.01'],
            'first_payment_paid_at' => ['required_with:first_payment_amount', 'nullable', 'date'],
            'first_payment_method' => ['required_with:first_payment_amount', 'nullable', 'string', PaymentMethods::validationInRule()],
            'first_payment_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['settlement_type'] === Expense::SETTLEMENT_INSTALLMENTS && $request->filled('first_payment_amount')) {
            if ((float) $validated['first_payment_amount'] > (float) $validated['amount'] + 0.009) {
                return back()->withErrors(['first_payment_amount' => __('expenses.validation_first_payment_over_total')])->withInput();
            }
        }

        $expense = DB::transaction(function () use ($request, $validated) {
            $headerMethod = $validated['settlement_type'] === Expense::SETTLEMENT_FULL
                ? ($validated['payment_method'] ?? 'cash')
                : (($validated['first_payment_method'] ?? null) ?: 'other');

            $expense = Expense::create([
                'expense_category_id' => $validated['expense_category_id'],
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $headerMethod,
                'settlement_type' => $validated['settlement_type'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            if ($validated['settlement_type'] === Expense::SETTLEMENT_FULL) {
                $expense->payments()->create([
                    'amount' => $expense->amount,
                    'paid_at' => $expense->expense_date,
                    'payment_method' => $expense->payment_method ?? 'cash',
                    'notes' => null,
                    'created_by' => $request->user()->id,
                ]);
            } elseif ($request->filled('first_payment_amount')) {
                $expense->payments()->create([
                    'amount' => $validated['first_payment_amount'],
                    'paid_at' => $validated['first_payment_paid_at'],
                    'payment_method' => $validated['first_payment_method'],
                    'notes' => $validated['first_payment_notes'] ?? null,
                    'created_by' => $request->user()->id,
                ]);
                $expense->refreshHeaderPaymentMethodFromPayments();
            }

            return $expense;
        });

        AuditLogger::log(
            'create',
            'expenses',
            $expense->id,
            __('expenses.audit_create', ['title' => $expense->title]),
            null,
            [
                'settlement_type' => $expense->settlement_type,
                'amount' => (string) $expense->amount,
            ]
        );

        return redirect()->route('expenses.show', $expense)
            ->with('success', __('expenses.flash_created'));
    }

    public function show(Request $request, Expense $expense): View
    {
        $expense->load(['category', 'creator', 'paymentsOrdered.creator:id,name']);
        $clinic = ClinicSettings::current();

        $pageTitle = __('expenses.page_show_prefix').' '.$expense->title;

        if ($request->ajax()) {
            return view('expenses.partials.show', compact('expense', 'clinic', 'pageTitle'));
        }

        return view('expenses.show', compact('expense', 'clinic', 'pageTitle'));
    }

    public function printExpense(Expense $expense)
    {
        $expense->load(['category', 'creator', 'paymentsOrdered.creator:id,name']);
        $clinic = ClinicSettings::current();

        AuditLogger::log(
            'export',
            'expenses',
            $expense->id,
            __('expenses.audit_print_doc', ['number' => $expense->documentNumber()]),
            null,
            null
        );

        return view('expenses.print', [
            'expense' => $expense,
            'clinic' => $clinic,
            'backUrl' => route('expenses.show', $expense),
        ]);
    }

    public function pdfExpense(Expense $expense)
    {
        $expense->load(['category', 'creator', 'paymentsOrdered.creator:id,name']);
        $clinic = ClinicSettings::current();

        AuditLogger::log(
            'export',
            'expenses',
            $expense->id,
            __('expenses.audit_pdf_doc', ['number' => $expense->documentNumber()]),
            null,
            null
        );

        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '_', $expense->documentNumber());

        return ClinicPdf::download(
            'expenses.print',
            compact('expense', 'clinic'),
            __('expenses.pdf_download_prefix').'-'.$safe.'.pdf'
        );
    }

    public function printPayment(Expense $expense, ExpensePayment $expensePayment)
    {
        if ((int) $expensePayment->expense_id !== (int) $expense->id) {
            abort(404);
        }

        $expense->load(['category']);
        $expensePayment->load('creator:id,name');
        $clinic = ClinicSettings::current();

        AuditLogger::log(
            'export',
            'expenses',
            $expense->id,
            __('expenses.audit_payment_print_receipt', ['id' => $expensePayment->id]),
            null,
            null
        );

        return view('expenses.print-payment', [
            'expense' => $expense,
            'payment' => $expensePayment,
            'clinic' => $clinic,
            'backUrl' => route('expenses.show', $expense),
        ]);
    }

    public function pdfPayment(Expense $expense, ExpensePayment $expensePayment)
    {
        if ((int) $expensePayment->expense_id !== (int) $expense->id) {
            abort(404);
        }

        $expense->load(['category']);
        $expensePayment->load('creator:id,name');
        $clinic = ClinicSettings::current();

        AuditLogger::log(
            'export',
            'expenses',
            $expense->id,
            __('expenses.audit_pdf_payment_receipt', ['id' => $expensePayment->id]),
            null,
            null
        );

        return ClinicPdf::download('expenses.print-payment', [
            'expense' => $expense,
            'payment' => $expensePayment,
            'clinic' => $clinic,
        ], __('expenses.payment_pdf_download_prefix').'-'.$expensePayment->id.'.pdf');
    }

    public function edit(Request $request, Expense $expense): View
    {
        $expense->load(['category', 'paymentsOrdered']);
        $categories = ExpenseCategory::query()
            ->where(function ($q) use ($expense) {
                $q->where('status', 'active')
                    ->orWhere('id', $expense->expense_category_id);
            })
            ->orderBy('name')
            ->get();

        $pageTitle = __('expenses.page_title_edit');

        if ($request->ajax()) {
            return view('expenses.partials.edit', compact('expense', 'categories', 'pageTitle'));
        }

        return view('expenses.edit', compact('expense', 'categories', 'pageTitle'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', PaymentMethods::validationInRule()],
            'notes' => ['nullable', 'string'],
        ]);

        if ($expense->isInstallments()) {
            $paid = $expense->paidTotal();
            if ((float) $validated['amount'] + 0.009 < $paid) {
                return back()->withErrors(['amount' => __('expenses.validation_amount_below_payments', ['amount' => number_format($paid, 2)])])->withInput();
            }
        }

        $old = [
            'expense_category_id' => $expense->expense_category_id,
            'title' => $expense->title,
            'amount' => (string) $expense->amount,
            'expense_date' => $expense->expense_date?->format('Y-m-d'),
            'payment_method' => $expense->payment_method,
            'notes' => $expense->notes,
        ];

        DB::transaction(function () use ($expense, $validated): void {
            $expense->update([
                'expense_category_id' => $validated['expense_category_id'],
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($expense->isFullSettlement()) {
                $expense->syncFullSettlementPaymentRow();
            }
        });

        $expense->refresh();

        AuditLogger::log(
            'update',
            'expenses',
            $expense->id,
            __('expenses.audit_update', ['title' => $expense->title]),
            $old,
            [
                'expense_category_id' => $expense->expense_category_id,
                'title' => $expense->title,
                'amount' => (string) $expense->amount,
                'expense_date' => $expense->expense_date?->format('Y-m-d'),
                'payment_method' => $expense->payment_method,
                'notes' => $expense->notes,
            ],
            Expense::class
        );

        return redirect()->route('expenses.show', $expense)
            ->with('success', __('expenses.flash_updated'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $id = $expense->id;
        $old = [
            'title' => $expense->title,
            'amount' => (string) $expense->amount,
            'expense_date' => $expense->expense_date?->format('Y-m-d'),
        ];
        $expense->delete();

        AuditLogger::log(
            'delete',
            'expenses',
            $id,
            __('expenses.audit_delete', ['id' => $id]),
            $old,
            null,
            Expense::class
        );

        return redirect()->route('expenses.index')
            ->with('success', __('expenses.flash_deleted'));
    }
}
