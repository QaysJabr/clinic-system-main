{{-- Cairo is bundled via @fontsource in app.css (no external stylesheet — CSP-safe). --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
