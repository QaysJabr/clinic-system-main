<x-guest-layout
    :shake-on-error="true"
    :page-title="__('auth.login_heading').' — '.config('app.name')"
    :meta-description="__('auth.login_subtitle')"
>
    <header class="mb-6 text-center lg:text-start">
        <p class="auth-page-kicker m-0 text-xs font-bold uppercase tracking-wider text-[#0F4C81] dark:text-[#93C5FD]">{{ __('auth.login') }}</p>
        <h1 class="auth-lp-heading auth-page-title m-0 mt-2 text-xl font-extrabold sm:text-2xl">{{ __('auth.login_heading') }}</h1>
        <p class="auth-page-lead m-0 mt-2 text-sm">{{ __('auth.login_subtitle') }}</p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" data-auth-form data-no-spa>
        @csrf

        <x-auth-floating-field
            :label="__('auth.email')"
            name="email"
            id="email"
            type="email"
            icon="email"
            autocomplete="username"
            placeholder="name@clinic.com"
            :required="true"
            :autofocus="true"
        />

        <x-auth-floating-field
            :label="__('auth.password')"
            name="password"
            id="password"
            type="password"
            icon="lock"
            autocomplete="current-password"
            :required="true"
        />

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-slate-700 dark:text-slate-300">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#0F4C81] focus:ring-[#0F4C81]/30 dark:border-slate-600 dark:bg-slate-800" name="remember">
                <span>{{ __('auth.remember_me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="font-semibold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]" href="{{ route('password.request') }}" data-no-spa>
                    {{ __('auth.forgot_password') }}
                </a>
            @endif
        </div>

        <x-auth-submit-button :loading-text="__('auth.login_loading')">
            {{ __('auth.login') }}
        </x-auth-submit-button>
    </form>

    @if (Route::has('saas.register-clinic.create') || Route::has('register'))
        <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600 dark:border-slate-700 dark:text-slate-400 lg:text-start">
            {{ __('auth.no_account') }}
            @if (Route::has('saas.register-clinic.create'))
                <a href="{{ route('saas.register-clinic.create') }}" data-no-spa class="font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]">{{ __('auth.create_account') }}</a>
            @else
                <a href="{{ route('register') }}" data-no-spa class="font-bold text-[#0F4C81] no-underline hover:underline dark:text-[#93C5FD]">{{ __('auth.create_account') }}</a>
            @endif
        </p>
    @endif
</x-guest-layout>
