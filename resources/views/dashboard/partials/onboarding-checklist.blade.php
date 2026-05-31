@if (! empty($showOnboardingChecklist))
    <div class="mb-8 rounded-2xl border border-[#0F4C81]/25 bg-gradient-to-br from-[#0F4C81]/5 to-white p-5 shadow-sm dark:border-[#3B82F6]/30 dark:from-[#3B82F6]/10 dark:to-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('dashboard.onboarding_kicker') }}</p>
                <h2 class="m-0 mt-1 text-lg font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.onboarding_title') }}</h2>
                <p class="m-0 mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">{{ __('dashboard.onboarding_subtitle') }}</p>
            </div>
            <form method="POST" action="{{ route('dashboard.onboarding-dismiss') }}" class="shrink-0">
                @csrf
                <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    {{ __('dashboard.onboarding_dismiss') }}
                </button>
            </form>
        </div>
        <ol class="m-0 mt-5 grid list-none gap-3 p-0 sm:grid-cols-2 lg:grid-cols-4">
            <li class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-900/80">
                <span class="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#0F4C81] text-xs font-extrabold text-white dark:bg-[#3B82F6]">1</span>
                <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.onboarding_s1_title') }}</p>
                <p class="m-0 mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('dashboard.onboarding_s1_desc') }}</p>
                <a href="{{ route('saas.pricing') }}" class="mt-3 inline-flex text-xs font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-no-spa>{{ __('dashboard.onboarding_s1_cta') }} →</a>
            </li>
            @can('manage settings')
                <li class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-900/80">
                    <span class="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#0F4C81] text-xs font-extrabold text-white dark:bg-[#3B82F6]">2</span>
                    <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.onboarding_s2_title') }}</p>
                    <p class="m-0 mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('dashboard.onboarding_s2_desc') }}</p>
                    <a href="{{ route('settings.edit') }}" class="mt-3 inline-flex text-xs font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-spa>{{ __('dashboard.onboarding_s2_cta') }} →</a>
                </li>
            @endcan
            @can('manage doctors')
                <li class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-900/80">
                    <span class="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#0F4C81] text-xs font-extrabold text-white dark:bg-[#3B82F6]">3</span>
                    <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.onboarding_s3_title') }}</p>
                    <p class="m-0 mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('dashboard.onboarding_s3_desc') }}</p>
                    <a href="{{ route('doctors.create') }}" class="mt-3 inline-flex text-xs font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-spa>{{ __('dashboard.onboarding_s3_cta') }} →</a>
                </li>
            @endcan
            @can('manage patients')
                <li class="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-900/80">
                    <span class="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#0F4C81] text-xs font-extrabold text-white dark:bg-[#3B82F6]">4</span>
                    <p class="m-0 text-sm font-extrabold text-slate-900 dark:text-white">{{ __('dashboard.onboarding_s4_title') }}</p>
                    <p class="m-0 mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('dashboard.onboarding_s4_desc') }}</p>
                    <a href="{{ route('patients.create') }}" class="mt-3 inline-flex text-xs font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" data-spa>{{ __('dashboard.onboarding_s4_cta') }} →</a>
                </li>
            @endcan
        </ol>
    </div>
@endif
