@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
@php
    use App\Models\Clinic;
    use App\Models\ClinicSubscription;
    use App\Services\Saas\ClinicBillingPageService;
    use App\Support\PlanDisplay;
    $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $statusLabel = __('saas.status_inactive');
    $badgePositive = ($isActive ?? false) && ($alertTier ?? '') !== 'expired';
    if ($clinic ?? null) {
        if (($alertTier ?? '') === 'expired') {
            $statusLabel = __('saas.status_expired');
            $badgePositive = false;
        } elseif ($isActive ?? false) {
            $statusLabel = __('saas.status_active');
        }
    }
    $rowLabel = ($clinicSubscription ?? null)
        ? match ($clinicSubscription->status) {
            ClinicSubscription::STATUS_ACTIVE => __('saas.clinic_sub_active'),
            ClinicSubscription::STATUS_TRIAL => __('saas.clinic_sub_trial'),
            ClinicSubscription::STATUS_CANCELED => __('saas.clinic_sub_canceled'),
            ClinicSubscription::STATUS_EXPIRED => __('saas.clinic_sub_expired'),
            default => $clinicSubscription->status,
        }
        : __('common.em_dash');
    $features = is_array($plan?->features) ? $plan->features : [];
@endphp

<div id="saas-billing-root" class="max-w-[1200px] mx-auto" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}" data-cancel-url="{{ route('saas.billing.cancel') }}">
    <div class="mb-8">
        <h1 class="text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('saas.page_title') }}</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('saas.page_subtitle') }}</p>
    </div>

    @if (session('success') && request('checkout') !== 'success')
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/35 dark:text-emerald-100" data-flash-auto-dismiss="9000" role="status">
            <p class="m-0">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 dark:border-rose-900/40 dark:bg-rose-950/35 dark:text-rose-100" role="alert">
            <p class="m-0 font-bold">{{ __('saas.session_error_title') }}</p>
            <p class="mt-1 mb-3 m-0">{{ session('error') }}</p>
            <a href="{{ route('saas.pricing') }}" data-no-spa class="inline-flex rounded-lg bg-rose-700 px-4 py-2 text-sm font-bold text-white hover:bg-rose-800">{{ __('saas.btn_renew_now') }}</a>
        </div>
    @endif

    @if (request('checkout') === 'success')
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/35 dark:text-blue-100" data-flash-auto-dismiss="9000" role="status">
            <p class="m-0">{{ __('saas.checkout_success_flash') }}</p>
        </div>
    @endif

    @if ($clinic && ($alertTier ?? '') === 'expired')
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 dark:border-rose-900/50 dark:bg-rose-950/35">
            <p class="m-0 font-bold text-rose-900 dark:text-rose-200">{{ __('saas.banner_expired_title') }}</p>
            <p class="mt-1 mb-3 text-sm text-rose-800 dark:text-rose-300 m-0">{{ __('saas.banner_expired_body') }}</p>
            <a href="{{ route('saas.pricing') }}" data-no-spa class="inline-flex rounded-lg bg-rose-700 px-4 py-2 text-sm font-bold text-white hover:bg-rose-800">{{ __('saas.btn_renew_now') }}</a>
        </div>
    @elseif ($clinic && ($alertTier ?? '') === 'warning')
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-900/50 dark:bg-amber-950/35">
            <p class="m-0 font-bold text-amber-900 dark:text-amber-200">{{ __('saas.banner_warning_title', ['days' => $daysRemaining ?? 0]) }}</p>
            <p class="mt-1 mb-3 text-sm text-amber-800 dark:text-amber-300 m-0">{{ __('saas.banner_warning_body') }}</p>
            <a href="{{ route('saas.pricing') }}" data-no-spa class="inline-flex rounded-lg bg-amber-700 px-4 py-2 text-sm font-bold text-white hover:bg-amber-800">{{ __('saas.btn_renew_now') }}</a>
        </div>
    @elseif ($trial['active'] ?? false)
        <div class="mb-6 rounded-xl border border-sky-200 bg-sky-50 px-5 py-4 dark:border-sky-900/50 dark:bg-sky-950/35">
            <p class="m-0 font-bold text-sky-900 dark:text-sky-200">{{ __('saas.trial_title') }}</p>
            <p class="mt-1 mb-0 text-sm text-sky-800 dark:text-sky-300">{{ $trial['label'] ?? '' }}</p>
        </div>
    @elseif ($clinic && ($isActive ?? false))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/80 px-5 py-3 dark:border-emerald-900/40 dark:bg-emerald-950/25">
            <p class="m-0 text-sm font-bold text-emerald-900 dark:text-emerald-200">{{ __('saas.banner_active_title') }}</p>
        </div>
    @endif

    <p id="saas-billing-action-message" class="mb-4 hidden rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 dark:border-[#374151] dark:bg-[#111827] dark:text-[#E5E7EB]"></p>

    @if (! $clinic)
        <p class="text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('saas.no_clinic') }}</p>
    @else
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-8">
                {{-- Subscription summary --}}
                <section class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                    <div class="border-b border-slate-100 bg-slate-50/90 px-5 py-3 dark:border-[#374151] dark:bg-[#111827]">
                        <h2 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_subscription') }}</h2>
                    </div>
                    <dl class="grid gap-0 sm:grid-cols-2 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151] sm:border-e">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_clinic') }}</dt>
                            <dd class="font-semibold text-slate-900 dark:text-[#F3F4F6]">{{ $clinic->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151]">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_plan') }}</dt>
                            <dd id="saas-billing-plan-name" class="font-semibold text-[#0F4C81] dark:text-[#93C5FD]">{{ PlanDisplay::localizedName($plan, $clinic->subscription_plan ?: null) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151] sm:border-e">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_subscription_status') }}</dt>
                            <dd>
                                <span id="saas-billing-status-badge" @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-bold',
                                    'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/45 dark:text-emerald-200' => $badgePositive,
                                    'bg-rose-100 text-rose-900 dark:bg-rose-950/45 dark:text-rose-200' => ! $badgePositive,
                                ])>{{ $statusLabel }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151]">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_subscription_row') }}</dt>
                            <dd id="saas-billing-clinic-sub-status" class="font-semibold">{{ $rowLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151] sm:border-e">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_billing_cycle') }}</dt>
                            <dd class="font-semibold">{{ $billingCycleLabel ?? __('common.em_dash') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151]">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_amount') }}</dt>
                            <dd class="font-semibold tabular-nums">{{ $subscriptionAmount ?? __('common.em_dash') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151] sm:border-e">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_started') }}</dt>
                            <dd>{{ $subscriptionStartsAt?->format('d/m/Y') ?? __('common.em_dash') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 px-5 py-3 dark:border-[#374151]">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_expires') }}</dt>
                            <dd id="saas-billing-expires" class="font-semibold">{{ optional($clinic->subscription_expires_at)->format('d/m/Y H:i') ?? __('common.em_dash') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 px-5 py-3 sm:col-span-2">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_days_remaining') }}</dt>
                            <dd id="saas-billing-days" class="font-bold tabular-nums">{{ $daysRemaining !== null ? $daysRemaining : __('common.em_dash') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-slate-100 px-5 py-3 sm:col-span-2 dark:border-[#374151]">
                            <dt class="text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.label_stripe') }}</dt>
                            <dd id="saas-billing-stripe" class="text-sm font-semibold">{{ ClinicBillingPageService::stripeSubscriptionStatusLabel($subscription?->stripe_status) }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Plan features --}}
                @if ($plan && count($features))
                    <section class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                        <h2 class="m-0 mb-3 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_plan_features') }}</h2>
                        <ul class="m-0 list-none space-y-2 text-sm text-slate-700 dark:text-[#E5E7EB]">
                            @foreach ($features as $feature)
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#1F7A8C]" aria-hidden="true"></span>
                                    <span>{{ PlanDisplay::localizedFeature($feature) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Stripe invoices --}}
                @if (! empty($stripeInvoices))
                    <section class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                        <div class="border-b border-slate-100 bg-slate-50/90 px-5 py-3 dark:border-[#374151] dark:bg-[#111827]">
                            <h2 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_stripe_invoices') }}</h2>
                            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.section_stripe_invoices_hint') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[520px] text-sm">
                                <thead>
                                    <tr class="border-b bg-slate-50 dark:bg-[#111827] dark:border-[#374151]">
                                        <th class="px-4 py-2 text-start font-bold">{{ __('common.date') }}</th>
                                        <th class="px-4 py-2 text-start font-bold">{{ __('saas.th_invoice') }}</th>
                                        <th class="px-4 py-2 text-end font-bold">{{ __('saas.th_amount') }}</th>
                                        <th class="px-4 py-2 text-start font-bold">{{ __('common.status') }}</th>
                                        <th class="px-4 py-2 text-start font-bold">{{ __('saas.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stripeInvoices as $stripeInvoice)
                                        <tr class="border-b border-slate-50 dark:border-[#374151]">
                                            <td class="px-4 py-2">{{ $stripeInvoice['date']->format('d/m/Y') }}</td>
                                            <td class="px-4 py-2 font-mono text-xs">{{ $stripeInvoice['number'] }}</td>
                                            <td class="px-4 py-2 text-end tabular-nums font-bold">{{ number_format($stripeInvoice['amount'], 2) }} {{ $stripeInvoice['currency'] }}</td>
                                            <td class="px-4 py-2">
                                                @php
                                                    $stripeStatusLabel = match ($stripeInvoice['status']) {
                                                        'paid' => __('saas.stripe_invoice_status_paid'),
                                                        'open' => __('saas.stripe_invoice_status_open'),
                                                        'draft' => __('saas.stripe_invoice_status_draft'),
                                                        'void' => __('saas.stripe_invoice_status_void'),
                                                        'uncollectible' => __('saas.stripe_invoice_status_uncollectible'),
                                                        default => $stripeInvoice['status'],
                                                    };
                                                @endphp
                                                {{ $stripeStatusLabel }}
                                            </td>
                                            <td class="px-4 py-2">
                                                @if ($stripeInvoice['hosted_url'])
                                                    <a href="{{ $stripeInvoice['hosted_url'] }}" target="_blank" rel="noopener" class="text-[#0F4C81] font-bold text-xs no-underline hover:underline dark:text-[#93C5FD]">{{ __('saas.btn_view_invoice') }}</a>
                                                @elseif ($stripeInvoice['pdf_url'])
                                                    <a href="{{ $stripeInvoice['pdf_url'] }}" target="_blank" rel="noopener" class="text-[#0F4C81] font-bold text-xs no-underline hover:underline dark:text-[#93C5FD]">{{ __('common.pdf') }}</a>
                                                @else
                                                    {{ __('common.em_dash') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (($canManageBilling ?? false) && ($stripeWebhookEvents ?? collect())->isNotEmpty())
                    <section class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                        <div class="border-b border-slate-100 bg-slate-50/90 px-5 py-3 dark:border-[#374151] dark:bg-[#111827]">
                            <h2 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_stripe_webhooks') }}</h2>
                            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.section_stripe_webhooks_hint') }}</p>
                        </div>
                        <ul class="m-0 list-none divide-y divide-slate-100 dark:divide-[#374151] text-xs">
                            @foreach ($stripeWebhookEvents as $event)
                                <li class="px-5 py-2.5 flex flex-wrap justify-between gap-2">
                                    <span class="font-mono text-slate-700 dark:text-slate-300">{{ $event->event_type }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ $event->received_at?->format('d/m/Y H:i') }}</span>
                                    @if ($event->summary)
                                        <span class="w-full text-slate-600 dark:text-slate-400">{{ $event->summary }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Payment history --}}
                <section class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                    <div class="border-b border-slate-100 bg-slate-50/90 px-5 py-3 dark:border-[#374151] dark:bg-[#111827]">
                        <h2 class="m-0 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_payments') }}</h2>
                        <p class="m-0 mt-1 text-xs text-slate-500 dark:text-[#9CA3AF]">{{ __('saas.section_payments_hint') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead>
                                <tr class="border-b bg-slate-50 dark:bg-[#111827] dark:border-[#374151]">
                                    <th class="px-4 py-2 text-start font-bold">{{ __('common.date') }}</th>
                                    <th class="px-4 py-2 text-end font-bold">{{ __('saas.th_amount') }}</th>
                                    <th class="px-4 py-2 text-start font-bold">{{ __('saas.th_source') }}</th>
                                    <th class="px-4 py-2 text-start font-bold">{{ __('common.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr class="border-b border-slate-50 dark:border-[#374151]">
                                        <td class="px-4 py-2">{{ $payment->paid_at?->format('d/m/Y H:i') ?? __('common.em_dash') }}</td>
                                        <td class="px-4 py-2 text-end tabular-nums font-bold">{{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="px-4 py-2">{{ ClinicBillingPageService::paymentSourceLabel($payment->source) }}</td>
                                        <td class="px-4 py-2">{{ ClinicBillingPageService::paymentStatusLabel($payment->status) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-slate-500">{{ __('saas.empty_payments') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                {{-- Usage --}}
                @if ($usage)
                    <section class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                        <h2 class="m-0 mb-4 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_usage') }}</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">
                                    <span>{{ __('saas.usage_patients') }}</span>
                                    <span class="tabular-nums">{{ $usage['patients'] }}@if(! $usage['patients_unlimited']) / {{ $usage['max_patients'] }}@else ∞@endif</span>
                                </div>
                                @if (! $usage['patients_unlimited'] && $usage['max_patients'] > 0)
                                    @php $pct = min(100, round(($usage['patients'] / $usage['max_patients']) * 100)); @endphp
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-[#111827]"><div class="h-2 rounded-full bg-[#0F4C81] dark:bg-blue-500" style="width: {{ $pct }}%"></div></div>
                                @endif
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">
                                    <span>{{ __('saas.usage_users') }}</span>
                                    <span class="tabular-nums">{{ $usage['users'] }}@if(! $usage['users_unlimited']) / {{ $usage['max_users'] }}@else ∞@endif</span>
                                </div>
                                @if (! $usage['users_unlimited'] && $usage['max_users'] > 0)
                                    @php $pctU = min(100, round(($usage['users'] / $usage['max_users']) * 100)); @endphp
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-[#111827]"><div class="h-2 rounded-full bg-teal-600" style="width: {{ $pctU }}%"></div></div>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Actions --}}
                <section class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-md dark:border-[#374151] dark:bg-[#1F2937]">
                    <h2 class="m-0 mb-4 text-sm font-bold text-slate-800 dark:text-[#F3F4F6]">{{ __('saas.section_actions') }}</h2>
                    @if (! ($canManageBilling ?? false))
                        <p class="mb-4 text-xs text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('saas.owner_only_hint', ['owner' => $clinic->owner?->name ?? $clinic->owner?->email ?? '—']) }}</p>
                    @elseif (($canManageBilling ?? false) && (int) auth()->id() !== (int) $clinic->owner_id)
                        <p class="mb-4 text-xs text-emerald-700 dark:text-emerald-300 m-0">{{ __('saas.billing_manager_hint') }}</p>
                    @endif
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('saas.billing.pdf') }}" data-no-spa class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 no-underline hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#E5E7EB]">
                            {{ __('saas.btn_download_pdf') }}
                        </a>
                        <a href="{{ route('saas.pricing') }}" data-no-spa class="inline-flex items-center justify-center rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white no-underline hover:bg-[#0c3d6b] dark:bg-blue-600">
                            {{ __('saas.btn_upgrade_plan') }}
                        </a>
                        @if ($canOwnerBilling && $clinic->hasStripeId())
                            <a href="{{ route('saas.billing.portal') }}" data-no-spa class="inline-flex items-center justify-center rounded-lg border border-[#0F4C81] px-4 py-2.5 text-sm font-bold text-[#0F4C81] no-underline dark:text-[#93C5FD]">
                                {{ __('saas.btn_stripe_portal') }}
                            </a>
                        @endif
                        @php $waUrl = 'https://wa.me/'.($whatsappPhone ?? '972597360027').'?text='.rawurlencode(__('saas.support_whatsapp_message', ['clinic' => $clinic->name])); @endphp
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg border border-emerald-600 px-4 py-2.5 text-sm font-bold text-emerald-800 no-underline dark:text-emerald-300">
                            {{ __('saas.btn_support_whatsapp') }}
                        </a>
                        @if ($canOwnerBilling)
                            <button type="button" id="saas-billing-cancel-btn" class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-900 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-100">
                                {{ __('saas.btn_cancel_subscription') }}
                            </button>
                        @endif
                    </div>
                    <p class="mt-4 text-xs text-slate-500 dark:text-[#9CA3AF] m-0">{{ __('saas.footer_stripe_cancel_hint') }}</p>
                </section>
            </div>
        </div>
    @endif
</div>
