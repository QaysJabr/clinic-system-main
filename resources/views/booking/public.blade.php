<x-guest-layout
    :page-title="__('appointments.booking_title').' — '.($clinic->name ?? config('app.name'))"
    :meta-description="__('appointments.booking_meta')"
>
    <div class="mx-auto max-w-lg px-4 py-10" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('appointments.booking_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $clinic->name ?? __('navigation.brand_clinic') }}</p>
        </div>

        <form
            action="{{ route('booking.public.store', $token) }}"
            method="POST"
            class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            data-public-booking
            data-slots-url="{{ route('booking.public.slots', $token) }}"
        >
            @csrf

            <div>
                <label for="full_name" class="mb-1 block text-sm font-semibold">{{ __('patients.field_full_name') }}</label>
                <input type="text" name="full_name" id="full_name" required maxlength="255" value="{{ old('full_name') }}"
                    class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800">
                @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-semibold">{{ __('patients.field_phone') }}</label>
                <input type="tel" name="phone" id="phone" required maxlength="30" value="{{ old('phone') }}"
                    class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800"
                    placeholder="{{ __('appointments.booking_phone_hint') }}">
                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="doctor_id" class="mb-1 block text-sm font-semibold">{{ __('appointments.field_doctor') }}</label>
                <select name="doctor_id" id="doctor_id" required class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800">
                    <option value="">{{ __('appointments.placeholder_select_doctor') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((int) old('doctor_id', $preselectedDoctorId ?? 0) === (int) $doctor->id)>{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="appointment_date" class="mb-1 block text-sm font-semibold">{{ __('appointments.field_appointment_date') }}</label>
                <input type="date" name="appointment_date" id="appointment_date" required min="{{ now()->toDateString() }}" value="{{ old('appointment_date') }}"
                    class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ __('appointments.booking_pick_slot') }}</label>
                <input type="hidden" name="start_time" id="start_time" required value="{{ old('start_time') }}">
                @error('start_time')<p class="mb-2 text-sm text-red-600">{{ $message }}</p>@enderror

                <div id="public-booking-slots-hint" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                    {{ __('appointments.booking_slots_prerequisite') }}
                </div>

                <div id="public-booking-slots-loading" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-300" aria-live="polite">
                    {{ __('appointments.booking_slots_loading') }}
                </div>

                <div id="public-booking-slots" class="hidden min-h-[3rem] grid grid-cols-3 gap-2 sm:grid-cols-4"></div>

                <p id="public-booking-slots-empty" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-center text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                    {{ __('appointments.booking_no_slots') }}
                </p>
            </div>

            <div>
                <label for="reason" class="mb-1 block text-sm font-semibold">{{ __('appointments.field_reason') }}</label>
                <input type="text" name="reason" id="reason" value="{{ old('reason') }}" class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800">
            </div>

            <button type="submit" class="w-full rounded-xl bg-[#0F4C81] py-3 text-sm font-bold text-white hover:bg-[#0c3d66] dark:bg-[#3B82F6]">
                {{ __('appointments.booking_submit') }}
            </button>
        </form>
    </div>

    @push('scripts')
        @vite('resources/js/public-booking.js')
    @endpush
</x-guest-layout>
