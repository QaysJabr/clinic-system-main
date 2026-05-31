<x-app-layout>

    <div class="mx-auto max-w-[1400px] py-6" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">

        @include('inventory.partials.nav')

        <h1 class="mb-2 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('inventory.btn_add_item') }}</h1>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">{{ __('inventory.items_subtitle') }}</p>

        @include('inventory.items.partials.form', ['item' => null])

    </div>

</x-app-layout>

