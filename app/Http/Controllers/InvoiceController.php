<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Visit;
use App\Support\AuditLogger;
use App\Support\ClinicBusinessRules;
use App\Support\ClinicSettings;
use App\Support\Queries\InvoiceListQuery;
use App\Support\ClinicPdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scope = Invoice::query();
        $today = now()->toDateString();

        $invoiceStats = [
            'today' => (clone $scope)->whereDate('created_at', $today)->count(),
            'unpaid' => (clone $scope)->where('status', 'unpaid')->count(),
            'partial' => (clone $scope)->where('status', 'partial')->count(),
            'outstanding' => (float) ((clone $scope)->whereIn('status', ['unpaid', 'partial'])
                ->selectRaw('COALESCE(SUM(total - paid), 0) as outstanding')
                ->value('outstanding') ?? 0),
        ];

        $invoices = InvoiceListQuery::apply(
            $scope->select([
                'id',
                'invoice_number',
                'patient_id',
                'visit_id',
                'doctor_id',
                'total',
                'paid',
                'status',
                'due_date',
                'created_at',
            ]),
            $request
        )
            ->with([
                'patient:id,full_name',
                'treatingDoctor:id,full_name',
                'visit:id,visit_date',
            ])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends($request->query());

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $pageTitle = __('invoices.page_title_index');

        if ($request->ajax()) {
            return view('invoices.partials.content', compact('invoices', 'pageTitle', 'doctors', 'invoiceStats'));
        }

        return view('invoices.index', compact('invoices', 'pageTitle', 'doctors', 'invoiceStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('full_name')->get();
        $visits = Visit::with('patient')->orderByDesc('visit_date')->get();
        $services = Service::query()->active()->orderBy('name')->get(['id', 'name', 'price', 'doctor_percentage', 'is_active']);

        $pageTitle = __('invoices.page_title_create');

        if ($request->ajax()) {
            return view('invoices.partials.create', compact('patients', 'visits', 'services', 'pageTitle'));
        }

        return view('invoices.create', compact('patients', 'visits', 'services', 'pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:visits,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.item_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $this->assertPatientMatchesVisit((int) $validated['patient_id'], $validated['visit_id'] ?? null);
        $this->assertSingleInvoicePerVisitRule($validated['visit_id'] ?? null);

        $invoice = DB::transaction(function () use ($validated) {
            $invoiceNumber = 'INV-'.strtoupper(Str::random(8)).'-'.now()->format('Ymd');

            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            $total = round((float) $total, 2);

            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
                'paid' => '0.00',
                'status' => 'unpaid',
            ]);

            foreach ($validated['items'] as $item) {
                $resolved = $this->resolveInvoiceLineItem($item);
                $itemTotal = round((float) $resolved['price'] * (int) $resolved['quantity'], 2);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $resolved['service_id'],
                    'item_name' => $resolved['item_name'],
                    'price' => $resolved['price'],
                    'quantity' => $resolved['quantity'],
                    'total' => $itemTotal,
                ]);
            }

            return $invoice->fresh(['items']);
        });

        AuditLogger::log(
            'create',
            'invoices',
            $invoice->id,
            __('invoices.audit_create', ['number' => $invoice->invoice_number]),
            null,
            $this->invoiceSnapshot($invoice),
            Invoice::class
        );

        return redirect()->route('invoices.index')->with('success', __('invoices.flash_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $invoice = Invoice::with(['patient', 'visit', 'items', 'payments', 'treatingDoctor'])->findOrFail($id);
        $this->authorize('view', $invoice);

        $pageTitle = __('invoices.page_title_show');

        if ($request->ajax()) {
            return view('invoices.partials.show', compact('invoice', 'pageTitle'));
        }

        return view('invoices.show', compact('invoice', 'pageTitle'));
    }

    /**
     * Printable HTML view (no app layout).
     */
    public function printInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        AuditLogger::log(
            'export',
            'invoices',
            $invoice->id,
            __('invoices.audit_export_print', ['number' => $invoice->invoice_number]),
            null,
            null,
            Invoice::class
        );

        $invoice->load(['patient', 'visit', 'items', 'payments']);
        $clinic = ClinicSettings::current();

        return view('invoices.print', [
            'invoice' => $invoice,
            'clinic' => $clinic,
            'backUrl' => route('invoices.show', $invoice),
        ]);
    }

    /**
     * PDF export.
     */
    public function pdfInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        AuditLogger::log(
            'export',
            'invoices',
            $invoice->id,
            __('invoices.audit_export_pdf', ['number' => $invoice->invoice_number]),
            null,
            null,
            Invoice::class
        );

        $invoice->load(['patient', 'visit', 'items', 'payments']);
        $clinic = ClinicSettings::current();

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $invoice->invoice_number);

        return ClinicPdf::download(
            'invoices.print',
            compact('invoice', 'clinic'),
            __('invoices.pdf_download_prefix').'-'.$safeName.'.pdf'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $this->authorize('update', $invoice);
        $patients = Patient::orderBy('full_name')->get();
        $visits = Visit::with('patient')->orderByDesc('visit_date')->get();
        $services = Service::query()->active()->orderBy('name')->get(['id', 'name', 'price', 'doctor_percentage', 'is_active']);

        $pageTitle = __('invoices.page_title_edit');

        if ($request->ajax()) {
            return view('invoices.partials.edit', compact('invoice', 'patients', 'visits', 'services', 'pageTitle'));
        }

        return view('invoices.edit', compact('invoice', 'patients', 'visits', 'services', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:visits,id',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.item_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $this->assertPatientMatchesVisit((int) $validated['patient_id'], $validated['visit_id'] ?? null);
        $this->assertSingleInvoicePerVisitRule($validated['visit_id'] ?? null, $invoice->id);

        $old = $this->invoiceSnapshot($invoice);

        DB::transaction(function () use ($validated, $invoice) {
            $invoice->items()->delete();

            $total = 0;
            foreach ($validated['items'] as $item) {
                $resolved = $this->resolveInvoiceLineItem($item);
                $itemTotal = round((float) $resolved['price'] * (int) $resolved['quantity'], 2);
                $total += $itemTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $resolved['service_id'],
                    'item_name' => $resolved['item_name'],
                    'price' => $resolved['price'],
                    'quantity' => $resolved['quantity'],
                    'total' => $itemTotal,
                ]);
            }
            $total = round((float) $total, 2);

            $paid = round((float) $invoice->payments()->sum('amount'), 2);
            if ($total + 0.009 < $paid) {
                throw ValidationException::withMessages([
                    'items' => __('invoices.validation_total_below_payments', ['amount' => number_format($paid, 2)]),
                ]);
            }
            $status = $this->calculateStatus($total, $paid);

            $invoice->update([
                'patient_id' => $validated['patient_id'],
                'visit_id' => $validated['visit_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
                'paid' => $paid,
                'status' => $status,
            ]);
        });

        $invoice->refresh()->load('items');

        AuditLogger::log(
            'update',
            'invoices',
            $invoice->id,
            __('invoices.audit_update', ['number' => $invoice->invoice_number]),
            $old,
            $this->invoiceSnapshot($invoice),
            Invoice::class
        );

        return redirect()->route('invoices.index')->with('success', __('invoices.flash_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $this->authorize('delete', $invoice);

        if ($invoice->payments()->exists()) {
            return redirect()->route('invoices.index')
                ->with('error', __('invoices.flash_delete_has_payments'));
        }

        $old = $this->invoiceSnapshot($invoice);
        $iid = $invoice->id;
        $number = $invoice->invoice_number;
        $invoice->delete();

        AuditLogger::log(
            'delete',
            'invoices',
            $iid,
            __('invoices.audit_delete', ['number' => $number]),
            $old,
            null,
            Invoice::class
        );

        return redirect()->route('invoices.index')->with('success', __('invoices.flash_deleted'));
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

    /**
     * @return array<string, mixed>
     */
    private function invoiceSnapshot(Invoice $invoice): array
    {
        $invoice->loadMissing('items');

        return [
            'invoice_number' => $invoice->invoice_number,
            'patient_id' => $invoice->patient_id,
            'visit_id' => $invoice->visit_id,
            'doctor_id' => $invoice->doctor_id,
            'total' => (string) $invoice->total,
            'paid' => (string) $invoice->paid,
            'status' => $invoice->status,
            'notes' => $invoice->notes,
            'items' => $invoice->items->map(fn (InvoiceItem $i) => [
                'service_id' => $i->service_id,
                'item_name' => $i->item_name,
                'price' => (string) $i->price,
                'quantity' => $i->quantity,
                'total' => (string) $i->total,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{service_id: int|null, item_name: string, price: float|int|string, quantity: int}
     */
    private function resolveInvoiceLineItem(array $item): array
    {
        $serviceId = isset($item['service_id']) && $item['service_id'] !== '' && $item['service_id'] !== null
            ? (int) $item['service_id']
            : null;
        $name = trim((string) ($item['item_name'] ?? ''));
        $price = $item['price'];
        $quantity = (int) ($item['quantity'] ?? 1);

        if ($serviceId !== null) {
            $service = Service::query()->find($serviceId);
            if (! $service || ! $service->is_active) {
                throw ValidationException::withMessages([
                    'items' => __('invoices.validation_invalid_service'),
                ]);
            }
            if ($name === '') {
                $name = $service->name;
            }
        }

        if ($name === '') {
            throw ValidationException::withMessages([
                'items' => __('invoices.validation_item_name_required'),
            ]);
        }

        return [
            'service_id' => $serviceId,
            'item_name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    private function assertSingleInvoicePerVisitRule(?string $visitId, ?int $ignoreInvoiceId = null): void
    {
        if ($visitId === null || $visitId === '') {
            return;
        }

        if (! ClinicBusinessRules::settings()->enforce_one_invoice_per_visit) {
            return;
        }

        $q = Invoice::query()->where('visit_id', (int) $visitId);
        if ($ignoreInvoiceId !== null) {
            $q->where('id', '!=', $ignoreInvoiceId);
        }

        if ($q->exists()) {
            throw ValidationException::withMessages([
                'visit_id' => __('invoices.validation_one_invoice_per_visit'),
            ]);
        }
    }

    private function assertPatientMatchesVisit(int $patientId, ?string $visitId): void
    {
        if ($visitId === null || $visitId === '') {
            return;
        }

        $visit = Visit::query()->select(['id', 'patient_id'])->find((int) $visitId);
        if (! $visit) {
            return;
        }

        if ((int) $visit->patient_id !== $patientId) {
            throw ValidationException::withMessages([
                'patient_id' => __('invoices.validation_patient_visit_mismatch'),
                'visit_id' => __('invoices.validation_visit_patient_must_match'),
            ]);
        }
    }
}
