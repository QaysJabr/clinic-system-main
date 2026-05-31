@props(['roleName'])

@php
    $name = (string) $roleName;
    $classes = match ($name) {
        'super_admin' => 'bg-indigo-100 text-indigo-900 dark:bg-indigo-950/45 dark:text-indigo-300',
        'admin', 'clinic_owner' => 'bg-violet-100 text-violet-900 dark:bg-violet-950/45 dark:text-violet-300',
        'doctor' => 'bg-cyan-100 text-cyan-900 dark:bg-cyan-950/45 dark:text-cyan-300',
        'receptionist' => 'bg-teal-100 text-teal-900 dark:bg-teal-950/45 dark:text-teal-300',
        'accountant' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/45 dark:text-amber-300',
        default => 'bg-slate-100 text-slate-800 dark:bg-slate-700/60 dark:text-slate-100',
    };
    $label = match ($name) {
        'super_admin' => __('chat.role_super_admin'),
        'admin' => __('chat.role_admin'),
        'clinic_owner' => __('settings.users_role_clinic_owner'),
        'doctor' => __('chat.role_doctor'),
        'receptionist' => __('chat.role_receptionist'),
        'accountant' => __('chat.role_accountant'),
        default => $name !== '' ? $name : __('chat.role_unknown'),
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {$classes}"]) }}>
    {{ $label }}
</span>
