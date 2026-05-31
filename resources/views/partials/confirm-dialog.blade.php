{{-- نافذة تأكيد مخصّصة لأي <form> يحمل data-confirm (اختياري: data-confirm-title) --}}
<div
    x-data="clinicConfirmDialog()"
    x-cloak
    @keydown.escape.window="dialogVisible && cancel()"
>
    <template x-if="dialogVisible">
        <div
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[200] flex items-end justify-center sm:items-center"
        >
            <div
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px] dark:bg-slate-950/65"
                @click="cancel()"
                aria-hidden="true"
            ></div>
            <div
                @click.stop
                x-transition:enter="ease-out duration-240"
                x-transition:enter-start="opacity-0 translate-y-8 scale-[0.94] sm:translate-y-2 sm:scale-[0.96]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-180"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-5 scale-[0.96]"
                class="relative z-10 mx-3 mb-[max(1.25rem,env(safe-area-inset-bottom))] w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xl shadow-slate-900/25 ring-1 ring-slate-900/5 dark:border-[#374151] dark:bg-[#1F2937] dark:shadow-black/50 dark:ring-white/5 sm:mx-4 sm:mb-0"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="clinic-confirm-dialog-title"
                aria-describedby="clinic-confirm-dialog-desc"
            >
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-amber-400 via-rose-500 to-[#0F4C81] opacity-90 dark:from-amber-500 dark:via-rose-600 dark:to-[#3B82F6]"></div>
                <div class="flex gap-4 p-5 pt-6 sm:p-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-300" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1 text-start">
                        <h2 id="clinic-confirm-dialog-title" class="m-0 text-lg font-bold text-slate-900 dark:text-[#F3F4F6]" x-text="headingText"></h2>
                        <p id="clinic-confirm-dialog-desc" class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-600 dark:text-[#CBD5E1]" x-text="bodyText"></p>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-4 dark:border-[#374151] dark:bg-[#111827]/80 sm:flex-row sm:justify-end sm:gap-3 sm:px-5">
                    <button type="button" @click="cancel()" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 active:scale-[0.99] dark:border-[#4B5563] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151] sm:w-auto sm:min-w-[7rem]">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="button" @click="confirmSubmit()" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-l from-[#B91C1C] to-[#DC2626] px-4 py-3 text-sm font-bold text-white shadow-md shadow-rose-900/25 transition hover:from-[#991B1B] hover:to-[#B91C1C] active:scale-[0.99] dark:from-rose-700 dark:to-rose-600 dark:hover:from-rose-600 dark:hover:to-rose-500 sm:w-auto sm:min-w-[7rem]">
                        {{ __('common.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
