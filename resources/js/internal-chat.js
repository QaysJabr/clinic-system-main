/**
 * دردشة داخلية (عام / خاص) — واجهة عائمة، تحديث تلقائي، سياق من مسار الصفحة.
 */

function uiLocale() {
    return window.AppI18n?.locale || document.documentElement.lang || 'ar';
}

function t(key, fallback) {
    return window.AppI18n?.messages?.[key] ?? fallback;
}

function readConfig() {
    const el = document.getElementById('internal-chat-config');
    if (!el?.textContent) return null;
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function parsePageContext() {
    const path = window.location.pathname;
    const ctx = { patient_id: null, invoice_id: null, appointment_id: null };
    let m = path.match(/^\/patients\/(\d+)/);
    if (m) ctx.patient_id = parseInt(m[1], 10);
    m = path.match(/^\/invoices\/(\d+)/);
    if (m) ctx.invoice_id = parseInt(m[1], 10);
    m = path.match(/^\/appointments\/(\d+)/);
    if (m) ctx.appointment_id = parseInt(m[1], 10);
    return ctx;
}

function hasAnyContext(ctx) {
    return Boolean(ctx.patient_id || ctx.invoice_id || ctx.appointment_id);
}

function contextDescription(ctx) {
    if (ctx.patient_id) return t('contextPatientAttached', 'Current patient will be attached.');
    if (ctx.invoice_id) return t('contextInvoiceAttached', 'Current invoice will be attached.');
    if (ctx.appointment_id) return t('contextAppointmentAttached', 'Current appointment will be attached.');
    return '';
}

function scrollToBottom(el) {
    if (!el) return;
    requestAnimationFrame(() => {
        el.scrollTop = el.scrollHeight;
    });
}

function formatTime(iso) {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        return d.toLocaleString(uiLocale(), { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short' });
    } catch {
        return '';
    }
}

/** وقت قصير داخل الفقاعة (شبيه بالواتساب) */
function formatBubbleTime(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleString(uiLocale(), { hour: '2-digit', minute: '2-digit' });
    } catch {
        return '';
    }
}

function formatListTime(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleString(uiLocale(), { hour: '2-digit', minute: '2-digit' });
    } catch {
        return '';
    }
}

function sameCalendarDay(a, b) {
    return (
        a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()
    );
}

function formatDaySeparator(iso) {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        const now = new Date();
        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);
        if (sameCalendarDay(d, now)) return t('chatToday', 'Today');
        if (sameCalendarDay(d, yesterday)) return t('chatYesterday', 'Yesterday');
        return d.toLocaleDateString(uiLocale(), {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: d.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
        });
    } catch {
        return '';
    }
}

function buildDateSeparatorEl(iso) {
    const el = document.createElement('div');
    el.className = 'internal-chat-date-sep flex justify-center py-2';
    const pill = document.createElement('span');
    pill.className =
        'rounded-full bg-white/90 px-3 py-0.5 text-[11px] font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200/80 dark:bg-gray-800/95 dark:text-gray-300 dark:ring-gray-600';
    pill.textContent = formatDaySeparator(iso);
    el.appendChild(pill);
    return el;
}

function mergeMessagesById(existing, incoming) {
    const map = new Map();
    for (const m of existing) map.set(m.id, m);
    for (const m of incoming) map.set(m.id, m);
    return Array.from(map.values()).sort((a, b) => a.id - b.id);
}

function upsertMessageInList(messages, updated) {
    if (!updated?.id) return messages ?? [];
    const list = messages ?? [];
    const idx = list.findIndex((m) => m.id === updated.id);
    if (idx === -1) return mergeMessagesById(list, [updated]);
    const copy = list.slice();
    copy[idx] = updated;
    return copy;
}

function removeMessageFromList(messages, id) {
    return (messages ?? []).filter((m) => m.id !== id);
}

function lastMessageId(messages) {
    if (!messages?.length) return null;
    return messages[messages.length - 1].id;
}

function fillAvatarElement(el, user, sizeClass = 'h-11 w-11') {
    el.replaceChildren();
    const name = user?.name ?? '';
    const url = user?.avatar_url || '';
    const initialsClass = `flex ${sizeClass} shrink-0 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white shadow-sm ring-1 ring-white/30 dark:bg-emerald-800`;
    if (!url) {
        el.className = initialsClass;
        el.textContent = initialsFromName(name);
        return;
    }
    const img = document.createElement('img');
    img.src = url;
    img.alt = name;
    img.className = `${sizeClass} shrink-0 rounded-full object-cover shadow-sm ring-1 ring-white/30 dark:ring-gray-700`;
    img.loading = 'lazy';
    img.addEventListener('error', () => {
        el.className = initialsClass;
        el.textContent = initialsFromName(name);
        el.replaceChildren();
    });
    el.appendChild(img);
}

function animateMessageEnter(el) {
    el.classList.add('opacity-0', 'translate-y-1', 'transition', 'duration-200', 'ease-out');
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-1');
        });
    });
}

/** أحرف أولية من الاسم (دعم العربية) */
function initialsFromName(name) {
    if (!name || typeof name !== 'string') return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length >= 2) {
        return parts[0].charAt(0) + parts[1].charAt(0);
    }
    return name.trim().slice(0, 2) || '?';
}

/** أيقونات تعديل/حذف تحت الفقاعة — أوضح من النص في الوضعين الفاتح والداكن */
function appendIconAction(container, title, svgInner, extraClass, onClick) {
    const b = document.createElement('button');
    b.type = 'button';
    b.title = title;
    b.setAttribute('aria-label', title);
    b.className =
        'inline-flex min-h-7 min-w-7 items-center justify-center rounded-md p-1 text-slate-600 transition hover:bg-slate-200/90 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-white ' +
        extraClass;
    b.innerHTML =
        '<svg class="pointer-events-none h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">' +
        svgInner +
        '</svg>';
    b.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        onClick();
    });
    container.appendChild(b);
}

function appendContextBlock(bubble, msg, mine) {
    if (!msg.context?.url) return;
    const ctxBox = document.createElement('div');
    ctxBox.className =
        'mt-2 rounded-lg border px-2 py-1.5 text-xs ' +
        (mine
            ? 'border-emerald-300/50 bg-emerald-50/60 dark:border-emerald-700/50 dark:bg-emerald-950/60'
            : 'border-slate-200 bg-slate-50 dark:border-gray-600 dark:bg-gray-800/90');
    const lbl = document.createElement('p');
    lbl.className = 'm-0 mb-0.5 text-[11px] font-bold text-slate-700 dark:text-gray-200';
    lbl.textContent = msg.context.label ?? '';
    ctxBox.appendChild(lbl);
    const a = document.createElement('a');
    a.href = msg.context.url;
    a.setAttribute('data-spa', '');
    a.className = 'text-[11px] font-bold text-emerald-800 hover:underline dark:text-sky-300 dark:hover:text-sky-200';
    a.textContent = msg.context.link_label ?? t('chatViewLink', 'View');
    ctxBox.appendChild(a);
    bubble.appendChild(ctxBox);
}

/** أزرار تعديل/حذف داخل زاوية الفقاعة (تظهر عند المرور) */
function appendBubbleCornerActions(bubble, msg) {
    const hasEdit = msg.is_mine;
    const hasDelete = msg.can_delete;
    if (!hasEdit && !hasDelete) return;

    const menu = document.createElement('div');
    menu.className =
        'internal-chat-bubble-menu absolute top-1 end-1 z-10 flex items-center gap-0.5 rounded-lg bg-white/95 p-0.5 opacity-0 shadow-md ring-1 ring-slate-200/90 transition-opacity duration-150 group-hover/bubble:opacity-100 dark:bg-gray-800/95 dark:ring-gray-600';

    if (hasEdit) {
        const editTitle = msg.can_edit ? t('chatEditMsg', 'Edit') : t('chatEditMsgWindow', 'Edit (available for 5 minutes after sending)');
        appendIconAction(
            menu,
            editTitle,
            '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>',
            msg.can_edit ? '' : 'opacity-70',
            () => {
                document.dispatchEvent(
                    new CustomEvent('internal-chat:edit-message', {
                        detail: { id: msg.id, body: msg.body, can_edit: msg.can_edit !== false },
                    }),
                );
            },
        );
    }
    if (hasDelete) {
        appendIconAction(
            menu,
            t('chatDelete', 'Delete'),
            '<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>',
            'hover:text-red-600 dark:hover:text-red-400',
            () => {
                document.dispatchEvent(new CustomEvent('internal-chat:delete-message', { detail: { id: msg.id } }));
            },
        );
    }

    bubble.appendChild(menu);
}

