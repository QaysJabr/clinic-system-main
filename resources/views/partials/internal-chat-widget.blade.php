{{-- Internal team chat — floating widget --}}
@php
    $chatUiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true);
    $chatFabX = $chatUiRtl ? 'md:left-5 md:right-auto' : 'md:right-5 md:left-auto';
    $chatSendIconRtl = $chatUiRtl ? '-translate-x-px rotate-180' : '';
    $__internalChatConfig = [
        'usersUrl' => route('chat.users'),
        'generalUrl' => route('chat.general'),
        'generalClearConversationUrl' => route('chat.general.conversation.clear'),
        'generalSendUrl' => route('chat.general.send'),
        'privateBaseUrl' => url('/chat/private'),
        'privateSendUrl' => route('chat.private.send'),
        'unreadUrl' => route('chat.unread-counts'),
        'messagesBaseUrl' => url('/chat/messages'),
        'canClearGeneral' => auth()->user()->hasRole('admin'),
        'csrf' => csrf_token(),
    ];
@endphp
<script type="application/json" id="internal-chat-config" @if(! empty($cspNonce ?? null)) nonce="{{ $cspNonce }}" @endif>
{!! json_encode($__internalChatConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>
<div id="internal-chat-root" dir="{{ $chatUiRtl ? 'rtl' : 'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="pointer-events-none fixed z-50 flex max-h-dvh flex-col justify-end gap-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))] print:hidden max-md:inset-x-0 max-md:bottom-0 max-md:items-stretch max-md:justify-end max-md:gap-2 max-md:p-2 md:bottom-4 {{ $chatFabX }} md:items-end md:max-h-[calc(100dvh-0.5rem)] md:gap-2">
    <div
        id="internal-chat-panel"
        class="internal-chat-panel-shell pointer-events-auto relative hidden flex min-h-0 w-full max-w-full flex-col overflow-x-hidden rounded-none border border-slate-200 bg-white shadow-2xl shadow-slate-900/15 ring-1 ring-slate-900/10 transition-all duration-200 ease-out dark:border-gray-600 dark:bg-gray-900 dark:shadow-black/50 dark:ring-white/10 max-md:fixed max-md:inset-0 max-md:z-[100] max-md:h-dvh max-md:max-h-dvh md:z-auto md:shrink-0 md:rounded-3xl"
        aria-hidden="true"
    >
        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/15 bg-gradient-to-l from-slate-900 via-blue-950 to-teal-900 px-4 py-3.5 text-white sm:px-5 sm:py-4">
            <div class="min-w-0 flex-1 pe-2">
                <p class="m-0 text-base font-bold tracking-tight sm:text-lg">{{ __('chat.panel_title') }}</p>
                <p class="m-0 mt-1 text-sm font-medium text-white/85">{{ __('chat.panel_subtitle') }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-1.5 rounded-xl bg-black/30 p-1 ring-2 ring-white/25 backdrop-blur-sm">
                <button type="button" id="internal-chat-refresh" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-white/15 text-white shadow-inner transition hover:bg-white/30 hover:ring-2 hover:ring-white/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" title="{{ __('chat.refresh') }}" aria-label="{{ __('chat.refresh') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M4.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                </button>
                <button type="button" id="internal-chat-close" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-white/15 text-white shadow-inner transition hover:bg-red-500/90 hover:ring-2 hover:ring-white/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300" title="{{ __('chat.close') }}" aria-label="{{ __('chat.close') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="shrink-0 border-b border-slate-100 bg-slate-50/90 px-4 py-3 dark:border-gray-700 dark:bg-gray-950" role="presentation">
            <div class="flex gap-1.5 rounded-2xl bg-slate-200/60 p-1.5 dark:bg-gray-800/90" role="tablist">
                <button type="button" role="tab" id="internal-chat-tab-general" class="internal-chat-tab flex-1 rounded-xl px-4 py-3 text-sm font-bold transition-all duration-200 dark:text-gray-200" data-tab="general">{{ __('chat.general') }}</button>
                <button type="button" role="tab" id="internal-chat-tab-private" class="internal-chat-tab flex-1 rounded-xl px-4 py-3 text-sm font-bold transition-all duration-200 dark:text-gray-200" data-tab="private">{{ __('chat.private') }}</button>
            </div>
        </div>

        <div id="internal-chat-body-general" class="internal-chat-tab-panel flex min-h-0 flex-1 flex-col overflow-hidden bg-neutral-200/90 dark:bg-gray-900">
            <div class="flex shrink-0 items-center justify-between gap-2 border-b border-slate-200/80 bg-neutral-100/95 px-2 py-1.5 dark:border-gray-700 dark:bg-gray-900/90">
                <span class="truncate ps-1 text-[11px] font-bold text-slate-600 dark:text-gray-400">{{ __('chat.general_room_label') }}</span>
                <button type="button" id="internal-chat-general-delete-all" @class(['inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-200/90 bg-red-50 text-red-700 shadow-sm transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950/70', 'hidden' => ! auth()->user()->hasRole('admin')]) title="{{ __('chat.delete_general_room_title') }}" aria-label="{{ __('chat.clear_general_modal_title') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </button>
            </div>
            <div id="internal-chat-general-messages" class="internal-chat-msg-scroll internal-chat-wallpaper min-h-0 flex-1 space-y-3 overflow-y-auto overflow-x-hidden px-3 py-4 sm:px-4"></div>
            <div id="internal-chat-general-context" class="hidden border-t border-dashed border-slate-200/80 bg-white/60 px-3 py-2 text-xs text-slate-600 dark:border-gray-600 dark:bg-gray-800/90 dark:text-gray-300"></div>
            <div class="shrink-0 border-t border-slate-200/90 bg-white p-4 dark:border-gray-600 dark:bg-gray-950">
                <div id="internal-chat-general-status" class="mb-2 hidden text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400"></div>
                <div id="internal-chat-general-context-row" class="mb-2 flex items-center gap-2 rounded-xl bg-slate-50 px-2.5 py-2 dark:bg-gray-800/80">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-slate-600 dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    </span>
                    <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-2">
                        <input type="checkbox" id="internal-chat-general-attach" class="h-4 w-4 shrink-0 rounded border-slate-300 text-blue-900 focus:ring-blue-900 dark:border-gray-600 dark:bg-gray-800" />
                        <span id="internal-chat-general-attach-label" class="min-w-0 text-xs font-medium leading-snug text-slate-600 dark:text-gray-400"></span>
                    </label>
                </div>
                <form id="internal-chat-general-form" class="flex flex-col gap-2">
                    <div id="internal-chat-general-edit-banner" class="hidden flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-300/90 bg-amber-50 px-3 py-2 text-xs text-amber-950 dark:border-amber-800/70 dark:bg-amber-950/50 dark:text-amber-100" role="status">
                        <span class="min-w-0 flex-1 font-bold leading-snug">{{ __('chat.edit_banner') }}</span>
                        <button type="button" id="internal-chat-general-edit-cancel" class="shrink-0 rounded-lg border border-amber-400/80 bg-white px-2.5 py-1 text-xs font-bold text-amber-900 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-gray-900 dark:text-amber-200 dark:hover:bg-gray-800">{{ __('chat.cancel') }}</button>
                    </div>
                    <p id="internal-chat-general-sending" class="hidden text-center text-xs font-medium text-slate-500 dark:text-gray-400">{{ __('chat.sending') }}</p>
                    <div class="flex items-end gap-2 rounded-3xl border border-slate-200 bg-white px-1 py-1 shadow-sm dark:border-gray-600 dark:bg-gray-900">
                        <textarea id="internal-chat-general-input" rows="2" maxlength="2000" placeholder="{{ __('chat.write_message') }}" class="internal-chat-composer-input min-h-11 flex-1 resize-none rounded-3xl border-0 bg-transparent px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-gray-100 dark:placeholder:text-gray-400 dark:caret-emerald-400"></textarea>
                        <button type="submit" id="internal-chat-general-send" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-emerald-700 dark:hover:bg-emerald-600" title="{{ __('chat.send') }}" aria-label="{{ __('chat.send') }}">
                            <svg class="h-5 w-5 {{ $chatSendIconRtl }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="internal-chat-body-private" class="internal-chat-tab-panel hidden flex min-h-0 flex-1 flex-col overflow-hidden bg-neutral-200/90 dark:bg-gray-900">
            <div class="flex min-h-0 flex-1 flex-col md:flex-row md:items-stretch md:gap-0">
                <aside id="internal-chat-private-sidebar" class="order-1 flex max-h-48 min-h-0 w-full shrink-0 flex-col border-slate-200/90 bg-white dark:border-gray-700 dark:bg-gray-900 md:order-2 md:max-h-none md:h-full md:w-[13rem] md:min-w-[13rem] md:max-w-[38%] md:border-s">
                    <p class="m-0 px-3 pt-2 pb-1 text-center text-xs text-slate-500 md:hidden">{{ __('chat.private_pick_sidebar') }}</p>
                    <div class="shrink-0 border-b border-slate-100 px-2 py-2 dark:border-gray-800">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 end-2.5 flex items-center text-slate-400 dark:text-gray-500" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </span>
                            <input type="search" id="internal-chat-user-search" autocomplete="off" placeholder="{{ __('chat.search_placeholder') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pe-10 ps-3 text-base shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600/30 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500" />
                        </div>
                    </div>
                    <div id="internal-chat-user-list" class="min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto overscroll-contain dark:divide-gray-800"></div>
                </aside>
                <main id="internal-chat-private-main" class="order-2 flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden bg-neutral-200/80 dark:bg-gray-900 md:order-1 md:min-w-0 md:flex-[1_1_0%] max-md:hidden">
                    <div id="internal-chat-private-empty" class="flex flex-1 flex-col items-center justify-center gap-2 p-8 text-center">
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-200/80 dark:bg-gray-800 dark:text-gray-500 dark:ring-gray-700">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </span>
                        <p class="m-0 text-sm font-bold text-slate-800 dark:text-gray-200">{{ __('chat.private_pick_title') }}</p>
                        <p class="m-0 max-w-xs text-xs leading-relaxed text-slate-500 dark:text-gray-500">{{ __('chat.private_pick_hint') }}</p>
                    </div>
                    <div id="internal-chat-private-thread" class="hidden flex min-h-0 flex-1 flex-col overflow-hidden">
                        <div id="internal-chat-private-header" class="hidden shrink-0 border-b border-slate-200/90 bg-white px-2 py-2 shadow-sm dark:border-gray-600 dark:bg-gray-800">
                            <div class="flex items-center gap-2">
                                <button type="button" id="internal-chat-private-back" class="hidden inline-flex shrink-0 rounded-full p-2 text-slate-600 transition hover:bg-slate-100 md:hidden dark:text-gray-300 dark:hover:bg-gray-800" title="{{ __('chat.back_to_list') }}" aria-label="{{ __('chat.back_to_list') }}">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                </button>
                                <span id="internal-chat-private-partner-avatar" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white shadow-sm" aria-hidden="true">؟</span>
                                <div class="min-w-0 flex-1">
                                    <p id="internal-chat-private-partner-name" class="m-0 truncate text-sm font-bold leading-tight text-slate-900 dark:text-gray-100"></p>
                                    <p id="internal-chat-private-status-line" class="m-0 mt-0.5 truncate text-xs text-emerald-700 dark:text-emerald-400">{{ __('chat.online') }}</p>
                                    <p id="internal-chat-private-typing" class="m-0 mt-0.5 hidden truncate text-xs italic text-slate-400 dark:text-gray-500" aria-hidden="true">{{ __('chat.typing') }}</p>
                                </div>
                                <button type="button" id="internal-chat-private-empty-view" class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 md:inline-flex dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800" title="{{ __('chat.empty_view_hint') }}" aria-label="{{ __('chat.empty_view_hint') }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                </button>
                                <button type="button" id="internal-chat-private-delete-all" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-200/90 bg-red-50 text-red-700 shadow-sm transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950/70" title="{{ __('chat.delete_private_thread_title') }}" aria-label="{{ __('chat.clear_private_modal_title') }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </div>
                        </div>
                        <div id="internal-chat-private-messages" class="internal-chat-msg-scroll internal-chat-wallpaper min-h-0 flex-1 space-y-3 overflow-y-auto overflow-x-hidden overscroll-contain px-3 py-4 sm:px-4"></div>
                        <div id="internal-chat-private-context" class="hidden border-t border-dashed border-slate-200/80 bg-white/90 px-3 py-2 text-xs text-slate-600 dark:border-gray-600 dark:bg-gray-800/90 dark:text-gray-300"></div>
                        <div class="shrink-0 border-t border-slate-200/90 bg-white p-4 dark:border-gray-600 dark:bg-gray-950">
                            <div id="internal-chat-private-status" class="mb-2 hidden text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400"></div>
                            <div id="internal-chat-private-context-row" class="mb-2 flex items-center gap-2 rounded-xl bg-slate-50 px-2.5 py-2 dark:bg-gray-800/80">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-slate-600 dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                </span>
                                <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-2">
                                    <input type="checkbox" id="internal-chat-private-attach" class="h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700 dark:border-gray-600 dark:bg-gray-800" />
                                    <span id="internal-chat-private-attach-label" class="min-w-0 text-xs font-medium leading-snug text-slate-600 dark:text-gray-400"></span>
                                </label>
                            </div>
                            <form id="internal-chat-private-form" class="flex flex-col gap-1.5">
                                <div id="internal-chat-private-edit-banner" class="hidden flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-300/90 bg-amber-50 px-3 py-2 text-xs text-amber-950 dark:border-amber-800/70 dark:bg-amber-950/50 dark:text-amber-100" role="status">
                                    <span class="min-w-0 flex-1 font-bold leading-snug">{{ __('chat.edit_banner') }}</span>
                                    <button type="button" id="internal-chat-private-edit-cancel" class="shrink-0 rounded-lg border border-amber-400/80 bg-white px-2.5 py-1 text-xs font-bold text-amber-900 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-gray-900 dark:text-amber-200 dark:hover:bg-gray-800">{{ __('chat.cancel') }}</button>
                                </div>
                                <p id="internal-chat-private-sending" class="hidden text-center text-xs font-medium text-slate-500 dark:text-gray-400">{{ __('chat.sending') }}</p>
                                <div class="flex items-end gap-2 rounded-3xl border border-slate-200 bg-white px-1 py-1 shadow-sm dark:border-gray-600 dark:bg-gray-900">
                                    <textarea id="internal-chat-private-input" rows="2" maxlength="2000" placeholder="{{ __('chat.write_message') }}" class="internal-chat-composer-input min-h-11 flex-1 resize-none rounded-3xl border-0 bg-transparent px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-gray-100 dark:placeholder:text-gray-400 dark:caret-emerald-400"></textarea>
                                    <button type="submit" id="internal-chat-private-send" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-emerald-700 dark:hover:bg-emerald-600" title="{{ __('chat.send') }}" aria-label="{{ __('chat.send') }}">
                                        <svg class="h-5 w-5 {{ $chatSendIconRtl }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <p id="internal-chat-loading" class="hidden shrink-0 border-t border-slate-200 bg-white px-3 py-2 text-center text-xs font-medium text-slate-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ __('chat.loading') }}</p>

        <div id="internal-chat-delete-modal" class="pointer-events-auto absolute inset-0 z-[120] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm dark:bg-black/60" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="internal-chat-delete-modal-title">
            <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl dark:border-gray-600 dark:bg-gray-900">
                <p id="internal-chat-delete-modal-title" class="m-0 text-base font-bold text-slate-900 dark:text-gray-100">{{ __('chat.delete_message_title') }}</p>
                <p class="mt-2 mb-0 text-sm leading-relaxed text-slate-600 dark:text-gray-300">{{ __('chat.delete_message_body') }}</p>
                <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" id="internal-chat-delete-cancel" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">{{ __('chat.cancel') }}</button>
                    <button type="button" id="internal-chat-delete-confirm" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">{{ __('chat.delete') }}</button>
                </div>
            </div>
        </div>

        <div id="internal-chat-clear-all-modal" class="pointer-events-auto absolute inset-0 z-[120] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm dark:bg-black/60" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="internal-chat-clear-all-title">
            <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl dark:border-gray-600 dark:bg-gray-900">
                <p id="internal-chat-clear-all-title" class="m-0 text-base font-bold text-slate-900 dark:text-gray-100"></p>
                <p id="internal-chat-clear-all-desc" class="mt-2 mb-0 text-sm leading-relaxed text-slate-600 dark:text-gray-300"></p>
                <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" id="internal-chat-clear-all-cancel" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">{{ __('chat.cancel') }}</button>
                    <button type="button" id="internal-chat-clear-all-confirm" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">{{ __('chat.delete_all') }}</button>
                </div>
            </div>
        </div>
    </div>

    <button type="button" id="internal-chat-toggle" class="internal-chat-fab pointer-events-auto relative z-40 ms-auto flex h-[3.75rem] w-[3.75rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-slate-900 via-blue-900 to-teal-800 text-white shadow-xl shadow-blue-900/30 ring-4 ring-white/90 transition hover:scale-105 hover:shadow-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-300 max-md:me-2 max-md:mb-0 md:me-0 md:mb-0 dark:ring-gray-900/90 dark:focus-visible:ring-blue-500/50 touch-none md:touch-auto" aria-expanded="false" aria-controls="internal-chat-panel" title="{{ __('chat.fab_title') }}" aria-label="{{ __('chat.fab_title') }}">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337L5.05 21l1.395-3.72C5.512 15.042 5 13.574 5 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
        <span id="internal-chat-fab-badge" class="absolute -top-1 -start-1 hidden min-w-5 rounded-full border-2 border-white bg-red-500 px-1 text-center text-xs font-bold leading-5 text-white dark:border-gray-900">0</span>
    </button>
</div>
