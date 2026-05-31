<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Support\Queries\ExpenseCategoryListQuery;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $base = ExpenseCategory::query();

        $categoryStats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', ExpenseCategory::STATUS_ACTIVE)->count(),
            'inactive' => (clone $base)->where('status', ExpenseCategory::STATUS_INACTIVE)->count(),
            'used' => (clone $base)->whereHas('expenses')->count(),
        ];

        $categories = ExpenseCategoryListQuery::apply(
            ExpenseCategory::query()->withCount('expenses')->orderBy('name'),
            $request,
        )->paginate(15)->withQueryString();

        $pageTitle = __('expenses.categories_page_title_index');

        $viewData = compact('categories', 'pageTitle', 'categoryStats');

        if ($request->ajax()) {
            return view('expense-categories.partials.content', $viewData);
        }

        return view('expense-categories.index', $viewData);
    }

    public function create(Request $request): View
    {
        $pageTitle = __('expenses.categories_page_title_create');

        if ($request->ajax()) {
            return view('expense-categories.partials.create', compact('pageTitle'));
        }

        return view('expense-categories.create', compact('pageTitle'));
    }

    public function store(Request $request): RedirectResponse
    {
        $cid = TenantValidation::clinicIdForRules();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories', 'name')->where(fn ($q) => $q->where('clinic_id', $cid))],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', __('expenses.categories_flash_created'));
    }

    public function edit(Request $request, ExpenseCategory $expense_category): View
    {
        $pageTitle = __('expenses.categories_page_title_edit');

        if ($request->ajax()) {
            return view('expense-categories.partials.edit', ['category' => $expense_category, 'pageTitle' => $pageTitle]);
        }

        return view('expense-categories.edit', ['category' => $expense_category, 'pageTitle' => $pageTitle]);
    }

    public function update(Request $request, ExpenseCategory $expense_category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories', 'name')->ignore($expense_category->id)->where(fn ($q) => $q->where('clinic_id', $expense_category->clinic_id))],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $expense_category->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', __('expenses.categories_flash_updated'));
    }
}
