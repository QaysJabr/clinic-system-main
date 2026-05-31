<x-guest-layout
    :wide="true"
    :page-title="__('legal.terms_title').' — '.config('app.name')"
    :meta-description="__('legal.terms_meta')"
>
    <article class="legal-doc prose prose-slate max-w-none dark:prose-invert">
        <header class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-700">
            <h1 class="m-0 text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('legal.terms_title') }}</h1>
            <p class="m-0 mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('legal.last_updated', ['date' => '2026-05-27']) }}</p>
            <p class="m-0 mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">{{ __('legal.doc_disclaimer') }}</p>
        </header>

        @foreach (__('legal.terms_sections') as $section)
            <section class="mb-8">
                <h2 class="mb-3 text-lg font-extrabold text-[#0F4C81] dark:text-[#93C5FD]">{{ $section['title'] }}</h2>
                @foreach ($section['paragraphs'] as $paragraph)
                    <p class="mb-3 text-sm leading-relaxed text-slate-700 last:mb-0 dark:text-slate-300">{{ $paragraph }}</p>
                @endforeach
            </section>
        @endforeach
    </article>
</x-guest-layout>
