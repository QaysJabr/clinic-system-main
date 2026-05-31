<x-app-layout>

    <div class="mx-auto max-w-[1400px] py-6" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">

        @include('inventory.partials.nav')

        <h1 class="mb-6 text-[28px] font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('common.edit') }} — {{ $item->name }}</h1>

        @include('inventory.items.partials.form', ['item' => $item])

    </div>

</x-app-layout>

