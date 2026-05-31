<x-guest-layout>
    <div class="mx-auto max-w-md px-4 py-16 text-center" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-[#F3F4F6]">{{ __('appointments.booking_confirmed') }}</h1>
        <p class="mt-3 text-sm text-gray-600 dark:text-[#9CA3AF]">
            @safeDate($appointment->appointment_date) · {{ $appointment->start_time }}–{{ $appointment->end_time }}
        </p>
        <p class="mt-1 text-sm text-gray-500">{{ optional($appointment->doctor)->full_name }}</p>
    </div>
</x-guest-layout>
