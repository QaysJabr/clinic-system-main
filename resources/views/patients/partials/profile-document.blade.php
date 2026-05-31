@section('content')
    @php
        $clinicTitle = $clinic->clinic_name ?: config('app.name');
        $clinicContact = collect([$clinic->clinic_phone, $clinic->clinic_email])->filter()->implode(' Â· ');
        $t = $statementTotals;
        $invStatus = fn ($s) => match ($s) {
            'paid' => __('patients.invoice_status_paid'),
            'partial' => __('patients.invoice_status_partial'),
            default => __('patients.invoice_status_unpaid'),
        };
    @endphp

    <div class="border-b border-gray-200 pb-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-0 flex-1 text-start">
                <p class="text-xs font-bold text-gray-400 m-0">{{ __('patients.print_profile_tag') }}</p>
                <h1 class="mt-2 text-2xl font-bold text-[#0F4C81] m-0">{{ $clinicTitle }}</h1>
                @if($clinicContact !== '')
                    <p class="mt-1 text-sm text-gray-600 m-0">{{ $clinicContact }}</p>
                @endif
                <p class="mt-2 text-sm font-semibold text-gray-800 m-0">{{ $patient->full_name }} â€” {{ $patient->file_number }}</p>
                <p class="mt-1 text-xs text-gray-500 m-0">{{ __('patients.period_label', ['range' => $reportMeta['date_range_label'] ?? __('patients.em_dash')]) }}</p>
                <p class="mt-1 text-xs text-gray-500 m-0">{{ __('patients.print_date', ['datetime' => $generatedAt->format('d/m/Y H:i')]) }}</p>
                @if(! empty($reportMeta['filter_lines']))
                    @foreach($reportMeta['filter_lines'] as $line)
                        <p class="mt-0.5 text-xs text-gray-500 m-0">{{ $line }}</p>
                    @endforeach
                @endif
            </div>
            @php
                $clinicLogoSrc = ($exportRender ?? false)
                    ? \App\Support\ClinicDocumentLogo::src($clinic, true)
                    : $clinic->logoPublicUrl();
            @endphp
            @if($clinicLogoSrc)
                <div class="shrink-0">
                    <img src="{{ $clinicLogoSrc }}" alt="" class="h-16 max-w-[180px] object-contain">
                </div>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6 text-start">
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="m-0 text-xs text-slate-600">{{ __('patients.summary_visit_count') }}</p>
            <p class="mt-1 mb-0 text-base font-bold tabular-nums">{{ $summary['visit_count'] }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="m-0 text-xs text-slate-600">{{ __('patients.summary_invoice_count') }}</p>
            <p class="mt-1 mb-0 text-base font-bold tabular-nums">{{ $summary['invoice_count'] }}</p>
        </div>
        <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 p-3">
            <p class="m-0 text-xs text-emerald-900">{{ __('patients.summary_invoice_total') }}</p>
            <p class="mt-1 mb-0 text-base font-bold tabular-nums">{{ number_format($summary['invoice_total'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-lg border border-teal-100 bg-teal-50/50 p-3">
            <p class="m-0 text-xs text-teal-900">{{ __('patients.summary_paid') }}</p>
            <p class="mt-1 mb-0 text-base font-bold tabular-nums">{{ number_format($summary['paid_total'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-lg border border-rose-100 bg-rose-50/50 p-3">
            <p class="m-0 text-xs text-rose-900">{{ __('patients.summary_balance') }}</p>
            <p class="mt-1 mb-0 text-base font-bold tabular-nums">{{ number_format($summary['balance'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="m-0 text-xs text-slate-600">{{ __('patients.summary_last_visit') }}</p>
            <p class="mt-1 mb-0 text-sm font-bold">{{ $summary['last_visit_date'] ? \Illuminate\Support\Carbon::parse($summary['last_visit_date'])->format('d/m/Y') : __('patients.em_dash') }}</p>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 p-5 text-start">
        <h2 class="mb-3 text-base font-bold m-0">{{ __('patients.patient_demographics') }}</h2>
        <dl class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_name') }}</dt><dd class="font-semibold m-0">{{ $patient->full_name }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_file_number') }}</dt><dd class="font-medium m-0">{{ $patient->file_number }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_phone') }}</dt><dd class="font-medium m-0">{{ $patient->phone ?: __('patients.em_dash') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_date_of_birth') }}</dt><dd class="font-medium m-0">@safeDate($patient->date_of_birth)</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_gender') }}</dt><dd class="font-medium m-0">{{ match ($patient->gender) { 'male', 'm' => __('patients.gender_male'), 'female', 'f' => __('patients.gender_female'), default => $patient->gender ? (string) $patient->gender : __('patients.em_dash') } }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_address') }}</dt><dd class="font-medium m-0">{{ $patient->address ?: __('patients.em_dash') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 m-0">{{ __('patients.field_registration_date') }}</dt><dd class="font-medium m-0">@safeDate($patient->created_at)</dd></div>
        </dl>
    </div>

    @if($canViewClinical && $visits->isNotEmpty())
        <h2 class="mb-2 text-base font-bold text-start m-0">{{ __('patients.section_visits') }}</h2>
        <div class="mb-6 overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600">
                <tr>
                    <th class="px-3 py-2">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-2">{{ __('patients.label_doctor') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_chief_complaint') }}</th>
                    <th class="px-3 py-2">{{ __('patients.pdf_col_diagnosis') }}</th>
                    <th class="px-3 py-2">{{ __('patients.field_status') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($visits as $v)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">@safeDate($v->visit_date)</td>
                        <td class="px-3 py-2">{{ $v->doctor->full_name ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit((string) ($v->chief_complaint ?? ''), 60) }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit((string) ($v->diagnosis ?? ''), 60) }}</td>
                        <td class="px-3 py-2">{{ $v->statusLabel() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($canViewClinical && $appointments->isNotEmpty())
        <h2 class="mb-2 text-base font-bold text-start m-0">{{ __('patients.section_appointments') }}</h2>
        <div class="mb-6 overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600">
                <tr>
                    <th class="px-3 py-2">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-2">{{ __('patients.pdf_col_time') }}</th>
                    <th class="px-3 py-2">{{ __('patients.label_doctor') }}</th>
                    <th class="px-3 py-2">{{ __('patients.field_status') }}</th>
                    <th class="px-3 py-2">{{ __('patients.field_notes') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($appointments as $a)
                    <tr>
                        <td class="px-3 py-2">@safeDate($a->appointment_date)</td>
                        <td class="px-3 py-2">{{ $a->start_time ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-2">{{ $a->doctor->full_name ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-2">{{ match ($a->status) { 'scheduled' => __('patients.appointment_status_scheduled'), 'completed' => __('patients.appointment_status_completed'), 'cancelled' => __('patients.appointment_status_cancelled'), default => $a->status } }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit((string) ($a->notes ?? ''), 50) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($invoices->isNotEmpty())
        <h2 class="mb-2 text-base font-bold text-start m-0">{{ __('patients.section_invoices') }}</h2>
        <div class="mb-6 overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600">
                <tr>
                    <th class="px-3 py-2">{{ __('patients.col_invoice_number') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-2">{{ __('patients.label_doctor') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_total') }}</th>
                    <th class="px-3 py-2">{{ __('patients.summary_paid') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_remaining') }}</th>
                    <th class="px-3 py-2">{{ __('patients.field_status') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($invoices as $inv)
                    @php $rem = (float) $inv->total - (float) $inv->paid; @endphp
                    <tr>
                        <td class="px-3 py-2 font-semibold">{{ $inv->invoice_number }}</td>
                        <td class="px-3 py-2">@safeDate($inv->created_at)</td>
                        <td class="px-3 py-2">{{ $inv->treatingDoctor->full_name ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ number_format((float) $inv->total, 2) }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ number_format((float) $inv->paid, 2) }}</td>
                        <td class="px-3 py-2 tabular-nums">{{ number_format($rem, 2) }}</td>
                        <td class="px-3 py-2">{{ $invStatus($inv->status) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($payments->isNotEmpty())
        <h2 class="mb-2 text-base font-bold text-start m-0">{{ __('patients.section_payments') }}</h2>
        <div class="mb-6 overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full text-start text-sm">
                <thead class="bg-slate-50 text-xs font-bold text-slate-600">
                <tr>
                    <th class="px-3 py-2">{{ __('patients.col_date') }}</th>
                    <th class="px-3 py-2">{{ __('patients.pdf_col_invoice') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_amount') }}</th>
                    <th class="px-3 py-2">{{ __('patients.col_payment_method') }}</th>
                    <th class="px-3 py-2">{{ __('patients.field_notes') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($payments as $pay)
                    <tr>
                        <td class="px-3 py-2">@safeDate($pay->payment_date)</td>
                        <td class="px-3 py-2">{{ $pay->invoice->invoice_number ?? __('patients.em_dash') }}</td>
                        <td class="px-3 py-2 tabular-nums font-semibold">{{ number_format((float) $pay->amount, 2) }}</td>
                        <td class="px-3 py-2">{{ $pay->payment_method ?: __('patients.em_dash') }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit((string) ($pay->notes ?? ''), 40) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h2 class="mb-2 text-base font-bold text-start m-0">{{ __('patients.ledger_section_filtered') }}</h2>
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3 text-start">
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="m-0 text-xs text-slate-600">{{ __('patients.statement_total_invoiced_debit') }}</p>
            <p class="mt-1 mb-0 font-bold tabular-nums">{{ number_format($t['total_invoiced'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-lg border border-teal-200 bg-teal-50/40 p-3">
            <p class="m-0 text-xs text-teal-900">{{ __('patients.statement_total_paid_credit') }}</p>
            <p class="mt-1 mb-0 font-bold tabular-nums">{{ number_format($t['total_paid'], 2) }} {{ $cur }}</p>
        </div>
        <div class="rounded-lg border border-rose-200 bg-rose-50/40 p-3">
            <p class="m-0 text-xs text-rose-900">{{ __('patients.statement_ending_balance') }}</p>
            <p class="mt-1 mb-0 font-bold tabular-nums">{{ number_format($t['ending_balance'], 2) }} {{ $cur }}</p>
        </div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="min-w-full text-start text-sm">
            <thead class="bg-slate-50 text-xs font-bold text-slate-600">
            <tr>
                <th class="px-3 py-2">{{ __('patients.col_date') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_type') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_ref') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_description') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_debit') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_credit') }}</th>
                <th class="px-3 py-2">{{ __('patients.ledger_col_balance') }}</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($ledger as $row)
                <tr>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $row['date'] }}</td>
                    <td class="px-3 py-2">{{ $row['type'] }}</td>
                    <td class="px-3 py-2 tabular-nums">{{ $row['ref'] }}</td>
                    <td class="px-3 py-2">{{ $row['description'] }}</td>
                    <td class="px-3 py-2 tabular-nums text-rose-700">{{ $row['debit'] ?? __('patients.em_dash') }}</td>
                    <td class="px-3 py-2 tabular-nums text-emerald-700">{{ $row['credit'] ?? __('patients.em_dash') }}</td>
                    <td class="px-3 py-2 tabular-nums font-bold">{{ $row['balance'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('patients.empty_ledger_short') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
