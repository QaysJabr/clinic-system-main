<x-guest-layout
    :wide="true"
    :page-title="__('contact.title').' — '.config('app.name')"
    :meta-description="__('contact.meta')"
>
    <div class="mx-auto max-w-2xl">
        <header class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-700">
            <h1 class="m-0 text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('contact.title') }}</h1>
            <p class="m-0 mt-2 text-sm text-slate-600 dark:text-slate-300">{{ __('contact.lead') }}</p>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-700 dark:bg-slate-800/50">
                <h2 class="m-0 text-base font-bold text-slate-900 dark:text-white">{{ __('contact.form_heading') }}</h2>
                <p class="m-0 mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('contact.form_subtitle') }}</p>
            </div>
            <form method="POST" action="{{ route('contact.store') }}" class="p-5 sm:p-6" data-no-spa>
                @csrf
                @php
                    $inputClass = 'block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white';
                    $labelClass = 'mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200';
                @endphp
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="contact_name" class="{{ $labelClass }}">{{ __('contact.field_name') }}</label>
                        <input type="text" id="contact_name" name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $inputClass }}">
                        @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="contact_email" class="{{ $labelClass }}">{{ __('contact.field_email') }}</label>
                        <input type="email" id="contact_email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $inputClass }}">
                        @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="contact_phone" class="{{ $labelClass }}">{{ __('contact.field_phone') }}</label>
                        <input type="tel" id="contact_phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="{{ $inputClass }}">
                        @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="contact_subject" class="{{ $labelClass }}">{{ __('contact.field_subject') }}</label>
                        <input type="text" id="contact_subject" name="subject" value="{{ old('subject') }}" required class="{{ $inputClass }}">
                        @error('subject')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="contact_message" class="{{ $labelClass }}">{{ __('contact.field_message') }}</label>
                        <textarea id="contact_message" name="message" rows="5" required class="{{ $inputClass }}">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#0F4C81] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6]">
                        {{ __('contact.btn_send') }}
                    </button>
                </div>
            </form>
        </div>

        <p class="mb-4 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('contact.direct_heading') }}</p>
        <div class="grid gap-4 sm:grid-cols-2">
            @if ($supportEmail)
                <a href="mailto:{{ $supportEmail }}" class="rounded-2xl border border-slate-200 bg-white p-5 no-underline shadow-sm transition hover:border-[#0F4C81]/30 dark:border-slate-700 dark:bg-slate-900">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-[#0F4C81] dark:text-[#93C5FD]">{{ __('contact.email') }}</p>
                    <p class="m-0 mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $supportEmail }}</p>
                </a>
            @endif
            @if ($supportPhone)
                <a href="tel:{{ $supportPhone }}" class="rounded-2xl border border-slate-200 bg-white p-5 no-underline shadow-sm transition hover:border-[#0F4C81]/30 dark:border-slate-700 dark:bg-slate-900">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-[#0F4C81] dark:text-[#93C5FD]">{{ __('contact.phone') }}</p>
                    <p class="m-0 mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $supportPhone }}</p>
                </a>
            @endif
            @if ($supportWhatsapp)
                @php $waUrl = 'https://wa.me/'.$supportWhatsapp.'?text='.rawurlencode(__('contact.whatsapp_prefill')); @endphp
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 no-underline shadow-sm transition hover:bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/20 sm:col-span-2">
                    <p class="m-0 text-xs font-bold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ __('contact.whatsapp') }}</p>
                    <p class="m-0 mt-2 text-sm font-semibold text-emerald-950 dark:text-emerald-100">{{ __('contact.whatsapp_cta') }}</p>
                </a>
            @endif
        </div>

        @if (! $supportEmail && ! $supportPhone && ! $supportWhatsapp)
            <p class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ __('contact.empty') }}</p>
        @endif
    </div>
</x-guest-layout>
