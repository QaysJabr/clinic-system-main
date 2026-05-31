@props(['profile'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold '.$profile->statusBadgeClasses()]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $profile->status === \App\Models\StaffCompensationProfile::STATUS_ACTIVE ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
    {{ $profile->statusLabel() }}
</span>