/** علامات واتساب: ✓ رمادي = أُرسلت، ✓✓ أزرق = قرأها الطرف الآخر */
function appendPrivateReadTicks(foot, msg) {
    const read = Boolean(msg.read_at);
    const ticksWrap = document.createElement('span');
    ticksWrap.className = 'inline-flex select-none items-center leading-none';
    ticksWrap.setAttribute('aria-label', read ? t('chatReadReceipt', 'Read') : t('chatSentReceipt', 'Sent'));
    if (read) {
        const dbl = document.createElement('span');
        dbl.className =
            'text-base font-semibold tracking-tighter text-sky-600 dark:text-sky-400';
        dbl.textContent = '✓✓';
        ticksWrap.appendChild(dbl);
    } else {
        const one = document.createElement('span');
        one.className = 'text-base text-slate-400 dark:text-slate-500';
        one.textContent = '✓';
        ticksWrap.appendChild(one);
    }
    foot.appendChild(ticksWrap);
}

/** غرفة عامة — فقاعات شبيهة بالواتساب */
function buildGeneralMessageRow(msg) {
    const mine = msg.is_mine;
    const wrap = document.createElement('div');
    wrap.className = `flex w-full max-w-full gap-2 ${mine ? 'justify-start' : 'justify-end'}`;

    if (!mine && msg.sender) {
        const av = document.createElement('span');
        av.className = 'mt-1 shrink-0 self-end';
        fillAvatarElement(av, msg.sender, 'h-8 w-8');
        wrap.appendChild(av);
    }

    const col = document.createElement('div');
    col.className = 'flex min-h-0 min-w-0 max-w-[88%] flex-col';

    const bubble = document.createElement('div');
    bubble.className =
        'group/bubble relative ' +
        (mine
            ? 'rounded-2xl rounded-tr-sm border border-emerald-200/80 bg-emerald-100 px-3 py-2 pe-10 text-sm leading-relaxed text-slate-900 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-950/85 dark:text-emerald-50 dark:shadow-emerald-950/40'
            : 'rounded-2xl rounded-tl-sm border border-slate-200/90 bg-white px-3 py-2 pe-10 text-sm leading-relaxed text-slate-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:shadow-black/20');

    if (!mine) {
        const head = document.createElement('div');
        head.className = 'mb-0.5 flex flex-wrap items-baseline gap-1.5';
        const who = document.createElement('span');
        who.className = 'text-[11px] font-bold text-slate-800 dark:text-gray-100';
        who.textContent = msg.sender?.name ?? t('emDash', '—');
        head.appendChild(who);
        if (msg.sender?.role_label) {
            const role = document.createElement('span');
            role.className = 'rounded bg-slate-100 px-1 py-0.5 text-[9px] font-bold text-slate-500 dark:bg-gray-700 dark:text-gray-400';
            role.textContent = msg.sender.role_label;
            head.appendChild(role);
        }
        bubble.appendChild(head);
    }

    const body = document.createElement('p');
    body.className =
        'm-0 max-w-full break-words whitespace-pre-wrap leading-relaxed ' +
        (mine ? 'text-slate-900 dark:text-emerald-50' : 'text-slate-900 dark:text-gray-100');
    body.textContent = msg.body ?? '';
    bubble.appendChild(body);

    if (msg.edited_at) {
        const ed = document.createElement('p');
        ed.className = 'mt-1 mb-0 text-[10px] font-medium text-slate-500 dark:text-gray-400';
        ed.textContent = t('chatEdited', 'Edited');
        bubble.appendChild(ed);
    }

    appendContextBlock(bubble, msg, mine);

    const foot = document.createElement('div');
    foot.className = 'mt-1 flex min-w-0 flex-wrap items-end justify-end gap-1';
    const time = document.createElement('time');
    time.className =
        'text-[10px] font-medium ' +
        (mine ? 'text-slate-500 dark:text-emerald-100' : 'text-slate-500 dark:text-gray-300');
    time.dateTime = msg.created_at ?? '';
    time.textContent = formatBubbleTime(msg.created_at);
    foot.appendChild(time);
    bubble.appendChild(foot);

    appendBubbleCornerActions(bubble, msg);

    col.appendChild(bubble);
    wrap.appendChild(col);
    return wrap;
}

/** خاص — فقاعات + ✓ / ✓✓ */
function buildPrivateMessageRow(msg) {
    const mine = msg.is_mine;
    const wrap = document.createElement('div');
    wrap.className = `flex w-full max-w-full gap-2 ${mine ? 'justify-start' : 'justify-end'}`;

    if (!mine && msg.sender) {
        const av = document.createElement('span');
        av.className = 'mt-1 shrink-0 self-end';
        fillAvatarElement(av, msg.sender, 'h-8 w-8');
        wrap.appendChild(av);
    }

    const col = document.createElement('div');
    col.className = 'flex min-h-0 min-w-0 max-w-[88%] flex-col';

    const bubble = document.createElement('div');
    bubble.className =
        'group/bubble relative ' +
        (mine
            ? 'rounded-2xl rounded-tr-sm border border-emerald-200/80 bg-emerald-100 px-3 py-2 pe-10 text-sm leading-relaxed text-slate-900 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-950/85 dark:text-emerald-50 dark:shadow-emerald-950/40'
            : 'rounded-2xl rounded-tl-sm border border-slate-200/90 bg-white px-3 py-2 pe-10 text-sm leading-relaxed text-slate-900 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:shadow-black/20');

    if (!mine) {
        const who = document.createElement('p');
        who.className = 'm-0 mb-0.5 text-[11px] font-bold text-slate-700 dark:text-gray-200';
        who.textContent = msg.sender?.name ?? t('emDash', '—');
        bubble.appendChild(who);
    }

    const body = document.createElement('p');
    body.className =
        'm-0 max-w-full break-words whitespace-pre-wrap leading-relaxed ' +
        (mine ? 'text-slate-900 dark:text-emerald-50' : 'text-slate-900 dark:text-gray-100');
    body.textContent = msg.body ?? '';
    bubble.appendChild(body);

    if (msg.edited_at) {
        const ed = document.createElement('p');
        ed.className = 'mt-1 mb-0 text-[10px] font-medium text-slate-500 dark:text-gray-400';
        ed.textContent = t('chatEdited', 'Edited');
        bubble.appendChild(ed);
    }

    appendContextBlock(bubble, msg, mine);

    const foot = document.createElement('div');
    foot.className = 'mt-1 flex min-w-0 flex-wrap items-end justify-end gap-1.5';
    const time = document.createElement('time');
    time.className =
        'text-[10px] font-medium ' +
        (mine ? 'text-slate-500 dark:text-emerald-100' : 'text-slate-500 dark:text-gray-300');
    time.dateTime = msg.created_at ?? '';
    time.textContent = formatBubbleTime(msg.created_at);
    foot.appendChild(time);
    if (mine) {
        appendPrivateReadTicks(foot, msg);
    }
    bubble.appendChild(foot);

    appendBubbleCornerActions(bubble, msg);

    col.appendChild(bubble);
    wrap.appendChild(col);
    return wrap;
}

function buildMessageBubble(msg, isGeneralRoom) {
    return isGeneralRoom ? buildGeneralMessageRow(msg) : buildPrivateMessageRow(msg);
}

