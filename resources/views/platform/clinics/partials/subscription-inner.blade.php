@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div class="mx-auto max-w-5xl" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    @include('platform.partials.nav', ['active' => 'clinics'])

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-[24px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.subscription') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ $clinic->name }} · #{{ $clinic->id }}</p>
        </div>
        <a href="{{ route('platform.clinics.index') }}" data-spa class="inline-flex items-center gap-1 text-sm font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81] dark:text-[#5EEAD4]">
            ← {{ __('platform.clinics_title') }}
        </a>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 dark:border-[#374151] dark:bg-[#1F2937]">
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.subscription_status_snapshot') }}</p>
            <p class="mt-2 mb-0 flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $subscriptionTierBadgeClass }}">{{ $subscriptionTierLabel }}</span>
                @if($clinic->subscription_expires_at)
                    <span class="text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('platform.col_expires') }}: {{ $clinic->subscription_expires_at->format('d/m/Y H:i') }}</span>
                @endif
            </p>
        </div>
        <p class="m-0 text-sm font-semibold text-slate-700 dark:text-[#E5E7EB]">{{ $stripeStatusLabel }}</p>
    </div>

    <div class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-5 sm:grid-cols-2 lg:grid-cols-4 dark:border-[#374151] dark:from-[#111827] dark:to-[#1F2937]">
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.col_owner') }}</p>
            <p class="mt-1 mb-0 text-sm font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ $clinic->owner?->name ?? __('common.em_dash') }}</p>
            <p class="mt-0.5 mb-0 text-xs text-slate-600 dark:text-[#9CA3AF]">{{ $clinic->owner?->email ?? __('common.em_dash') }}</p>
        </div>
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.registered_at') }}</p>
            <p class="mt-1 mb-0 text-sm font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ $clinic->created_at?->format('d/m/Y H:i') ?? __('common.em_dash') }}</p>
        </div>
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.col_plan') }}</p>
            <p class="mt-1 mb-0 text-sm font-semibold text-slate-800 dark:text-[#F3F4F6]">{{ $clinic->plan?->name ?? __('platform.no_plan') }}</p>
        </div>
        <div>
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.col_total_paid') }}</p>
            <p class="mt-1 mb-0 text-sm font-semibold text-[#0F4C81] dark:text-[#93C5FD]">{{ number_format((float) ($clinic->total_paid ?? 0), 2) }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.clinic_settings') }}</p>
            <form method="POST" action="{{ route('platform.clinics.update', $clinic) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="is_active" :value="__('common.status')" />
                    <select id="is_active" name="is_active" class="mt-2 block w-full rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        <option value="1" @selected(old('is_active', $clinic->is_active ? '1' : '0') == '1')>{{ __('platform.status_active') }}</option>
                        <option value="0" @selected(old('is_active', $clinic->is_active ? '1' : '0') == '0')>{{ __('platform.status_suspended') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="subscription_status" :value="__('platform.col_subscription')" />
                    <select id="subscription_status" name="subscription_status" class="mt-2 block w-full rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        <option value="active" @selected(old('subscription_status', $clinic->subscription_status) === 'active')>{{ __('platform.status_active') }}</option>
                        <option value="expired" @selected(old('subscription_status', $clinic->subscription_status) === 'expired')>{{ __('platform.status_expired') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('subscription_status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="subscription_expires_at" :value="__('platform.col_expires')" />
                    <x-text-input id="subscription_expires_at" class="block mt-2 w-full" type="datetime-local" name="subscription_expires_at" :value="old('subscription_expires_at', optional($clinic->subscription_expires_at)?->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('subscription_expires_at')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="plan_id" :value="__('platform.col_plan')" />
                    <select id="plan_id" name="plan_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                        <option value="">{{ __('platform.no_plan') }}</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) old('plan_id', $clinic->plan_id) === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
                </div>

                <button type="submit" class="inline-flex rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.save') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-[#374151] dark:bg-[#1F2937]">
            <p class="m-0 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.manual_payment_activate') }}</p>
            <form method="POST" action="{{ route('platform.clinics.record-payment', $clinic) }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <x-input-label for="amount" :value="__('platform.col_amount')" />
                    <x-text-input id="amount" class="mt-2 block w-full" type="number" step="0.01" min="0.01" name="amount" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="paid_at" :value="__('platform.paid_at_optional')" />
                    <x-text-input id="paid_at" class="mt-2 block w-full" type="datetime-local" name="paid_at" :value="old('paid_at')" />
                    <x-input-error :messages="$errors->get('paid_at')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="payment_subscription_expires_at" :value="__('platform.expires_after_payment_optional')" />
                    <x-text-input id="payment_subscription_expires_at" class="mt-2 block w-full" type="datetime-local" name="subscription_expires_at" :value="old('subscription_expires_at', optional($clinic->subscription_expires_at)?->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('subscription_expires_at')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="notes" :value="__('platform.notes')" />
                    <textarea id="notes" name="notes" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
                <button type="submit" class="inline-flex rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">{{ __('platform.record_payment') }}</button>
            </form>
        </div>
    </div>

    <div class="mt-8">
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <h2 class="text-lg font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.clinic_payment_history') }}</h2>
            <a href="{{ route('platform.clinics.payments.export', array_merge(['clinic' => $clinic], request()->only(['paid_from', 'paid_to']))) }}" class="inline-flex shrink-0 items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-800 no-underline shadow-sm transition hover:bg-slate-50 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#E5E7EB]">
                {{ __('platform.export_payments_csv') }}
            </a>
        </div>

        <form method="GET" action="{{ route('platform.clinics.subscription', $clinic) }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-[#374151] dark:bg-[#1F2937]">
            <div>
                <label for="paid_from" class="block text-xs font-bold text-slate-500 dark:text-[#94A3B8]">{{ __('platform.paid_from') }}</label>
                <input type="date" id="paid_from" name="paid_from" value="{{ $paymentFilters['paid_from'] ?? '' }}" class="mt-1 rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
            </div>
            <div>
                <label for="paid_to" class="block text-xs font-bold text-slate-500 dark:text-[#94A3B8]">{{ __('platform.paid_to') }}</label>
                <input type="date" id="paid_to" name="paid_to" value="{{ $paymentFilters['paid_to'] ?? '' }}" class="mt-1 rounded-lg border-slate-300 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
            </div>
            <button type="submit" class="rounded-lg bg-[#0F4C81] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0c3d66] dark:bg-[#3B82F6]">{{ __('platform.apply_payment_filter') }}</button>
            @if(($paymentFilters['paid_from'] ?? '') !== '' || ($paymentFilters['paid_to'] ?? '') !== '')
                <a href="{{ route('platform.clinics.subscription', $clinic) }}" data-spa class="text-sm font-semibold text-slate-600 no-underline hover:text-[#0F4C81] dark:text-[#9CA3AF]">{{ __('platform.clear_payment_filter') }}</a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-start dark:border-[#374151] dark:bg-[#111827]">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_amount') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_source') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.col_paid_at') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('platform.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPayments as $payment)
                        <tr class="border-b border-slate-100 dark:border-[#374151]">
                            <td class="px-4 py-3 font-medium">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($payment->source === \App\Models\SubscriptionPayment::SOURCE_STRIPE)
                                    <span class="rounded-full bg-[#0F4C81]/10 px-2 py-0.5 text-xs font-semibold text-[#0F4C81] dark:bg-blue-950/40 dark:text-[#93C5FD]">{{ __('platform.source_stripe') }}</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">{{ __('platform.source_manual') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ $payment->notes ?: __('common.em_dash') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('platform.no_clinic_payments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
