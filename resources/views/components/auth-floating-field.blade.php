@props([
    'label',
    'name',
    'id',
    'type' => 'text',
    'icon' => null,
    'autocomplete' => null,
    'required' => false,
    'autofocus' => false,
    'value' => '',
    'placeholder' => '',
])

@php
    $messages = $errors->get($name);
    $oldVal = old($name, $value);
    $hasError = $messages && count($messages) > 0;

    if (! $icon) {
        $icon = match (true) {
            $type === 'email' => 'email',
            $type === 'password' => 'lock',
            default => 'user',
        };
    }

    $inputClass = 'auth-field-input block w-full rounded-xl border bg-white py-3 text-base text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:outline-none focus:ring-2 sm:text-sm dark:bg-slate-900 dark:text-slate-50 dark:placeholder:text-slate-500 ';
    $inputClass .= $hasError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
        : 'border-slate-200 focus:border-[#0F4C81] focus:ring-[#0F4C81]/20 dark:border-slate-600 dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25';
    $padInline = $type === 'password' ? 'ps-11 pe-11' : 'ps-11 pe-4';
    $inputClass .= ' '.$padInline;
@endphp

<div class="auth-field">
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-slate-800 dark:text-slate-100">
        {{ $label }}
    </label>

    @if ($type === 'password')
        <div class="relative" x-data="{ show: false }">
            <span class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </span>
            <input
                :type="show ? 'text' : 'password'"
                name="{{ $name }}"
                id="{{ $id }}"
                @if ($required) required @endif
                @if ($autofocus) autofocus @endif
                @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                spellcheck="false"
                value="{{ $oldVal }}"
                class="{{ $inputClass }}"
            />
            <button
                type="button"
                @click="show = !show"
                class="absolute end-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-[#0F4C81] dark:hover:bg-slate-800 dark:hover:text-[#93C5FD]"
                tabindex="-1"
                :aria-label="show ? '{{ __('auth.hide_password') }}' : '{{ __('auth.show_password') }}'"
            >
                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        </div>
    @else
        <div class="relative">
            <span class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" aria-hidden="true">
                @if ($icon === 'email')
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                @elseif ($icon === 'building')
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M4.5 21V7.5a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5V21M4.5 21h15M9 21V9.75a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 9.75V21"/>
                    </svg>
                @else
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                @endif
            </span>
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $id }}"
                @if ($required) required @endif
                @if ($autofocus) autofocus @endif
                @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                spellcheck="false"
                autocapitalize="off"
                placeholder="{{ $placeholder }}"
                class="{{ $inputClass }}"
                value="{{ $oldVal }}"
            />
        </div>
    @endif

    @if ($hasError)
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $messages[0] }}</p>
    @endif
</div>
