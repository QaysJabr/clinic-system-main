@php
    $usesPlatformLayout = auth()->check() && auth()->user()->hasRole('super_admin');
@endphp
@if ($usesPlatformLayout)
    <x-platform-layout>
        @include('security.sessions.partials.content')
    </x-platform-layout>
@else
    <x-app-layout>
        @include('security.sessions.partials.content')
    </x-app-layout>
@endif
