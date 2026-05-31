@php
    $profileUsesPlatformLayout = auth()->check() && auth()->user()->hasRole('super_admin');
@endphp
@if ($profileUsesPlatformLayout)
    <x-platform-layout>
        @include('profile.partials.edit')
    </x-platform-layout>
@else
    <x-app-layout>
        @include('profile.partials.edit')
    </x-app-layout>
@endif
