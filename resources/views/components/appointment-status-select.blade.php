@props(['name' => 'status', 'id' => 'status', 'selected' => 'scheduled'])

@php use App\Enums\AppointmentStatus; @endphp

<select
    name="{{ $name }}"
    id="{{ $id }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-[#374151] dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25']) }}
    required
>
    @foreach (AppointmentStatus::cases() as $status)
        <option value="{{ $status->value }}" @selected(old($name, $selected) === $status->value)>
            {{ __($status->labelKey()) }}
        </option>
    @endforeach
</select>
