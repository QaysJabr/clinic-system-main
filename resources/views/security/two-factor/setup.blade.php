@php
    $usesPlatformLayout = auth()->check() && auth()->user()->hasRole('super_admin');
@endphp
@if ($usesPlatformLayout)
    <x-platform-layout>
        @include('security.two-factor.partials.setup')
    </x-platform-layout>
@else
    <x-app-layout>
        @include('security.two-factor.partials.setup')
    </x-app-layout>
@endif
