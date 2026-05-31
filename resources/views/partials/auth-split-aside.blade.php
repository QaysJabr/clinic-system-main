@php
    $isRegister = request()->routeIs('saas.register-clinic.*');
    $appName = config('app.name', 'Clinic System');
@endphp
<aside class="auth-split-aside relative hidden flex-col justify-between overflow-hidden p-10 xl:p-12 lg:flex" aria-hidden="true">
    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.04)_1px,transparent_1px)] bg-[size:28px_28px]"></div>
    <div class="pointer-events-none absolute -end-24 top-0 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -start-16 bottom-0 h-64 w-64 rounded-full bg-blue-300/15 blur-3xl"></div>

    <div class="relative">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 no-underline">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-lg font-bold text-white shadow-lg ring-1 ring-white/20">+</span>
            <span>
                <span class="block text-sm font-extrabold text-white">{{ $appName }}</span>
                <span class="block text-xs text-blue-100/90">{{ __('saas.landing_brand_subtitle') }}</span>
            </span>
        </a>

        <h2 class="auth-lp-heading m-0 mt-10 max-w-md text-2xl font-extrabold leading-tight text-white xl:text-[1.75rem]">
            {{ $isRegister ? __('auth.panel_register_title') : __('auth.panel_login_title') }}
        </h2>
        <p class="m-0 mt-3 max-w-md text-sm leading-relaxed text-blue-100/95">
            {{ $isRegister ? __('auth.panel_register_lead') : __('auth.panel_login_lead') }}
        </p>

        @if ($isRegister)
            <ol class="m-0 mt-8 max-w-md list-none space-y-3 p-0">
                @for ($step = 1; $step <= 3; $step++)
                    <li class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-sm">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/15 text-xs font-extrabold text-white">{{ $step }}</span>
                        <span>
                            <span class="block text-sm font-bold text-white">{{ __('saas.landing_step_'.$step.'_title') }}</span>
                            <span class="mt-0.5 block text-xs leading-relaxed text-blue-100/80">{{ __('saas.landing_step_'.$step.'_desc') }}</span>
                        </span>
                    </li>
                @endfor
            </ol>
        @else
            <ul class="m-0 mt-8 max-w-md list-none space-y-2.5 p-0">
                @foreach ([__('auth.panel_trust_1'), __('auth.panel_trust_2'), __('auth.panel_trust_3')] as $trust)
                    <li class="flex items-center gap-2.5 text-sm text-blue-50/95">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-400/20 text-[10px] text-emerald-200">✓</span>
                        {{ $trust }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="relative mt-10">
        @include('partials.auth-preview-panel')
    </div>
</aside>
