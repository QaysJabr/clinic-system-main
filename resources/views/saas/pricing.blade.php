<x-guest-layout
    :wide="true"
    :page-title="__('saas.pricing_title').' — '.config('app.name')"
    :meta-description="__('saas.pricing_subtitle')"
>
    @php
        $canSubscribe = $canManageBilling ?? false;
        $whatsappPhone = (string) config('saas.support.whatsapp', '');
        $dbPlans = collect($plans ?? []);
        $fallbackPlans = collect([
            (object) [
                'name' => __('saas.pricing_basic'),
                'monthly' => __('saas.pricing_fallback_basic_monthly'),
                'yearly' => __('saas.pricing_fallback_basic_yearly'),
                'features' => [__('saas.pricing_fallback_feature_1'), __('saas.pricing_fallback_feature_2')],
            ],
            (object) [
                'name' => __('saas.pricing_pro'),
                'monthly' => __('saas.pricing_fallback_pro_monthly'),
                'yearly' => __('saas.pricing_fallback_pro_yearly'),
                'features' => [__('saas.pricing_fallback_feature_1'), __('saas.pricing_fallback_feature_2'), __('saas.pricing_fallback_feature_3')],
            ],
            (object) [
                'name' => __('saas.pricing_premium'),
                'monthly' => __('saas.pricing_fallback_premium_monthly'),
                'yearly' => __('saas.pricing_fallback_premium_yearly'),
                'features' => [__('saas.pricing_fallback_feature_1'), __('saas.pricing_fallback_feature_2'), __('saas.pricing_fallback_feature_3')],
            ],
        ]);
        $renderPlans = $dbPlans->isNotEmpty() ? $dbPlans : $fallbackPlans;
    @endphp

    @php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
    <div id="saas-pricing-root" class="w-full" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}" data-default-cycle="monthly" data-billing-url="{{ url('/billing') }}">
        <div class="mb-6 border-b border-slate-100 pb-6 dark:border-[#374151]">
            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('saas.pricing_kicker') }}</p>
            <h1 class="text-xl font-bold text-[#1F2937] dark:text-[#F3F4F6]">{{ __('saas.pricing_title') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-[#9CA3AF]">{{ __('saas.pricing_subtitle') }}</p>
            <ul class="mt-3 space-y-1.5 text-sm text-slate-700 dark:text-slate-300">
                <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-600" aria-hidden="true"></span>{{ __('saas.pricing_value_1') }}</li>
                <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-600" aria-hidden="true"></span>{{ __('saas.pricing_value_2') }}</li>
                <li class="flex items-start gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-600" aria-hidden="true"></span>{{ __('saas.pricing_value_3') }}</li>
            </ul>
            <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_fairness_note') }}</p>
        </div>

        {{-- z-index: شارة «الأكثر استخداماً» (-top-3) قد ترسم فوق صف الأزرار وتسرق النقرات --}}
        <div class="relative z-20 mb-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('saas.pricing_cycle') }}</span>
            <button type="button" data-cycle-set="monthly" class="rounded-lg border border-[#0F4C81] bg-[#0F4C81] px-3 py-1.5 text-xs font-bold text-white ring-2 ring-[#0F4C81] dark:bg-[#3B82F6] dark:ring-[#3B82F6]">{{ __('common.monthly') }}</button>
            <button type="button" data-cycle-set="yearly" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-800 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">{{ __('common.yearly') }}</button>
        </div>

        <div id="saas-pricing-message" class="mb-4 hidden rounded-lg border px-4 py-3 text-sm" role="status"></div>

        @if ($allowPromotionCodes ?? false)
            <div class="mb-6 max-w-md">
                <label for="saas-promotion-code" class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('saas.promotion_code_label') }}</label>
                <input type="text" id="saas-promotion-code" name="promotion_code" autocomplete="off" placeholder="{{ __('saas.promotion_code_placeholder') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-white" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 m-0">{{ __('saas.promotion_code_hint') }}</p>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($renderPlans as $plan)
                @php
                    $features = is_array($plan->features) ? $plan->features : [];
                    $hasStripeMonthly = ! empty($plan->stripe_price_id ?? null);
                    $hasStripeYearly = ! empty($plan->stripe_price_yearly_id) || $hasStripeMonthly;
                    $planSlug = strtolower((string) ($plan->slug ?? ''));
                    $isPopular = $planSlug === 'pro' || str_contains(strtolower((string) $plan->name), 'pro');
                    $isPremium = $planSlug === 'premium' || str_contains(strtolower((string) $plan->name), 'premium') || str_contains(strtolower((string) $plan->name), 'ممي');
                    $planFit = \App\Support\PlanDisplay::planFitLabel($plan);
                    $isCurrent = isset($currentPlanId) && isset($plan->id) && (int) $currentPlanId === (int) $plan->id;
                    $monthlyText = method_exists($plan, 'displayMonthly') ? $plan->displayMonthly() : ($plan->monthly ?? __('saas.pricing_fallback_pro_monthly'));
                    $yearlyText = method_exists($plan, 'displayYearly') ? $plan->displayYearly() : ($plan->yearly ?? __('saas.pricing_fallback_pro_yearly'));
                    $waMessage = str_replace('[PLAN_NAME]', \App\Support\PlanDisplay::localizedName($plan), __('saas.pricing_whatsapp_message'));
                    $waUrl = 'https://wa.me/'.$whatsappPhone.'?text='.rawurlencode($waMessage);
                @endphp
                <div class="relative flex flex-col rounded-2xl border border-slate-200 bg-slate-50/50 p-5 shadow-sm dark:border-[#374151] dark:bg-[#111827]/50 {{ $isCurrent ? 'ring-2 ring-emerald-600 dark:ring-emerald-500' : ($isPopular ? 'ring-2 ring-[#0F4C81] dark:ring-[#60A5FA]' : '') }}">
                    @if ($isCurrent)
                        <span class="pointer-events-none absolute -top-3 start-4 rounded-full bg-emerald-600 px-3 py-1 text-[10px] font-bold text-white">{{ __('saas.pricing_current_plan') }}</span>
                    @elseif ($isPopular)
                        <span class="pointer-events-none absolute -top-3 start-4 rounded-full bg-[#0F4C81] px-3 py-1 text-[10px] font-bold text-white dark:bg-[#2563EB]">{{ __('saas.pricing_popular_badge') }}</span>
                    @endif
                    @php
                        $maxPatients = $plan->max_patients ?? null;
                        $maxUsers = $plan->max_users ?? null;
                        $trialDays = (int) ($plan->trial_days ?? 0);
                        $monthlyAmount = (float) ($plan->price_monthly ?? 0);
                        $yearlyAmount = (float) ($plan->price_yearly ?? 0);
                        $yearlySavePct = \App\Support\PlanDisplay::yearlySavingsPercent($monthlyAmount, $yearlyAmount);
                    @endphp
                    <div class="mb-3">
                        <p class="m-0 text-lg font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ \App\Support\PlanDisplay::localizedName($plan) }}</p>
                        @if ($planFit)
                            <p class="m-0 mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $planFit }}</p>
                        @endif
                        <ul class="mt-2 space-y-1 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-1.5">
                                <span class="h-1 w-1 rounded-full bg-[#1F7A8C]" aria-hidden="true"></span>
                                {{ \App\Support\PlanDisplay::patientsLimitLabel($maxPatients) }}
                            </li>
                            <li class="flex items-center gap-1.5">
                                <span class="h-1 w-1 rounded-full bg-[#1F7A8C]" aria-hidden="true"></span>
                                {{ \App\Support\PlanDisplay::usersLimitLabel($maxUsers) }}
                            </li>
                            @if ($trialDays > 0)
                                <li class="flex items-center gap-1.5">
                                    <span class="h-1 w-1 rounded-full bg-emerald-600" aria-hidden="true"></span>
                                    {{ \App\Support\PlanDisplay::trialDaysLabel($trialDays) }}
                                </li>
                            @endif
                        </ul>
                        <div data-price-panel="monthly" class="mt-2">
                            <p class="m-0 text-2xl font-extrabold text-[#1F2937] dark:text-[#F3F4F6]">{{ $monthlyText }}</p>
                            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_per_month') }}</p>
                        </div>
                        <div data-price-panel="yearly" class="mt-2 hidden">
                            <p class="m-0 text-2xl font-extrabold text-[#1F2937] dark:text-[#F3F4F6]">{{ $yearlyText }}</p>
                            <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('saas.pricing_per_year') }}</p>
                            @if ($yearlySavePct !== null && $yearlySavePct > 0)
                                <p class="m-0 mt-1 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                    {{ __('saas.pricing_yearly_save', ['percent' => $yearlySavePct]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if (count($features))
                        <ul class="mb-4 flex-1 space-y-2 text-sm text-slate-700 dark:text-[#E5E7EB]">
                            @foreach ($features as $f)
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#1F7A8C] dark:bg-[#5EEAD4]" aria-hidden="true"></span>
                                    <span>{{ \App\Support\PlanDisplay::localizedFeature($f) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mb-4 flex-1 text-sm text-slate-500 dark:text-slate-400">{{ __('saas.pricing_no_features') }}</p>
                    @endif
                    @if ($isPremium)
                        <p class="mb-3 rounded-lg border border-violet-200/80 bg-violet-50/80 px-3 py-2 text-xs font-semibold text-violet-900 dark:border-violet-500/30 dark:bg-violet-950/40 dark:text-violet-200">
                            {{ __('saas.pricing_premium_confidence') }}
                        </p>
                    @endif
                    @if (! $hasStripeMonthly && ! app()->environment('local') && ! filter_var(env('SAAS_ALLOW_MANUAL_SUBSCRIBE', false), FILTER_VALIDATE_BOOL))
                        <p class="mb-3 text-xs text-amber-700 dark:text-amber-300">{{ __('saas.pricing_stripe_required') }}</p>
                    @endif
                    @auth
                        @if ($canSubscribe && isset($plan->id))
                            <button
                                type="button"
                                class="saas-choose-plan mt-auto inline-flex w-full items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0c3d66] disabled:opacity-60 dark:bg-[#3B82F6] dark:hover:bg-blue-600"
                                data-checkout-url="{{ route('saas.checkout', $plan) }}"
                            >
                                {{ $isCurrent ? __('saas.pricing_change_plan') : __('saas.pricing_choose_plan') }}
                            </button>
                        @endif
                    @endauth
                    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-[#0F4C81] px-4 py-2.5 text-sm font-bold text-[#0F4C81] no-underline transition hover:bg-[#0F4C81] hover:text-white dark:border-[#60A5FA] dark:text-[#93C5FD] dark:hover:bg-[#1D4ED8]">
                        {{ __('saas.pricing_whatsapp_cta') }}
                    </a>
                </div>
            @endforeach
        </div>
        <p class="mt-5 text-sm text-slate-600 dark:text-slate-300">{{ __('saas.pricing_trust_note') }}</p>

        <p class="mt-3 text-center">
            <a href="{{ route('saas.pricing.brochure') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-[#0F4C81]/30 bg-white px-4 py-2.5 text-sm font-bold text-[#0F4C81] no-underline shadow-sm transition hover:bg-[#0F4C81] hover:text-white dark:border-[#60A5FA]/40 dark:bg-[#111827] dark:text-[#93C5FD] dark:hover:bg-[#2563EB]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                {{ __('saas.pricing_download_brochure') }}
            </a>
        </p>

        @guest
            <p class="mt-6 text-center text-xs text-slate-500 dark:text-[#9CA3AF]">
                @if (Route::has('saas.register-clinic.create'))
                    <a href="{{ route('saas.register-clinic.create') }}" class="font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81]">{{ __('saas.pricing_register_clinic') }}</a>
                @elseif (Route::has('register'))
                    <a href="{{ route('register') }}" class="font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81]">{{ __('saas.pricing_create_account') }}</a>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81]">{{ __('saas.pricing_login') }}</a>
                @endif
                {{ __('saas.pricing_login_return') }}
            </p>
        @endguest
    </div>
</x-guest-layout>

