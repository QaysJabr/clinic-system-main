@if(! empty($pageTitle ?? null))
    <span class="sr-only" data-spa-page-title>{{ $pageTitle }}</span>
@endif
<div id="admin-plans-page" class="max-w-6xl mx-auto" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
     data-url-data="{{ route('admin.plans.data') }}"
     data-url-store="{{ route('admin.plans.store') }}"
     data-url-base="{{ url('/admin/plans') }}"
>
    @include('platform.partials.nav', ['active' => 'plans'])
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-[24px] font-bold text-[#0F4C81] dark:text-[#93C5FD] m-0">{{ __('platform.plans_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-[#9CA3AF] m-0">{{ __('platform.plans_subtitle') }}</p>
        </div>
        <button type="button" id="admin-plans-btn-new" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#0F4C81] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">
            {{ __('platform.add_plan') }}
        </button>
    </div>

    <div id="admin-plans-alert" class="mb-4 hidden rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 dark:border-[#374151] dark:bg-[#111827] dark:text-[#E5E7EB]" role="status"></div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-[#374151] dark:bg-[#1F2937]">
        <table class="min-w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-start dark:border-[#374151] dark:bg-[#111827]">
                <tr>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.sort_order') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.name') }}</th>
                    <th class="px-4 py-3 font-semibold">slug</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.monthly_price') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.yearly_price') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.limits') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.stripe') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('common.status') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('platform.clinics_count') }}</th>
                    <th class="px-4 py-3 font-semibold min-w-[14rem]">{{ __('platform.actions') }}</th>
                </tr>
            </thead>
            <tbody id="admin-plans-tbody">
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-slate-500 dark:text-[#9CA3AF]">{{ __('platform.loading_table') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-xs text-slate-500 dark:text-[#9CA3AF] m-0">
        {{ __('platform.pricing_public_note') }} <a href="{{ route('saas.pricing') }}" data-no-spa class="font-semibold text-[#1F7A8C] underline dark:text-[#5EEAD4]">{{ __('saas.pricing_nav') }}</a>.
    </p>

    <div id="admin-plans-modal" class="admin-plans-modal" hidden aria-hidden="true">
        <div class="admin-plans-modal__backdrop" aria-hidden="true"></div>
        <div class="admin-plans-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-plans-modal-title">
        <div class="admin-plans-modal__panel">
            <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-200 bg-slate-50/90 px-5 py-4 dark:border-[#374151] dark:bg-[#111827]/80">
                <div class="min-w-0">
                    <h2 id="admin-plans-modal-title" class="m-0 text-lg font-bold text-[#0F4C81] dark:text-[#93C5FD]">{{ __('platform.plan') }}</h2>
                    <p id="admin-plans-modal-subtitle" class="mt-1 mb-0 truncate text-xs text-slate-500 dark:text-[#9CA3AF]"></p>
                </div>
                <button type="button" id="admin-plans-modal-close" class="shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]" aria-label="{{ __('platform.close') }}">×</button>
            </div>
            <form id="admin-plans-form" class="flex min-h-0 flex-1 flex-col">
                <input type="hidden" name="id" id="admin-plans-field-id" value="">
                <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 text-sm">
                    <fieldset class="space-y-3 border-0 p-0 m-0">
                        <legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.plan_section_basic') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-name">{{ __('platform.name') }}</label>
                                <input id="admin-plans-field-name" name="name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-slug">slug</label>
                                <input id="admin-plans-field-slug" name="slug" required dir="ltr" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-sort">{{ __('platform.sort_order') }}</label>
                                <input id="admin-plans-field-sort" name="sort_order" type="number" min="0" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-trial">{{ __('platform.trial_days') }}</label>
                                <input id="admin-plans-field-trial" name="trial_days" type="number" min="0" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-5 space-y-3 border-0 p-0 m-0">
                        <legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.plan_section_pricing') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-pm">{{ __('platform.monthly_price') }}</label>
                                <input id="admin-plans-field-pm" name="price_monthly" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 tabular-nums dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-py">{{ __('platform.yearly_price') }}</label>
                                <input id="admin-plans-field-py" name="price_yearly" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-slate-300 px-3 py-2 tabular-nums dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-5 space-y-3 border-0 p-0 m-0">
                        <legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.plan_section_limits') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-mp">{{ __('platform.patients_limit_hint') }}</label>
                                <input id="admin-plans-field-mp" name="max_patients" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-mu">{{ __('platform.users_limit_hint') }}</label>
                                <input id="admin-plans-field-mu" name="max_users" type="number" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-features">{{ __('platform.features_multiline_hint') }}</label>
                            <textarea id="admin-plans-field-features" name="features_text" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]"></textarea>
                        </div>
                    </fieldset>

                    <fieldset class="mt-5 space-y-3 border-0 p-0 m-0">
                        <legend class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-[#94A3B8]">{{ __('platform.plan_section_stripe') }}</legend>
                        <div class="grid gap-3">
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-stripe-m">{{ __('platform.stripe_monthly_price_id') }}</label>
                                <input id="admin-plans-field-stripe-m" name="stripe_price_id" dir="ltr" placeholder="price_..." class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                            <div>
                                <label class="mb-1 block font-semibold text-slate-700 dark:text-[#E5E7EB]" for="admin-plans-field-stripe-y">{{ __('platform.stripe_yearly_price_id') }}</label>
                                <input id="admin-plans-field-stripe-y" name="stripe_price_yearly_id" dir="ltr" placeholder="price_..." class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#F3F4F6]">
                            </div>
                        </div>
                    </fieldset>

                    <label class="mt-5 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 font-semibold text-slate-700 dark:border-[#374151] dark:bg-[#111827] dark:text-[#E5E7EB]">
                        <input id="admin-plans-field-active" name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 dark:border-[#4B5563]">
                        {{ __('platform.plan_active') }}
                    </label>
                </div>
                <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4 dark:border-[#374151] dark:bg-[#1F2937]">
                    <button type="button" id="admin-plans-modal-cancel" class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#4B5563] dark:bg-[#111827] dark:text-[#E5E7EB] dark:hover:bg-[#374151]">{{ __('common.cancel') }}</button>
                    <button type="submit" id="admin-plans-modal-submit" class="inline-flex rounded-lg bg-[#0F4C81] px-5 py-2 text-sm font-bold text-white hover:bg-[#0c3d66] dark:bg-[#3B82F6] dark:hover:bg-blue-600">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>
