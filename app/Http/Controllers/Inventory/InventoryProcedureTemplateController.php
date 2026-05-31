<?php

namespace App\Http\Controllers\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryProcedureTemplate;
use App\Models\InventoryProcedureTemplateItem;
use App\Support\AuditLogger;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryProcedureTemplateController extends InventoryController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $templateBase = InventoryProcedureTemplate::query();
        $templateStats = [
            'total' => (clone $templateBase)->count(),
            'auto_consume' => (clone $templateBase)->where('auto_consume', true)->count(),
        ];

        $templates = InventoryProcedureTemplate::query()
            ->withCount('lines')
            ->orderBy('name')
            ->paginate(20);

        $viewData = [
            'templates' => $templates,
            'templateStats' => $templateStats,
            'pageTitle' => __('inventory.templates_title'),
        ];

        if ($request->ajax()) {
            return view('inventory.templates.partials.content', $viewData);
        }

        return view('inventory.templates.index', $viewData);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('inventory.templates.create', [
            'items' => InventoryItem::query()->where('status', 'active')->orderBy('name')->get(),
            'pageTitle' => __('inventory.template_create_title'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $this->validatedTemplate($request);

        $template = InventoryProcedureTemplate::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'auto_consume' => $data['auto_consume'] ?? true,
        ]);

        $this->syncLines($template, $request->input('lines', []));

        AuditLogger::log('create', 'inventory_templates', $template->id, __('inventory.audit_template_created', ['name' => $template->name]));

        return redirect()->route('inventory.templates.index')->with('success', __('inventory.flash_template_created'));
    }

    public function edit(InventoryProcedureTemplate $template): View
    {
        $this->authorize('create', InventoryItem::class);

        $template->load('lines.item');

        return view('inventory.templates.edit', [
            'template' => $template,
            'items' => InventoryItem::query()->where('status', 'active')->orderBy('name')->get(),
            'pageTitle' => __('inventory.template_edit_title'),
        ]);
    }

    public function update(Request $request, InventoryProcedureTemplate $template): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $this->validatedTemplate($request, $template);

        $template->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'auto_consume' => $data['auto_consume'] ?? true,
        ]);

        $template->lines()->delete();
        $this->syncLines($template, $request->input('lines', []));

        return redirect()->route('inventory.templates.index')->with('success', __('inventory.flash_template_updated'));
    }

    /**
     * @param  list<mixed>  $lines
     */
    private function syncLines(InventoryProcedureTemplate $template, array $lines): void
    {
        foreach ($lines as $row) {
            if (! is_array($row)) {
                continue;
            }
            $itemId = (int) ($row['inventory_item_id'] ?? 0);
            $qty = (float) ($row['quantity'] ?? 0);
            if ($itemId <= 0 || $qty <= 0) {
                continue;
            }
            InventoryProcedureTemplateItem::query()->create([
                'inventory_procedure_template_id' => $template->id,
                'inventory_item_id' => $itemId,
                'quantity' => $qty,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedTemplate(Request $request, ?InventoryProcedureTemplate $template = null): array
    {
        $clinicId = TenantValidation::clinicIdForRules();

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'auto_consume' => ['sometimes', 'boolean'],
            'lines' => ['nullable', 'array'],
            'lines.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id,clinic_id,'.$clinicId],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);
    }
}