const CHAT_FAB_POS_STORAGE_KEY = 'clinic-internal-chat-fab-pos';
const CHAT_FAB_DRAG_THRESHOLD_PX = 6;
const CHAT_FAB_DESKTOP_MQ = '(min-width: 768px)';
const CHAT_FAB_POSITION_CLASSES = [
    'md:bottom-4',
    'md:left-5',
    'md:right-5',
    'md:left-auto',
    'md:right-auto',
];

/**
 * FAB عائم قابل للسحب على الشاشات الكبيرة — يحفظ الموضع في localStorage.
 * @returns {{ shouldSuppressClick: () => boolean }}
 */
function initChatFabDrag(root, toggle) {
    const noop = { shouldSuppressClick: () => false };
    if (!root || !toggle) return noop;

    const desktopMq = window.matchMedia(CHAT_FAB_DESKTOP_MQ);
    const defaultPositionClasses = CHAT_FAB_POSITION_CLASSES.filter((c) => root.classList.contains(c));
    let suppressNextClick = false;

    const isDesktop = () => desktopMq.matches;

    const loadSavedPosition = () => {
        try {
            const raw = localStorage.getItem(CHAT_FAB_POS_STORAGE_KEY);
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            if (typeof parsed?.x !== 'number' || typeof parsed?.y !== 'number') return null;
            return { x: parsed.x, y: parsed.y };
        } catch {
            return null;
        }
    };

    const savePosition = (x, y) => {
        try {
            localStorage.setItem(CHAT_FAB_POS_STORAGE_KEY, JSON.stringify({ x, y }));
        } catch {
            /* ignore */
        }
    };

    const clampToViewport = (x, y) => {
        const pad = 8;
        const rect = root.getBoundingClientRect();
        const w = rect.width || 56;
        const h = rect.height || 56;
        const maxX = Math.max(pad, window.innerWidth - w - pad);
        const maxY = Math.max(pad, window.innerHeight - h - pad);
        return {
            x: Math.min(Math.max(pad, x), maxX),
            y: Math.min(Math.max(pad, y), maxY),
        };
    };

    const applyCustomPosition = (x, y) => {
        const pos = clampToViewport(x, y);
        CHAT_FAB_POSITION_CLASSES.forEach((c) => root.classList.remove(c));
        root.classList.add('internal-chat-root--positioned');
        root.style.left = `${pos.x}px`;
        root.style.top = `${pos.y}px`;
        root.style.right = 'auto';
        root.style.bottom = 'auto';
        return pos;
    };

    const clearCustomPosition = () => {
        root.classList.remove('internal-chat-root--positioned');
        root.style.left = '';
        root.style.top = '';
        root.style.right = '';
        root.style.bottom = '';
        defaultPositionClasses.forEach((c) => root.classList.add(c));
    };

    const restoreSavedPosition = () => {
        if (!isDesktop()) {
            clearCustomPosition();
            return;
        }
        const saved = loadSavedPosition();
        if (!saved) return;
        requestAnimationFrame(() => {
            applyCustomPosition(saved.x, saved.y);
        });
    };

    restoreSavedPosition();

    desktopMq.addEventListener('change', () => {
        if (isDesktop()) {
            restoreSavedPosition();
        } else {
            clearCustomPosition();
        }
    });

    window.addEventListener('resize', () => {
        if (!isDesktop() || !root.classList.contains('internal-chat-root--positioned')) return;
        const rect = root.getBoundingClientRect();
        const pos = applyCustomPosition(rect.left, rect.top);
        savePosition(pos.x, pos.y);
    });

    let dragging = false;
    let moved = false;
    let startPointerX = 0;
    let startPointerY = 0;
    let originLeft = 0;
    let originTop = 0;

    const onPointerDown = (e) => {
        if (!isDesktop() || e.button !== 0) return;
        dragging = true;
        moved = false;
        startPointerX = e.clientX;
        startPointerY = e.clientY;
        const rect = root.getBoundingClientRect();
        originLeft = rect.left;
        originTop = rect.top;
        if (!root.classList.contains('internal-chat-root--positioned')) {
            applyCustomPosition(originLeft, originTop);
        }
        toggle.setPointerCapture(e.pointerId);
        toggle.classList.add('internal-chat-fab--grabbing');
    };

    const onPointerMove = (e) => {
        if (!dragging) return;
        const dx = e.clientX - startPointerX;
        const dy = e.clientY - startPointerY;
        if (!moved && Math.hypot(dx, dy) < CHAT_FAB_DRAG_THRESHOLD_PX) return;
        if (!moved) {
            moved = true;
            e.preventDefault();
        }
        applyCustomPosition(originLeft + dx, originTop + dy);
    };

    const finishDrag = (e) => {
        if (!dragging) return;
        dragging = false;
        toggle.classList.remove('internal-chat-fab--grabbing');
        try {
            toggle.releasePointerCapture(e.pointerId);
        } catch {
            /* ignore */
        }
        if (moved) {
            const rect = root.getBoundingClientRect();
            const pos = clampToViewport(rect.left, rect.top);
            root.style.left = `${pos.x}px`;
            root.style.top = `${pos.y}px`;
            savePosition(pos.x, pos.y);
            suppressNextClick = true;
        }
        moved = false;
    };

    toggle.addEventListener('pointerdown', onPointerDown);
    toggle.addEventListener('pointermove', onPointerMove);
    toggle.addEventListener('pointerup', finishDrag);
    toggle.addEventListener('pointercancel', finishDrag);

    return {
        shouldSuppressClick: () => {
            if (!suppressNextClick) return false;
            suppressNextClick = false;
            return true;
        },
    };
}

