@if (session('patient_portal_url'))
    <div class="mb-6 overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/25">
        <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('portal.share_portal') }}</p>
        <input type="text" readonly value="{{ session('patient_portal_url') }}" class="mt-2 block w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-mono text-slate-800 dark:border-emerald-900/50 dark:bg-[#111827] dark:text-slate-100" onclick="this.select()">
        <p class="m-0 mt-2 text-xs text-emerald-900/80 dark:text-emerald-200/80">{{ __('portal.copy_portal_hint') }}</p>
    </div>
@endif
