<x-app-layout>

<div class="max-w-3xl mx-auto py-6">

    @include('inventory.partials.nav')

    <h1 class="text-xl font-bold text-[#0F4C81] dark:text-[#93C5FD] mb-4">{{ __('inventory.template_create_title') }}</h1>

    @include('inventory.templates.partials.form', ['template' => null, 'items' => $items])

</div>

</x-app-layout>

