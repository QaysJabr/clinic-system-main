@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service>|null $services */
    $services = $services ?? collect();
@endphp

@include('invoices.partials._invoice-form-classes')

@php
    $inputClass = $inputClass ?? 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $labelClass = $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700 dark:text-[#E5E7EB]';
    $itemLabelClass = $itemLabelClass ?? 'mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400';
    $removeItemBtnClass = $removeItemBtnClass ?? 'remove-item inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-900/40';
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
@endphp
@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto w-full max-w-[1100px]" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
    <div class="mb-6">
        <a href="{{ route('invoices.show', $invoice) }}" data-no-spa class="mb-3 inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-[#0F4C81] dark:text-slate-400 dark:hover:text-[#93C5FD]">{{ __('invoices.back_to_invoice') }}</a>
        <h1 class="m-0 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('invoices.page_title_edit') }}</h1>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#9CA3AF]">{{ $invoice->invoice_number }}</p>
    </div>

    <x-invoice.nav :invoice="$invoice" active="edit" />

    @can(\App\Support\ClinicPermissions::MANAGE_INVOICES)
        <p class="mb-4 rounded-lg border border-slate-200/90 bg-slate-50/80 px-4 py-3 text-sm text-gray-600 dark:border-[#374151] dark:bg-[#111827]/60 dark:text-slate-400">
            <a href="{{ route('services.index') }}" data-spa class="font-semibold text-[#0F4C81] hover:underline dark:text-[#93C5FD]">{{ __('invoices.link_services_catalog') }}</a>
            {{ __('invoices.services_catalog_hint') }}
        </p>
    @endcan

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#374151] dark:bg-[#1F2937]">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/90 sm:px-5">
            <h2 class="m-0 text-base font-bold text-gray-800 dark:text-[#F3F4F6]">{{ __('invoices.section_invoice_info') }}</h2>
        </div>
        <div class="p-4 sm:p-6">
            <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
                @csrf
                @method('PUT')

                <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="patient_id" class="{{ $labelClass }}">{{ __('invoices.field_patient') }}</label>
                        <select id="patient_id" name="patient_id" class="{{ $inputClass }}" required>
                            <option value="">{{ __('invoices.patient_select_placeholder') }}</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ (string) old('patient_id', $invoice->patient_id) === (string) $patient->id ? 'selected' : '' }}>{{ $patient->full_name }}</option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="visit_id" class="{{ $labelClass }}">{{ __('invoices.field_visit_optional') }}</label>
                        <select id="visit_id" name="visit_id" class="{{ $inputClass }}">
                            <option value="">{{ __('invoices.visit_none') }}</option>
                            @foreach($visits as $visit)
                                <option value="{{ $visit->id }}" {{ (string) old('visit_id', $invoice->visit_id) === (string) $visit->id ? 'selected' : '' }}>{{ $visit->chief_complaint ?: __('invoices.visit_fallback_label') }} — {{ optional($visit->patient)->full_name ?? __('common.em_dash') }}</option>
                            @endforeach
                        </select>
                        @error('visit_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="{{ $labelClass }} mb-2">{{ __('invoices.section_line_items') }}</label>
                    <div id="itemsContainer">
                        @foreach($invoice->items as $index => $item)
                            <div class="item-row mb-4 grid grid-cols-1 items-end gap-4 md:grid-cols-12">
                                <div class="md:col-span-3">
                                    <label class="{{ $itemLabelClass }}">{{ __('invoices.label_service') }}</label>
                                    <select name="items[{{ $index }}][service_id]" class="service-select {{ $inputClass }}">
                                        <option value="">{{ __('invoices.service_manual') }}</option>
                                        @foreach($services as $svc)
                                            <option value="{{ $svc->id }}" data-price="{{ $svc->price }}" {{ (string) old('items.'.$index.'.service_id', $item->service_id) === (string) $svc->id ? 'selected' : '' }}>{{ $svc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="{{ $itemLabelClass }}">{{ __('invoices.label_item_name') }}</label>
                                    <input type="text" name="items[{{ $index }}][item_name]" value="{{ old('items.'.$index.'.item_name', $item->item_name) }}" placeholder="{{ __('invoices.placeholder_item_name') }}" class="{{ $inputClass }}" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="{{ $itemLabelClass }}">{{ __('invoices.label_price') }}</label>
                                    <input type="number" step="0.01" name="items[{{ $index }}][price]" value="{{ old('items.'.$index.'.price', $item->price) }}" placeholder="{{ __('invoices.placeholder_price') }}" class="price-input {{ $inputClass }}" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="{{ $itemLabelClass }}">{{ __('invoices.label_quantity') }}</label>
                                    <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" placeholder="{{ __('invoices.placeholder_quantity') }}" class="quantity-input {{ $inputClass }}" required>
                                </div>
                                <div class="flex items-center gap-2 pb-1 md:col-span-2">
                                    <span class="item-total tabular-nums text-sm font-semibold text-slate-700 dark:text-slate-200">{{ number_format($item->total, 2) }}</span>
                                    <button type="button" class="{{ $removeItemBtnClass }}" {{ count($invoice->items) > 1 ? '' : 'style="display: none;"' }}>{{ __('invoices.btn_remove_line') }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="addItem" class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ __('invoices.btn_add_line') }}</button>
                    @error('items')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="{{ $labelClass }}">{{ __('invoices.field_notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="{{ $inputClass }}">{{ old('notes', $invoice->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6 rounded-lg border border-slate-200/90 bg-slate-50/80 px-4 py-3 dark:border-[#374151] dark:bg-[#111827]/60">
                    <p class="m-0 text-base font-semibold text-slate-800 dark:text-slate-100">{{ __('invoices.total_preview') }} <span id="totalAmount" class="tabular-nums text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format($invoice->total, 2) }}</span></p>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-6 dark:border-[#374151]">
                    <a href="{{ route('invoices.show', $invoice) }}" data-no-spa class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('common.cancel') }}</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0c3d66] dark:bg-[#3B82F6]">{{ __('invoices.update_invoice') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $serviceOptionsHtml = '<option value="">'.e(__('invoices.service_manual')).'</option>';
    foreach ($services as $svc) {
        $serviceOptionsHtml .= '<option value="'.e($svc->id).'" data-price="'.e($svc->price).'">'.e($svc->name).'</option>';
    }
@endphp

<script @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
    let itemIndex = {{ count($invoice->items) }};
    const invoiceI18n = (window.AppI18n && window.AppI18n.messages) ? window.AppI18n.messages : {};
    const invFormCls = {
        input: @json($inputClass),
        itemLabel: @json($itemLabelClass),
        removeBtn: @json($removeItemBtnClass),
    };

    function serviceOptionsHtml() {
        return @json($serviceOptionsHtml);
    }

    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'item-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 items-end';
        newRow.innerHTML = `
            <div class="md:col-span-3">
                <label class="${invFormCls.itemLabel}">${invoiceI18n.invoiceJsService ?? 'Service'}</label>
                <select name="items[${itemIndex}][service_id]" class="service-select ${invFormCls.input}">
                    ${serviceOptionsHtml()}
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="${invFormCls.itemLabel}">${invoiceI18n.invoiceJsItemName ?? ''}</label>
                <input type="text" name="items[${itemIndex}][item_name]" placeholder="${invoiceI18n.invoiceJsItemNamePlaceholder ?? ''}" class="${invFormCls.input}" required>
            </div>
            <div class="md:col-span-2">
                <label class="${invFormCls.itemLabel}">${invoiceI18n.invoiceJsPrice ?? ''}</label>
                <input type="number" step="0.01" name="items[${itemIndex}][price]" placeholder="${invoiceI18n.invoiceJsPricePlaceholder ?? ''}" class="price-input ${invFormCls.input}" required>
            </div>
            <div class="md:col-span-2">
                <label class="${invFormCls.itemLabel}">${invoiceI18n.invoiceJsQty ?? ''}</label>
                <input type="number" min="1" name="items[${itemIndex}][quantity]" placeholder="${invoiceI18n.invoiceJsQtyPlaceholder ?? ''}" class="quantity-input ${invFormCls.input}" required>
            </div>
            <div class="md:col-span-2 flex items-center gap-2 pb-1">
                <span class="item-total tabular-nums text-sm font-semibold text-slate-700 dark:text-slate-200">0.00</span>
                <button type="button" class="${invFormCls.removeBtn}">${invoiceI18n.invoiceJsRemove ?? ''}</button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
        bindServiceSelect(newRow.querySelector('.service-select'));
        updateTotals();
    });

    function bindServiceSelect(selectEl) {
        if (!selectEl) return;
        selectEl.addEventListener('change', function() {
            const opt = selectEl.options[selectEl.selectedIndex];
            const row = selectEl.closest('.item-row');
            const priceInput = row.querySelector('.price-input');
            const nameInput = row.querySelector('input[name*="[item_name]"]');
            if (opt && opt.value && opt.dataset.price !== undefined) {
                priceInput.value = opt.dataset.price;
                if (nameInput && !nameInput.value) {
                    nameInput.value = opt.text;
                }
                updateRowTotal(row);
                updateTotals();
            }
        });
    }

    document.querySelectorAll('.service-select').forEach(bindServiceSelect);

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
            updateTotals();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('price-input') || e.target.classList.contains('quantity-input')) {
            updateRowTotal(e.target.closest('.item-row'));
            updateTotals();
        }
    });

    function updateRowTotal(row) {
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
        const total = price * quantity;
        row.querySelector('.item-total').textContent = total.toFixed(2);
    }

    function updateTotals() {
        const totals = document.querySelectorAll('.item-total');
        let grandTotal = 0;
        totals.forEach(total => {
            grandTotal += parseFloat(total.textContent) || 0;
        });
        document.getElementById('totalAmount').textContent = grandTotal.toFixed(2);
    }

    updateTotals();
</script>
