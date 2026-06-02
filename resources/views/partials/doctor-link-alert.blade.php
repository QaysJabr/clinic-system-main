@if(auth()->check() && auth()->user()->hasRole('doctor') && ! auth()->user()->hasRole('admin') && ! auth()->user()->linkedDoctor())
    <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100" role="alert">
        <p class="m-0 font-bold">{{ __('doctor_link.alert_title') }}</p>
        <p class="m-0 mt-1">{{ __('doctor_link.alert_body') }}</p>
        <p class="m-0 mt-2 text-xs opacity-90">{{ __('doctor_link.alert_admin_hint') }}</p>
    </div>
@endif
