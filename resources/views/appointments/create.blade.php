<x-app-layout>
    @push('scripts')
        @vite('resources/js/appointment-slots.js')
    @endpush
    @include('appointments.partials.create')
</x-app-layout>
