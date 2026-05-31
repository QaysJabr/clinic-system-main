@php
    $usesPlatformLayout = auth()->check() && auth()->user()->hasRole('super_admin');
@endphp
@if ($usesPlatformLayout)
    <x-platform-layout>
        @include('security.two-factor.partials.recovery-codes')
    </x-platform-layout>
@else
    <x-app-layout>
        @include('security.two-factor.partials.recovery-codes')
    </x-app-layout>
@endif