export function initInternalChat() {
    const root = document.getElementById('internal-chat-root');
    if (!root || root.dataset.internalChatInit === '1') return;
    root.dataset.internalChatInit = '1';

    const config = readConfig();
    if (!config) return;

    const panel = document.getElementById('internal-chat-panel');
    const toggle = document.getElementById('internal-chat-toggle');
    const fabDrag = initChatFabDrag(root, toggle);
    const closeBtn = document.getElementById('internal-chat-close');
    const refreshBtn = document.getElementById('internal-chat-refresh');
    const fabBadge = document.getElementById('internal-chat-fab-badge');
    const loadingEl = document.getElementById('internal-chat-loading');
    const tabGeneral = document.getElementById('internal-chat-tab-general');
    const tabPrivate = document.getElementById('internal-chat-tab-private');
    const bodyGeneral = document.getElementById('internal-chat-body-general');
    const bodyPrivate = document.getElementById('internal-chat-body-private');
    const generalMessages = document.getElementById('internal-chat-general-messages');
    const privateMessages = document.getElementById('internal-chat-private-messages');
    const userList = document.getElementById('internal-chat-user-list');
    const userSearch = document.getElementById('internal-chat-user-search');
    const generalForm = document.getElementById('internal-chat-general-form');
    const privateForm = document.getElementById('internal-chat-private-form');
    const generalInput = document.getElementById('internal-chat-general-input');
    const privateInput = document.getElementById('internal-chat-private-input');
    const generalAttach = document.getElementById('internal-chat-general-attach');
    const privateAttach = document.getElementById('internal-chat-private-attach');
    const generalCtxBox = document.getElementById('internal-chat-general-context');
    const privateCtxBox = document.getElementById('internal-chat-private-context');
    const generalAttachLabel = document.getElementById('internal-chat-general-attach-label');
    const privateAttachLabel = document.getElementById('internal-chat-private-attach-label');
    const generalStatus = document.getElementById('internal-chat-general-status');
    const privateStatus = document.getElementById('internal-chat-private-status');
    const privateEmpty = document.getElementById('internal-chat-private-empty');
    const privateThread = document.getElementById('internal-chat-private-thread');
    const privateHeader = document.getElementById('internal-chat-private-header');
    const privatePartnerName = document.getElementById('internal-chat-private-partner-name');
    const privateMain = document.getElementById('internal-chat-private-main');
    const privateSidebar = document.getElementById('internal-chat-private-sidebar');
    const privateBack = document.getElementById('internal-chat-private-back');
    const privatePartnerAvatar = document.getElementById('internal-chat-private-partner-avatar');
    const privateStatusLine = document.getElementById('internal-chat-private-status-line');
    const generalSendBtn = document.getElementById('internal-chat-general-send');
    const privateSendBtn = document.getElementById('internal-chat-private-send');
    const generalSendingEl = document.getElementById('internal-chat-general-sending');
    const privateSendingEl = document.getElementById('internal-chat-private-sending');
    const deleteModal = document.getElementById('internal-chat-delete-modal');
    const deleteConfirmBtn = document.getElementById('internal-chat-delete-confirm');
    const deleteCancelBtn = document.getElementById('internal-chat-delete-cancel');
    const generalEditBanner = document.getElementById('internal-chat-general-edit-banner');
    const generalEditCancelBtn = document.getElementById('internal-chat-general-edit-cancel');
    const privateEditBanner = document.getElementById('internal-chat-private-edit-banner');
    const privateEditCancelBtn = document.getElementById('internal-chat-private-edit-cancel');
    const privateDeleteAllBtn = document.getElementById('internal-chat-private-delete-all');
    const privateEmptyViewBtn = document.getElementById('internal-chat-private-empty-view');
    const generalDeleteAllBtn = document.getElementById('internal-chat-general-delete-all');
    const clearAllModal = document.getElementById('internal-chat-clear-all-modal');
    const clearAllTitleEl = document.getElementById('internal-chat-clear-all-title');
    const clearAllDescEl = document.getElementById('internal-chat-clear-all-desc');
    const clearAllConfirmBtn = document.getElementById('internal-chat-clear-all-confirm');
    const clearAllCancelBtn = document.getElementById('internal-chat-clear-all-cancel');

    /** @type {Map<number, { preview: string, time: string }>} معاينة محلية بعد الإرسال الفوري */
    const conversationMeta = new Map();

    let generalState = { messages: [], hasMore: false };
    let privateState = { messages: [], hasMore: false };
    let generalLoadingOlder = false;
    let privateLoadingOlder = false;

    let open = false;
    let activeTab = 'general';
    /** @type {number|null} */
    let selectedPrivateUserId = null;
    /** @type {number|null} */
    let pendingDeleteId = null;
    /** @type {number|null} */
    let editingMessageId = null;
    /** @type {'general' | 'private' | null} */
    let pendingClearAllScope = null;
    let pollTimer = null;
    /** @type {ReturnType<typeof setTimeout>|null} */
    let searchDebounce = null;

    function setLoading(v) {
        if (!loadingEl) return;
        loadingEl.classList.toggle('hidden', !v);
    }

    function isNarrowPrivate() {
        return typeof window.matchMedia === 'function' && window.matchMedia('(max-width: 767px)').matches;
    }

    function syncPrivateLayout() {
        if (!privateMain || !privateSidebar) return;
        const narrow = isNarrowPrivate();
        if (narrow) {
            if (selectedPrivateUserId) {
                privateSidebar.classList.add('hidden');
                privateMain.classList.remove('max-md:hidden');
                privateBack?.classList.remove('hidden');
            } else {
                privateSidebar.classList.remove('hidden');
                privateMain.classList.add('max-md:hidden');
                privateBack?.classList.add('hidden');
            }
        } else {
            privateSidebar.classList.remove('hidden');
            privateMain.classList.remove('max-md:hidden');
            privateBack?.classList.add('hidden');
        }
    }

    function clearEditMode() {
        editingMessageId = null;
        if (generalEditBanner) generalEditBanner.classList.add('hidden');
        if (privateEditBanner) privateEditBanner.classList.add('hidden');
        if (generalInput) generalInput.value = '';
        if (privateInput) privateInput.value = '';
        const sendLbl = t('chatSend', 'Send');
        if (generalSendBtn) {
            generalSendBtn.setAttribute('title', sendLbl);
            generalSendBtn.setAttribute('aria-label', sendLbl);
        }
        if (privateSendBtn) {
            privateSendBtn.setAttribute('title', sendLbl);
            privateSendBtn.setAttribute('aria-label', sendLbl);
        }
    }

    function openDeleteModal(id) {
        pendingDeleteId = id;
        if (!deleteModal) return;
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        deleteModal.setAttribute('aria-hidden', 'false');
        deleteConfirmBtn?.focus();
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        if (!deleteModal) return;
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        deleteModal.setAttribute('aria-hidden', 'true');
    }

    function openClearAllModal(scope) {
        pendingClearAllScope = scope;
        if (!clearAllModal || !clearAllTitleEl || !clearAllDescEl) return;
        if (scope === 'general') {
            clearAllTitleEl.textContent = t('chatClearGeneralTitle', 'Delete entire general room?');
            clearAllDescEl.textContent = t(
                'chatClearGeneralBody',
                'All messages in the team room will be removed for the clinic. This cannot be undone.',
            );
        } else {
            clearAllTitleEl.textContent = t('chatClearPrivateTitle', 'Delete entire direct conversation?');
            clearAllDescEl.textContent = t(
                'chatClearPrivateBody',
                'All messages between you and this colleague will be removed (hidden for both sides).',
            );
        }
        clearAllModal.classList.remove('hidden');
        clearAllModal.classList.add('flex');
        clearAllModal.setAttribute('aria-hidden', 'false');
        clearAllConfirmBtn?.focus();
    }

    function closeClearAllModal() {
        pendingClearAllScope = null;
        if (!clearAllModal) return;
        clearAllModal.classList.add('hidden');
        clearAllModal.classList.remove('flex');
        clearAllModal.setAttribute('aria-hidden', 'true');
    }

    function startEditingMessage(id, body) {
        const text = typeof body === 'string' ? body : '';
        editingMessageId = id;
        if (activeTab === 'general') {
            if (generalInput) generalInput.value = text;
            generalEditBanner?.classList.remove('hidden');
            privateEditBanner?.classList.add('hidden');
            if (privateInput) privateInput.value = '';
            generalInput?.focus();
            generalSendBtn?.setAttribute('title', t('chatSaveEdit', 'Save edit'));
            generalSendBtn?.setAttribute('aria-label', t('chatSaveEdit', 'Save edit'));
            privateSendBtn?.setAttribute('title', t('chatSend', 'Send'));
            privateSendBtn?.setAttribute('aria-label', t('chatSend', 'Send'));
        } else {
            if (privateInput) privateInput.value = text;
            privateEditBanner?.classList.remove('hidden');
            generalEditBanner?.classList.add('hidden');
            if (generalInput) generalInput.value = '';
            privateInput?.focus();
            privateSendBtn?.setAttribute('title', t('chatSaveEdit', 'Save edit'));
            privateSendBtn?.setAttribute('aria-label', t('chatSaveEdit', 'Save edit'));
            generalSendBtn?.setAttribute('title', t('chatSend', 'Send'));
            generalSendBtn?.setAttribute('aria-label', t('chatSend', 'Send'));
        }
    }

    function clearPrivateSelection() {
        clearEditMode();
        selectedPrivateUserId = null;
        privateState = { messages: [], hasMore: false };
        privateThread?.classList.add('hidden');
        privateEmpty?.classList.remove('hidden');
        privateHeader?.classList.add('hidden');
        privateMessages?.replaceChildren();
        syncPrivateLayout();
        void (async () => {
            try {
                const users = await loadUsers(userSearch?.value?.trim() ?? '');
                renderUserList(users);
            } catch {
                renderUserList([]);
            }
        })();
    }

    function setGeneralSending(v) {
        if (generalSendBtn) generalSendBtn.disabled = v;
        if (generalInput) generalInput.disabled = v;
        generalSendingEl?.classList.toggle('hidden', !v);
    }

    function setPrivateSending(v) {
        if (privateSendBtn) privateSendBtn.disabled = v;
        if (privateInput) privateInput.disabled = v;
        privateSendingEl?.classList.toggle('hidden', !v);
    }

    function bindEnterToSend(textarea, form) {
        if (!textarea || !form) return;
        textarea.addEventListener('keydown', (ev) => {
            if (ev.key !== 'Enter' || ev.shiftKey) return;
            ev.preventDefault();
            if (textarea.disabled) return;
            form.requestSubmit();
        });
    }

    function setTabUi() {
        [tabGeneral, tabPrivate].forEach((t) => {
            if (!t) return;
            const on = t.dataset.tab === activeTab;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (bodyGeneral) bodyGeneral.classList.toggle('hidden', activeTab !== 'general');
        if (bodyPrivate) bodyPrivate.classList.toggle('hidden', activeTab !== 'private');
        if (activeTab === 'private') syncPrivateLayout();
    }

    const generalContextRow = document.getElementById('internal-chat-general-context-row');
    const privateContextRow = document.getElementById('internal-chat-private-context-row');

    function updateContextUi() {
        const ctx = parsePageContext();
        const has = hasAnyContext(ctx);
        const desc = contextDescription(ctx);
        if (generalAttach) {
            generalAttach.disabled = !has;
            generalAttach.checked = has && generalAttach.checked;
        }
        if (privateAttach) {
            privateAttach.disabled = !has;
            privateAttach.checked = has && privateAttach.checked;
        }
        if (generalAttachLabel) {
            generalAttachLabel.textContent = has ? t('chatAttachPage', 'Attach this page context to the message') : '';
        }
        if (privateAttachLabel) {
            privateAttachLabel.textContent = has ? t('chatAttachShort', 'Attach current context') : '';
        }
        [generalContextRow, privateContextRow].forEach((row) => {
            if (!row) return;
            row.classList.toggle('hidden', !has);
        });
        if (generalCtxBox) {
            generalCtxBox.classList.toggle('hidden', !has);
            generalCtxBox.textContent = desc;
        }
        if (privateCtxBox) {
            privateCtxBox.classList.toggle('hidden', !has);
            privateCtxBox.textContent = desc;
        }
    }

    async function api(url, opts = {}) {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': config.csrf,
            ...(opts.headers || {}),
        };
        if (opts.body && !(opts.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        const res = await fetch(url, { ...opts, headers });
        const text = await res.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : null;
        } catch {
            data = { message: text };
        }
        if (!res.ok) {
            const msg = data?.message ?? res.statusText;
            throw new Error(typeof msg === 'string' ? msg : t('chatRequestFail', 'Request failed'));
        }
        return data;
    }

    /**
     * @param {{ preserveScroll?: boolean, showLoadOlder?: boolean, onLoadOlder?: () => void, isPrivate?: boolean }} [opts]
     */
    function paintMessageStream(container, messages, isGeneral, opts = {}) {
        if (!container) return;
        const preserve = Boolean(opts.preserveScroll);
        const prevHeight = container.scrollHeight;
        const prevTop = container.scrollTop;

        container.replaceChildren();

        if (!messages?.length) {
            const wrap = document.createElement('div');
            wrap.dataset.internalChatEmpty = '1';
            wrap.className = 'flex flex-col items-center justify-center gap-2 px-4 py-14 text-center';
            const icon = document.createElement('span');
            icon.className =
                'flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-md ring-1 ring-slate-200/80 text-slate-400 dark:bg-gray-800 dark:ring-gray-700 dark:text-gray-500';
            icon.innerHTML = isGeneral
                ? '<svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a23.922 23.922 0 00-1.556-3.511m-1.055 2.503c-.095.293-.163.6-.208.918m-12.086 6.39l6.848 6.848a18.075 18.075 0 002.849-2.849L3.364 3.364a18.075 18.075 0 00-2.849 2.849zm6.39 6.39l-.849.849m0 0L3 21l3.39-3.39m0 0l.849-.849m0 0L21 3"/></svg>'
                : '<svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>';
            const t1 = document.createElement('p');
            t1.className = 'm-0 text-sm font-bold text-slate-700 dark:text-gray-300';
            t1.textContent = isGeneral
                ? t('chatGeneralEmptyTitle', 'No messages yet')
                : t('chatPrivateEmptyTitle', 'No messages yet');
            const t2 = document.createElement('p');
            t2.className = 'm-0 max-w-xs text-xs leading-relaxed text-slate-500 dark:text-gray-500';
            t2.textContent = isGeneral
                ? t('chatGeneralEmptyHint', 'Say hello to your team — everyone in the clinic sees these messages.')
                : t('chatPrivateEmptyHint', 'Send the first message below.');
            wrap.appendChild(icon);
            wrap.appendChild(t1);
            wrap.appendChild(t2);
            container.appendChild(wrap);
            return;
        }

        if (opts.showLoadOlder && typeof opts.onLoadOlder === 'function') {
            const loadBtn = document.createElement('button');
            loadBtn.type = 'button';
            loadBtn.className =
                'mx-auto mb-2 block rounded-full bg-white/95 px-4 py-1.5 text-[11px] font-bold text-emerald-800 shadow-sm ring-1 ring-slate-200/90 transition hover:bg-white dark:bg-gray-800/95 dark:text-emerald-300 dark:ring-gray-600';
            loadBtn.textContent = t('chatLoadOlder', 'Load older messages');
            loadBtn.addEventListener('click', opts.onLoadOlder);
            container.appendChild(loadBtn);
        }

        let lastDay = '';
        messages.forEach((m) => {
            const dayKey = m.created_at ? new Date(m.created_at).toDateString() : '';
            if (dayKey && dayKey !== lastDay) {
                lastDay = dayKey;
                container.appendChild(buildDateSeparatorEl(m.created_at));
            }
            const row = buildMessageBubble(m, isGeneral);
            animateMessageEnter(row);
            container.appendChild(row);
        });

        if (preserve) {
            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight - prevHeight + prevTop;
            });
        } else {
            scrollToBottom(container);
        }
    }

    function renderGeneralMessages(messages, opts = {}) {
        paintMessageStream(generalMessages, messages, true, {
            preserveScroll: opts.preserveScroll,
            showLoadOlder: generalState.hasMore && messages?.length > 0,
            onLoadOlder: () => void loadOlderGeneral(),
        });
    }

    function renderPrivateMessages(messages, opts = {}) {
        paintMessageStream(privateMessages, messages, false, {
            preserveScroll: opts.preserveScroll,
            showLoadOlder: privateState.hasMore && messages?.length > 0,
            onLoadOlder: () => void loadOlderPrivate(),
        });
        if (selectedPrivateUserId && messages?.length) {
            const last = messages[messages.length - 1];
            conversationMeta.set(selectedPrivateUserId, {
                preview: (last.body || '').replace(/\s+/g, ' ').trim().slice(0, 72),
                time: last.created_at,
            });
        }
    }

    async function loadOlderGeneral() {
        if (generalLoadingOlder || !generalState.hasMore || !generalState.messages.length) return;
        generalLoadingOlder = true;
        const before = generalState.messages[0].id;
        try {
            const url = new URL(config.generalUrl, window.location.origin);
            url.searchParams.set('before', String(before));
            const data = await api(url.toString());
            if (data.messages?.length) {
                generalState.messages = mergeMessagesById(data.messages, generalState.messages);
                generalState.hasMore = Boolean(data.has_more);
                renderGeneralMessages(generalState.messages, { preserveScroll: true });
            } else {
                generalState.hasMore = false;
            }
        } catch {
            /* ignore */
        } finally {
            generalLoadingOlder = false;
        }
    }

    async function loadOlderPrivate() {
        if (privateLoadingOlder || !privateState.hasMore || !privateState.messages.length || !selectedPrivateUserId) {
            return;
        }
        privateLoadingOlder = true;
        const before = privateState.messages[0].id;
        try {
            const url = new URL(`${config.privateBaseUrl}/${selectedPrivateUserId}`, window.location.origin);
            url.searchParams.set('before', String(before));
            const data = await api(url.toString());
            if (data.messages?.length) {
                privateState.messages = mergeMessagesById(data.messages, privateState.messages);
                privateState.hasMore = Boolean(data.has_more);
                renderPrivateMessages(privateState.messages, { preserveScroll: true });
            } else {
                privateState.hasMore = false;
            }
        } catch {
            /* ignore */
        } finally {
            privateLoadingOlder = false;
        }
    }

    /**
     * @param {{ silent?: boolean }} [opts]
     */
    async function loadGeneral(opts = {}) {
        const silent = Boolean(opts.silent);
        const forceFull = Boolean(opts.forceFull);
        const url = new URL(config.generalUrl, window.location.origin);
        if (silent && !forceFull && generalState.messages.length) {
            url.searchParams.set('after', String(lastMessageId(generalState.messages)));
        }
        if (!silent) setLoading(true);
        try {
            const data = await api(url.toString());
            if (silent && !forceFull && url.searchParams.has('after')) {
                if (data.messages?.length) {
                    generalState.messages = mergeMessagesById(generalState.messages, data.messages);
                    renderGeneralMessages(generalState.messages, { preserveScroll: true });
                }
            } else {
                generalState.messages = data.messages ?? [];
                generalState.hasMore = Boolean(data.has_more);
                renderGeneralMessages(generalState.messages);
            }
        } catch {
            if (!silent) {
                generalState = { messages: [], hasMore: false };
                renderGeneralMessages([]);
            }
        } finally {
            if (!silent) setLoading(false);
        }
    }

    /** إدراج رسالة واحدة بعد الإرسال (بدون إعادة تحميل كامل) */
    function appendIncomingGeneralMessage(msg) {
        if (!generalMessages || !msg) return;
        generalState.messages = mergeMessagesById(generalState.messages, [msg]);
        generalMessages.querySelector('[data-internal-chat-empty="1"]')?.remove();
        const row = buildMessageBubble(msg, true);
        animateMessageEnter(row);
        generalMessages.appendChild(row);
        scrollToBottom(generalMessages);
    }

    function appendIncomingPrivateMessage(msg) {
        if (!privateMessages || !msg) return;
        privateState.messages = mergeMessagesById(privateState.messages, [msg]);
        privateMessages.querySelector('[data-internal-chat-empty="1"]')?.remove();
        const row = buildMessageBubble(msg, false);
        animateMessageEnter(row);
        privateMessages.appendChild(row);
        scrollToBottom(privateMessages);
        if (selectedPrivateUserId) {
            conversationMeta.set(selectedPrivateUserId, {
                preview: (msg.body || '').replace(/\s+/g, ' ').trim().slice(0, 72),
                time: msg.created_at,
            });
        }
    }

    async function loadUsers(q) {
        const url = new URL(config.usersUrl, window.location.origin);
        if (q) url.searchParams.set('q', q);
        const data = await api(url.toString());
        return data.users ?? [];
    }

    function renderUserList(users) {
        if (!userList) return;
        userList.replaceChildren();
        const q = userSearch?.value?.trim() ?? '';
        if (!users?.length) {
            const p = document.createElement('p');
            p.className = 'px-3 py-8 text-center text-xs font-medium text-slate-500 dark:text-gray-400';
            p.textContent = q ? t('chatNoSearch', 'No results') : t('chatNoUsers', 'No users');
            userList.appendChild(p);
            return;
        }
        users.forEach((u) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const selected = selectedPrivateUserId === u.id;
            const unread = (u.unread_count ?? 0) > 0;
            btn.className =
                'group flex w-full items-start gap-3 border-0 px-3 py-3 text-start transition duration-150 hover:-translate-y-px hover:bg-slate-50 hover:shadow-sm active:translate-y-0 dark:hover:bg-gray-800/90';
            if (selected) {
                btn.classList.add('bg-emerald-50', 'ring-1', 'ring-inset', 'ring-emerald-200/80', 'dark:bg-emerald-950/25', 'dark:ring-emerald-900/40');
            }
            const av = document.createElement('span');
            av.setAttribute('aria-hidden', 'true');
            fillAvatarElement(av, u, 'h-11 w-11');
            const mid = document.createElement('span');
            mid.className = 'min-w-0 flex-1';
            const top = document.createElement('span');
            top.className = 'flex w-full items-baseline justify-between gap-2';
            const name = document.createElement('span');
            name.className =
                'min-w-0 truncate text-[15px] ' +
                (unread ? 'font-extrabold text-slate-900 dark:text-white' : 'font-semibold text-slate-800 dark:text-gray-100');
            name.textContent = u.name;
            const meta = conversationMeta.get(u.id);
            const timeIso = u.last_message_at || meta?.time || '';
            const timeEl = document.createElement('time');
            timeEl.className = 'shrink-0 text-[10px] font-medium text-slate-400 dark:text-gray-500';
            timeEl.dateTime = timeIso;
            if (timeIso) {
                timeEl.textContent = formatListTime(timeIso);
            } else {
                timeEl.classList.add('hidden');
            }
            top.appendChild(name);
            top.appendChild(timeEl);
            mid.appendChild(top);
            const preview = document.createElement('p');
            preview.className =
                'm-0 mt-0.5 truncate text-start text-xs leading-snug ' +
                (unread ? 'font-semibold text-slate-700 dark:text-gray-200' : 'text-slate-500 dark:text-gray-400');
            const previewText = u.last_message_preview || meta?.preview;
            if (previewText) {
                preview.textContent = previewText;
            } else if (u.role_label) {
                preview.textContent = u.role_label;
            } else {
                preview.textContent = t('chatTapStart', 'Tap to start chatting');
            }
            mid.appendChild(preview);
            btn.appendChild(av);
            btn.appendChild(mid);
            if (unread) {
                const right = document.createElement('span');
                right.className = 'flex shrink-0 flex-col items-end gap-1 pt-0.5';
                const badge = document.createElement('span');
                badge.className =
                    'flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1 text-[10px] font-bold text-white shadow-sm dark:bg-emerald-500';
                badge.textContent = u.unread_count > 99 ? '99+' : String(u.unread_count);
                right.appendChild(badge);
                btn.appendChild(right);
            }
            btn.addEventListener('click', () => selectPrivateUser(u.id));
            userList.appendChild(btn);
        });
    }

    async function selectPrivateUser(id) {
        clearEditMode();
        selectedPrivateUserId = id;
        syncPrivateLayout();
        if (privateEmpty) privateEmpty.classList.add('hidden');
        if (privateThread) privateThread.classList.remove('hidden');
        setLoading(true);
        try {
            const url = `${config.privateBaseUrl}/${id}`;
            const data = await api(url);
            if (data.partner) {
                if (privatePartnerName) privatePartnerName.textContent = data.partner.name;
                if (privatePartnerAvatar) fillAvatarElement(privatePartnerAvatar, data.partner, 'h-10 w-10');
                if (privateStatusLine) {
                    privateStatusLine.textContent =
                        data.partner.role_label || t('chatTeamMember', 'Team member');
                }
            }
            privateState.messages = data.messages ?? [];
            privateState.hasMore = Boolean(data.has_more);
            privateHeader?.classList.remove('hidden');
            renderPrivateMessages(privateState.messages);
            await api(`${config.privateBaseUrl}/${id}/read`, { method: 'POST', body: JSON.stringify({}) });
            await refreshUnread();
            const users = await loadUsers(userSearch?.value?.trim() ?? '');
            renderUserList(users);
        } catch {
            privateState = { messages: [], hasMore: false };
            renderPrivateMessages([]);
        } finally {
            setLoading(false);
            syncPrivateLayout();
        }
    }

    async function refreshUnread() {
        try {
            const data = await api(config.unreadUrl);
            const total = data.total_private_unread ?? 0;
            if (fabBadge) {
                if (total > 0) {
                    fabBadge.classList.remove('hidden');
                    fabBadge.textContent = total > 99 ? '99+' : String(total);
                } else {
                    fabBadge.classList.add('hidden');
                }
            }
        } catch {
            /* ignore */
        }
    }

    async function tick() {
        if (!open) {
            await refreshUnread();
            return;
        }
        if (activeTab === 'general') await loadGeneral({ silent: true });
        else {
            await refreshUnread();
            const q = userSearch?.value?.trim() ?? '';
            if (selectedPrivateUserId) {
                try {
                    const url = new URL(
                        `${config.privateBaseUrl}/${selectedPrivateUserId}`,
                        window.location.origin,
                    );
                    if (privateState.messages.length) {
                        url.searchParams.set('after', String(lastMessageId(privateState.messages)));
                    }
                    const data = await api(url.toString());
                    if (url.searchParams.has('after')) {
                        if (data.messages?.length) {
                            privateState.messages = mergeMessagesById(privateState.messages, data.messages);
                            renderPrivateMessages(privateState.messages, { preserveScroll: true });
                        }
                    } else {
                        privateState.messages = data.messages ?? [];
                        privateState.hasMore = Boolean(data.has_more);
                        renderPrivateMessages(privateState.messages);
                    }
                } catch {
                    /* ignore */
                }
            }
            try {
                const users = await loadUsers(q);
                renderUserList(users);
            } catch {
                /* ignore */
            }
        }
    }

    function showStatus(el, text) {
        if (!el) return;
        el.textContent = text;
        el.classList.remove('hidden');
        window.setTimeout(() => el.classList.add('hidden'), 2200);
    }

    const chatPanelDesktopMq = '(min-width: 768px)';
    const chatPrefersReducedMotion =
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function syncPanelTransformOrigin() {
        if (!panel || !toggle) return;
        if (!window.matchMedia(chatPanelDesktopMq).matches) {
            panel.style.transformOrigin = '';
            return;
        }
        const fab = toggle.getBoundingClientRect();
        const pr = panel.getBoundingClientRect();
        const x = fab.left + fab.width / 2 - pr.left;
        const y = fab.top + fab.height / 2 - pr.top;
        panel.style.transformOrigin = `${Math.round(x)}px ${Math.round(y)}px`;
    }

    function setFabOpenState(isOpen) {
        toggle?.classList.toggle('internal-chat-fab--panel-open', isOpen);
        toggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function finishPanelClose() {
        if (!panel) return;
        panel.classList.remove('internal-chat-panel--leave', 'internal-chat-panel--enter');
        panel.classList.add('hidden');
        panel.setAttribute('aria-hidden', 'true');
        panel.style.transformOrigin = '';
    }

    function closePanelImmediate() {
        open = false;
        closeDeleteModal();
        closeClearAllModal();
        clearEditMode();
        finishPanelClose();
        setFabOpenState(false);
        schedulePoll();
    }

    function closePanelAnimated() {
        if (!open || !panel) return;
        open = false;
        closeDeleteModal();
        closeClearAllModal();
        clearEditMode();
        setFabOpenState(false);
        schedulePoll();

        if (chatPrefersReducedMotion) {
            finishPanelClose();
            return;
        }

        panel.classList.remove('internal-chat-panel--enter');
        panel.classList.add('internal-chat-panel--leave');
        let closeSettled = false;
        const settleClose = () => {
            if (closeSettled) return;
            closeSettled = true;
            panel.removeEventListener('animationend', settleClose);
            finishPanelClose();
        };
        panel.addEventListener('animationend', settleClose);
        window.setTimeout(settleClose, 340);
    }

    function openPanelAnimated() {
        if (!panel) return;
        open = true;
        closeDeleteModal();
        closeClearAllModal();
        panel.classList.remove('hidden', 'internal-chat-panel--leave');
        panel.setAttribute('aria-hidden', 'false');
        setFabOpenState(true);
        syncPanelTransformOrigin();
        if (!chatPrefersReducedMotion) {
            panel.classList.remove('internal-chat-panel--enter');
            void panel.offsetWidth;
            panel.classList.add('internal-chat-panel--enter');
            panel.addEventListener(
                'animationend',
                () => {
                    panel.classList.remove('internal-chat-panel--enter');
                },
                { once: true },
            );
        }
        updateContextUi();
        setTabUi();
        void tick();
        if (activeTab === 'private') syncPrivateLayout();
        schedulePoll();
        requestAnimationFrame(() => {
            if (!panel || panel.classList.contains('hidden') || !root) return;
            const r = panel.getBoundingClientRect();
            const pad = 12;
            const off =
                r.left < pad ||
                r.right > window.innerWidth - pad ||
                r.top < pad ||
                r.bottom > window.innerHeight - pad;
            if (off && root.classList.contains('internal-chat-root--positioned')) {
                root.classList.remove('internal-chat-root--positioned');
                root.style.left = '';
                root.style.top = '';
                root.style.right = '';
                root.style.bottom = '';
                CHAT_FAB_POSITION_CLASSES.forEach((c) => root.classList.add(c));
                try {
                    localStorage.removeItem(CHAT_FAB_POS_STORAGE_KEY);
                } catch {
                    /* ignore */
                }
                syncPanelTransformOrigin();
            }
        });
    }

    toggle?.addEventListener('click', () => {
        if (fabDrag.shouldSuppressClick()) return;
        if (open) closePanelAnimated();
        else openPanelAnimated();
    });

    closeBtn?.addEventListener('click', () => {
        closePanelAnimated();
    });

    refreshBtn?.addEventListener('click', () => {
        void tick();
    });

    tabGeneral?.addEventListener('click', () => {
        clearEditMode();
        activeTab = 'general';
        setTabUi();
        void loadGeneral();
    });

    tabPrivate?.addEventListener('click', () => {
        clearEditMode();
        activeTab = 'private';
        setTabUi();
        void (async () => {
            try {
                const users = await loadUsers(userSearch?.value?.trim() ?? '');
                renderUserList(users);
            } catch {
                renderUserList([]);
            } finally {
                syncPrivateLayout();
            }
        })();
    });

    userSearch?.addEventListener('input', () => {
        if (searchDebounce) window.clearTimeout(searchDebounce);
        searchDebounce = window.setTimeout(async () => {
            try {
                const users = await loadUsers(userSearch.value.trim());
                renderUserList(users);
            } catch {
                /* ignore */
            }
        }, 320);
    });

    generalForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const body = generalInput?.value?.trim() ?? '';
        if (!body) return;
        if (editingMessageId) {
            setGeneralSending(true);
            try {
                const data = await api(`${config.messagesBaseUrl}/${editingMessageId}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ body }),
                });
                clearEditMode();
                if (data?.message) {
                    generalState.messages = upsertMessageInList(generalState.messages, data.message);
                    renderGeneralMessages(generalState.messages);
                } else {
                    await loadGeneral({ forceFull: true });
                }
            } catch (err) {
                showStatus(generalStatus, err instanceof Error ? err.message : t('chatSaveFail', 'Could not save your edit'));
            } finally {
                setGeneralSending(false);
            }
            return;
        }
        const ctx = parsePageContext();
        const payload = { body };
        if (generalAttach?.checked && hasAnyContext(ctx)) {
            if (ctx.patient_id) payload.patient_id = ctx.patient_id;
            if (ctx.invoice_id) payload.invoice_id = ctx.invoice_id;
            if (ctx.appointment_id) payload.appointment_id = ctx.appointment_id;
        }
        setGeneralSending(true);
        try {
            const sent = await api(config.generalSendUrl, { method: 'POST', body: JSON.stringify(payload) });
            if (generalInput) generalInput.value = '';
            if (sent?.message) {
                appendIncomingGeneralMessage(sent.message);
            } else {
                await loadGeneral({ silent: true });
            }
        } catch (err) {
            showStatus(generalStatus, err instanceof Error ? err.message : t('chatSendFail', 'Could not send'));
        } finally {
            setGeneralSending(false);
        }
    });

    privateForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!selectedPrivateUserId) return;
        const body = privateInput?.value?.trim() ?? '';
        if (!body) return;
        if (editingMessageId) {
            setPrivateSending(true);
            try {
                const data = await api(`${config.messagesBaseUrl}/${editingMessageId}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ body }),
                });
                clearEditMode();
                if (data?.message) {
                    privateState.messages = upsertMessageInList(privateState.messages, data.message);
                    renderPrivateMessages(privateState.messages);
                } else if (selectedPrivateUserId) {
                    await selectPrivateUser(selectedPrivateUserId);
                }
            } catch (err) {
                showStatus(privateStatus, err instanceof Error ? err.message : t('chatSaveFail', 'Could not save your edit'));
            } finally {
                setPrivateSending(false);
            }
            return;
        }
        const ctx = parsePageContext();
        const payload = { receiver_id: selectedPrivateUserId, body };
        if (privateAttach?.checked && hasAnyContext(ctx)) {
            if (ctx.patient_id) payload.patient_id = ctx.patient_id;
            if (ctx.invoice_id) payload.invoice_id = ctx.invoice_id;
            if (ctx.appointment_id) payload.appointment_id = ctx.appointment_id;
        }
        setPrivateSending(true);
        try {
            const sent = await api(config.privateSendUrl, { method: 'POST', body: JSON.stringify(payload) });
            if (privateInput) privateInput.value = '';
            if (sent?.message) {
                appendIncomingPrivateMessage(sent.message);
                try {
                    await api(`${config.privateBaseUrl}/${selectedPrivateUserId}/read`, {
                        method: 'POST',
                        body: JSON.stringify({}),
                    });
                } catch {
                    /* ignore */
                }
                await refreshUnread();
                try {
                    const users = await loadUsers(userSearch?.value?.trim() ?? '');
                    renderUserList(users);
                } catch {
                    /* ignore */
                }
            } else {
                const data = await api(`${config.privateBaseUrl}/${selectedPrivateUserId}`);
                privateState.messages = data.messages ?? [];
                privateState.hasMore = Boolean(data.has_more);
                renderPrivateMessages(privateState.messages);
            }
        } catch (err) {
            showStatus(privateStatus, err instanceof Error ? err.message : t('chatSendFail', 'Could not send'));
        } finally {
            setPrivateSending(false);
        }
    });

    privateBack?.addEventListener('click', () => {
        clearPrivateSelection();
    });

    window.addEventListener('resize', () => {
        syncPrivateLayout();
    });

    document.addEventListener('internal-chat:delete-message', (ev) => {
        const id = ev.detail?.id;
        if (!id) return;
        openDeleteModal(id);
    });

    document.addEventListener('internal-chat:edit-message', (ev) => {
        const id = ev.detail?.id;
        const oldBody = ev.detail?.body ?? '';
        const canEdit = ev.detail?.can_edit !== false;
        if (!id) return;
        if (!canEdit) {
            const hint = t('chatEditCooldown', 'Messages can only be edited within 5 minutes of sending.');
            if (activeTab === 'general') showStatus(generalStatus, hint);
            else showStatus(privateStatus, hint);
            return;
        }
        startEditingMessage(id, oldBody);
    });

    deleteConfirmBtn?.addEventListener('click', async () => {
        const id = pendingDeleteId;
        if (!id) return;
        try {
            await api(`${config.messagesBaseUrl}/${id}`, { method: 'DELETE' });
            closeDeleteModal();
            if (activeTab === 'general') {
                generalState.messages = removeMessageFromList(generalState.messages, id);
                renderGeneralMessages(generalState.messages);
            } else {
                privateState.messages = removeMessageFromList(privateState.messages, id);
                renderPrivateMessages(privateState.messages);
            }
        } catch (err) {
            closeDeleteModal();
            const msg = err instanceof Error ? err.message : t('chatDeleteFail', 'Could not delete');
            if (activeTab === 'general') showStatus(generalStatus, msg);
            else showStatus(privateStatus, msg);
        }
    });

    deleteCancelBtn?.addEventListener('click', () => {
        closeDeleteModal();
    });

    deleteModal?.addEventListener('click', (ev) => {
        if (ev.target === deleteModal) closeDeleteModal();
    });

    generalEditCancelBtn?.addEventListener('click', () => {
        clearEditMode();
    });

    privateEditCancelBtn?.addEventListener('click', () => {
        clearEditMode();
    });

    generalDeleteAllBtn?.addEventListener('click', () => {
        openClearAllModal('general');
    });

    privateDeleteAllBtn?.addEventListener('click', () => {
        if (!selectedPrivateUserId) return;
        openClearAllModal('private');
    });

    privateEmptyViewBtn?.addEventListener('click', () => {
        clearPrivateSelection();
    });

    clearAllConfirmBtn?.addEventListener('click', async () => {
        const scope = pendingClearAllScope;
        if (!scope) return;
        try {
            if (scope === 'general') {
                const url = config.generalClearConversationUrl;
                if (!url) throw new Error(t('chatUrlMissing', 'URL is not configured'));
                await api(url, { method: 'DELETE' });
                closeClearAllModal();
                clearEditMode();
                generalState = { messages: [], hasMore: false };
                await loadGeneral();
            } else {
                const uid = selectedPrivateUserId;
                if (!uid) {
                    closeClearAllModal();
                    return;
                }
                await api(`${config.privateBaseUrl}/${uid}/conversation`, { method: 'DELETE' });
                closeClearAllModal();
                clearEditMode();
                privateState = { messages: [], hasMore: false };
                renderPrivateMessages([]);
                conversationMeta.set(uid, { preview: '', time: '' });
                await refreshUnread();
                try {
                    const users = await loadUsers(userSearch?.value?.trim() ?? '');
                    renderUserList(users);
                } catch {
                    /* ignore */
                }
            }
        } catch (err) {
            closeClearAllModal();
            const msg = err instanceof Error ? err.message : t('chatDeleteFail', 'Could not delete');
            if (scope === 'general') showStatus(generalStatus, msg);
            else showStatus(privateStatus, msg);
        }
    });

    clearAllCancelBtn?.addEventListener('click', () => {
        closeClearAllModal();
    });

    clearAllModal?.addEventListener('click', (ev) => {
        if (ev.target === clearAllModal) closeClearAllModal();
    });

    document.addEventListener('spa:navigated', () => {
        updateContextUi();
    });

    updateContextUi();
    if (config.canClearGeneral === false && generalDeleteAllBtn) {
        generalDeleteAllBtn.classList.add('hidden');
    }
    if (tabGeneral) tabGeneral.textContent = t('chatGeneral', 'General');
    if (tabPrivate) tabPrivate.textContent = t('chatPrivate', 'Private');
    setTabUi();
    bindEnterToSend(generalInput, generalForm);
    bindEnterToSend(privateInput, privateForm);
    syncPrivateLayout();

    /** تحديث أقل ازعاجاً: أبطأ عند الإغلاق، أسرع عند فتح المحادثة */
    const POLL_MS_CLOSED = 20000;
    const POLL_MS_OPEN = 9000;

    function schedulePoll() {
        if (pollTimer) window.clearTimeout(pollTimer);
        const delay = open ? POLL_MS_OPEN : POLL_MS_CLOSED;
        pollTimer = window.setTimeout(async () => {
            await tick();
            schedulePoll();
        }, delay);
    }

    void refreshUnread();
    schedulePoll();

    document.addEventListener(
        'keydown',
        (ev) => {
            if (ev.key !== 'Escape') return;
            if (clearAllModal && !clearAllModal.classList.contains('hidden')) {
                closeClearAllModal();
                ev.preventDefault();
                ev.stopPropagation();
                return;
            }
            if (deleteModal && !deleteModal.classList.contains('hidden')) {
                closeDeleteModal();
                ev.preventDefault();
                ev.stopPropagation();
                return;
            }
            if (editingMessageId) {
                clearEditMode();
                ev.preventDefault();
                ev.stopPropagation();
                return;
            }
            if (open && panel && !panel.classList.contains('hidden')) {
                closePanelAnimated();
                ev.preventDefault();
                ev.stopPropagation();
            }
        },
        true,
    );

    window.addEventListener('beforeunload', () => {
        if (pollTimer) window.clearTimeout(pollTimer);
    });
}
