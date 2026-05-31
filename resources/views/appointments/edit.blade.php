<x-app-layout>
    @push('scripts')
        @vite('resources/js/appointment-slots.js')
    @endpush
    @include('appointments.partials.edit')
</x-app-layout>
