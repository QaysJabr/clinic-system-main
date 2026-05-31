@props(['class' => ''])
<div {{ $attributes->merge(['class' => 'dash-shell mx-auto w-full max-w-[1400px] '.$class, 'data-clinic-dashboard' => '']) }}>
    {{ $slot }}
</div>
