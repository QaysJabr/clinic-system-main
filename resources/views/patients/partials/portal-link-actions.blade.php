@can('view', $patient)
    <form method="POST" action="{{ route('patients.portal-link', $patient) }}" class="inline" data-no-spa data-portal-link-form>
        @csrf
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg border border-violet-300 bg-white px-4 py-2.5 text-sm font-semibold text-violet-900 shadow-sm transition hover:bg-violet-50 dark:border-violet-500/40 dark:bg-[#1F2937] dark:text-violet-200 dark:hover:bg-violet-950/30"
        >
            {{ __('portal.share_portal') }}
        </button>
    </form>
@endcan
