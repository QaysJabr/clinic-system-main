@props(['status'])

@php
    use App\Enums\AppointmentStatus;
    $enum = AppointmentStatus::tryFrom($status) ?? AppointmentStatus::Scheduled;
    $classes = match ($enum) {
        AppointmentStatus::Scheduled => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
        AppointmentStatus::Confirmed => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
        AppointmentStatus::CheckedIn => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
        AppointmentStatus::InProgress => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
        AppointmentStatus::Completed => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
        AppointmentStatus::Cancelled => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        AppointmentStatus::NoShow => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold '.$classes]) }}>
    {{ __($enum->labelKey()) }}
</span>
