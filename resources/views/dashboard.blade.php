<x-app-layout>
    @push('scripts')
        @vite('resources/js/clinic-dashboard.js')
    @endpush
    @include('dashboard.partials.content')
</x-app-layout>
