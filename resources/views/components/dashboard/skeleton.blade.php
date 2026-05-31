@props(['lines' => 3, 'class' => ''])
<div {{ $attributes->merge(['class' => 'dash-skeleton '.$class]) }} aria-hidden="true">
    @for($i = 0; $i < $lines; $i++)
        <div class="dash-skeleton-line" style="width: {{ $i === $lines - 1 ? '60%' : '100%' }}"></div>
    @endfor
</div>
