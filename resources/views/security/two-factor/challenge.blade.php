<x-guest-layout>
    <div class="mx-auto max-w-md w-full">
        <h1 class="text-xl font-bold text-[#0F4C81] m-0">{{ __('security.two_factor_challenge_title') }}</h1>
        <p class="text-sm text-gray-600 mt-2">{{ __('security.two_factor_challenge_intro') }}</p>
        <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('security.two_factor_code') }}</label>
                <input type="text" name="code" required autocomplete="one-time-code"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="trust_device" value="1" class="rounded border-slate-300">
                {{ __('security.two_factor_trust_device', ['days' => config('security.two_factor.trusted_device_days')]) }}
            </label>
            <button type="submit" class="w-full rounded-lg bg-[#0F4C81] px-4 py-2.5 text-sm font-semibold text-white">{{ __('common.confirm') }}</button>
        </form>
    </div>
</x-guest-layout>
