<x-guest-layout
    :shake-on-error="true"
    :wide-form="true"
    :page-title="__('saas.register_clinic_title').' — '.config('app.name')"
    :meta-description="__('saas.register_clinic_subtitle')"
>
    <header class="mb-6 text-center lg:text-start">
        <p class="auth-page-kicker m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('saas.pricing_register_clinic') }}</p>
        <h1 class="auth-lp-heading auth-page-title m-0 mt-2 text-xl font-extrabold sm:text-2xl">{{ __('saas.register_clinic_title') }}</h1>
        <p class="auth-page-lead m-0 mt-2 text-sm">{{ __('saas.register_clinic_subtitle') }}</p>
    </header>

    <ul class="mb-5 flex flex-wrap gap-2 lg:hidden">
        @foreach ([__('saas.landing_trust_1'), __('saas.landing_trust_2'), __('saas.landing_trust_3')] as $trust)
            <li class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                <span class="text-emerald-600 dark:text-emerald-400" aria-hidden="true">✓</span>
                {{ $trust }}
            </li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('saas.register-clinic.store') }}" data-auth-form data-no-spa>
        @csrf

        <x-auth-floating-field
            :label="__('saas.register_clinic_name')"
            name="clinic_name"
            id="clinic_name"
            type="text"
            icon="building"
            autocomplete="organization"
            :required="true"
            :autofocus="true"
        />

        <x-auth-floating-field
            :label="__('saas.register_owner_name')"
            name="owner_name"
            id="owner_name"
            type="text"
            autocomplete="name"
            :required="true"
        />

        <x-auth-floating-field
            :label="__('saas.register_email')"
            name="email"
            id="email"
            type="email"
            icon="email"
            autocomplete="username"
            placeholder="owner@clinic.com"
            :required="true"
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-auth-floating-field
                :label="__('saas.register_password')"
                name="password"
                id="password"
                type="password"
                icon="lock"
                autocomplete="new-password"
                :required="true"
            />

            <x-auth-floating-field
                :label="__('saas.register_password_confirm')"
                name="password_confirmation"
                id="password_confirmation"
                type="password"
                icon="lock"
                autocomplete="new-password"
                :required="true"
            />
        </div>

        <p class="m-0 text-xs leading-relaxed text-slate-600 dark:text-slate-400">{{ __('auth.register_terms_note') }}</p>

        <label for="terms_accepted" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800/50">
            <input
                id="terms_accepted"
                type="checkbox"
                name="terms_accepted"
                value="1"
                class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]/30 dark:border-slate-600 dark:bg-slate-800"
                required
                @checked(old('terms_accepted'))
            >
            <span class="text-slate-700 dark:text-slate-200">
                {{ __('auth.accept_terms_label') }}
                <a href="{{ route('legal.terms') }}" class="font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" target="_blank" rel="noopener noreferrer" data-no-spa>{{ __('legal.terms_nav') }}</a>
                {{ __('auth.accept_terms_and') }}
                <a href="{{ route('legal.privacy') }}" class="font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" target="_blank" rel="noopener noreferrer" data-no-spa>{{ __('legal.privacy_nav') }}</a>
            </span>
        </label>
        @error('terms_accepted')
            <p class="m-0 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <x-auth-submit-button :loading-text="__('saas.register_submit_loading')">
            {{ __('saas.register_submit') }}
        </x-auth-submit-button>

        <p class="text-center text-sm text-slate-600 dark:text-slate-400 lg:text-start">
            {{ __('saas.register_have_account') }}
            <a class="font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" href="{{ route('login') }}" data-no-spa>{{ __('saas.pricing_login') }}</a>
        </p>
    </form>
</x-guest-layout>
