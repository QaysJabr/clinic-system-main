@props(['minWidth' => '640'])
<div {{ $attributes->merge(['class' => 'dash-table-wrap']) }}>
    @if(isset($header))
        <div class="dash-table-header">{{ $header }}</div>
    @endif
    <div class="overflow-x-auto">
        <table class="dash-table" style="min-width: {{ $minWidth }}px">
            {{ $slot }}
        </table>
    </div>
</div>
