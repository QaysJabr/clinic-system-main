<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
    <table class="min-w-full text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-start dark:border-[#374151] dark:bg-[#111827]">
            <tr>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_id') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_name') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_owner') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_plan') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_subscription') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_expires') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_total_paid') }}</th>
                <th class="px-4 py-3 font-semibold">{{ __('platform.col_is_active') }}</th>
                <th class="px-4 py-3 font-semibold min-w-[14rem]">{{ __('platform.col_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clinics as $clinic)
                @php
                    $tier = \App\Support\PlatformClinicPresentation::subscriptionTier($clinic);
                @endphp
                <tr class="border-b border-slate-100 dark:border-[#374151]">
                    <td class="px-4 py-3">{{ $clinic->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $clinic->name }}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ $clinic->owner?->email ?? __('common.em_dash') }}</td>
                    <td class="px-4 py-3">{{ $clinic->plan?->name ?? __('platform.no_plan') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ \App\Support\PlatformClinicPresentation::tierBadgeClass($tier) }}">
                            {{ \App\Support\PlatformClinicPresentation::tierLabel($tier) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-[#9CA3AF]">{{ $clinic->subscription_expires_at?->format('d/m/Y H:i') ?? __('common.em_dash') }}</td>
                    <td class="px-4 py-3">{{ number_format((float) ($clinic->total_paid ?? 0), 2) }}</td>
                    <td class="px-4 py-3">{{ $clinic->is_active ? __('platform.yes') : __('platform.no') }}</td>
                    <td class="px-4 py-3 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            @if(! $clinic->is_active)
                                <button type="button" data-clinic-action="activate" data-url="{{ route('platform.clinics.activate', $clinic) }}" class="rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ __('platform.activate_clinic') }}</button>
                            @else
                                <button type="button" data-clinic-action="suspend" data-url="{{ route('platform.clinics.suspend', $clinic) }}" data-confirm-title="{{ __('platform.confirm_suspend_title') }}" data-confirm="{{ __('platform.confirm_suspend_body') }}" class="rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-800 hover:bg-slate-100 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#E5E7EB]">{{ __('platform.suspend_clinic') }}</button>
                            @endif
                            <a href="{{ route('platform.clinics.subscription', $clinic) }}" data-spa class="rounded border border-[#0F4C81]/30 bg-[#0F4C81]/10 px-2 py-1 text-xs font-semibold text-[#0F4C81] no-underline hover:bg-[#0F4C81]/15 dark:border-[#3B82F6]/40 dark:bg-blue-950/30 dark:text-[#93C5FD]">{{ __('platform.subscription') }}</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('platform.no_clinics_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $clinics->links() }}
</div>
