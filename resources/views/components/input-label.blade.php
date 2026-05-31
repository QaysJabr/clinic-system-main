@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-[#1F2937] dark:text-[#E5E7EB]']) }}>
    {{ $value ?? $slot }}
</label>
