<div class="auth-preview-fade w-full max-w-md">
    <div class="rounded-2xl border border-white/15 bg-white/10 p-5 shadow-xl backdrop-blur-md">
        <p class="m-0 text-xs font-bold text-blue-100/90">{{ __('auth.preview_title') }}</p>
        <p class="m-0 mt-0.5 text-[11px] text-blue-100/70">{{ __('auth.preview_subtitle') }}</p>
        <div class="mt-4 grid grid-cols-3 gap-2.5">
            <div class="rounded-xl border border-white/10 bg-white/10 px-2.5 py-2">
                <p class="m-0 text-[9px] font-bold uppercase tracking-wide text-blue-100/80">{{ __('saas.landing_stat_appointments') }}</p>
                <p class="m-0 mt-1 text-lg font-extrabold tabular-nums text-white">12</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/10 px-2.5 py-2">
                <p class="m-0 text-[9px] font-bold uppercase tracking-wide text-blue-100/80">{{ __('auth.preview_visits') }}</p>
                <p class="m-0 mt-1 text-lg font-extrabold tabular-nums text-white">8</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/10 px-2.5 py-2">
                <p class="m-0 text-[9px] font-bold uppercase tracking-wide text-blue-100/80">{{ __('saas.landing_stat_pending') }}</p>
                <p class="m-0 mt-1 text-lg font-extrabold tabular-nums text-amber-200">5</p>
            </div>
        </div>
        <div class="mt-3 space-y-1.5">
            @foreach ([['09:00', __('saas.landing_mock_patient_1')], ['10:30', __('saas.landing_mock_patient_2')]] as [$time, $name])
                <div class="flex items-center justify-between gap-2 rounded-lg border border-white/10 bg-white/5 px-2.5 py-1.5">
                    <span class="text-[10px] font-bold tabular-nums text-blue-100">{{ $time }}</span>
                    <span class="min-w-0 flex-1 truncate text-xs font-medium text-white/90">{{ $name }}</span>
                    <span class="rounded-full bg-emerald-400/20 px-1.5 py-0.5 text-[9px] font-bold text-emerald-100">{{ __('saas.landing_mock_status') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
