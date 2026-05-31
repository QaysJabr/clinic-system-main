<div class="space-y-4">
    <p class="m-0 text-sm text-gray-600 dark:text-[#E5E7EB]">
        {{ __('profile.delete_account_intro') }}
    </p>

    <button
        type="button"
        class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-50 dark:border-red-500/45 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950/60"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('profile.delete_account_button') }}
    </button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="m-0 text-lg font-bold text-gray-900 dark:text-[#F3F4F6]">{{ __('profile.delete_modal_title') }}</h2>
        <p class="m-0 mt-2 text-sm text-gray-600 dark:text-[#CBD5E1]">
            {{ __('profile.delete_modal_body') }}
        </p>

        <div class="mt-5">
            <label for="password" class="sr-only">{{ __('profile.label_password_modal') }}</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="{{ __('profile.password_placeholder_modal') }}"
                class="mt-1 block w-full max-w-md rounded-lg border bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0F4C81] focus:outline-none focus:ring-2 focus:ring-[#0F4C81]/20 dark:bg-[#111827] dark:text-[#F3F4F6] dark:focus:border-[#3B82F6] dark:focus:ring-[#3B82F6]/25 {{ $errors->userDeletion->has('password') ? 'border-red-300 dark:border-red-500/55' : 'border-slate-200 dark:border-[#4B5563]' }}"
            />
            @if ($errors->userDeletion->has('password'))
                <p class="mt-1.5 text-sm font-medium text-red-600">{{ $errors->userDeletion->first('password') }}</p>
            @endif
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-2">
            <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]" x-on:click="$dispatch('close')">
                {{ __('common.cancel') }}
            </button>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-500">
                {{ __('profile.btn_delete_finalize') }}
            </button>
        </div>
    </form>
</x-modal>
