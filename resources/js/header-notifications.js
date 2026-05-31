/**
 * Header popovers (notifications, account menu, locale): one open at a time + click outside.
 */

const HEADER_POPOVER_SELECTOR = '[data-header-popover]';

const PANEL_WIDTH = 320;
const VIEWPORT_PAD = 8;
const GAP = 8;

let documentListenersBound = false;

function positionNotificationsPanel(details) {
    const panel = details.querySelector('[data-header-notifications-panel]');
    const trigger = details.querySelector('[data-header-notifications-trigger]');
    if (!panel || !trigger) return;

    const rect = trigger.getBoundingClientRect();
    const width = Math.min(PANEL_WIDTH, window.innerWidth - VIEWPORT_PAD * 2);
    const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

    panel.style.width = `${width}px`;
    panel.style.top = `${rect.bottom + GAP}px`;

    let left = isRtl ? rect.left : rect.right - width;
    left = Math.max(VIEWPORT_PAD, Math.min(left, window.innerWidth - width - VIEWPORT_PAD));

    panel.style.left = `${left}px`;
    panel.style.right = 'auto';
}

export function closeAllHeaderPopovers(except = null) {
    document.querySelectorAll(HEADER_POPOVER_SELECTOR).forEach((el) => {
        if (el !== except && el.open) {
            el.removeAttribute('open');
        }
    });
}

function popoverFromTarget(target) {
    if (!(target instanceof Element)) return null;
    return target.closest(HEADER_POPOVER_SELECTOR);
}

function onDocumentPointerDown(event) {
    const target = event.target;
    if (!(target instanceof Node)) return;

    const openMenus = document.querySelectorAll(`${HEADER_POPOVER_SELECTOR}[open]`);
    if (openMenus.length === 0) return;

    const clickedPopover = popoverFromTarget(target);
    const insideOpenMenu = [...openMenus].some((menu) => menu.contains(target));

    if (!insideOpenMenu) {
        closeAllHeaderPopovers(null);
        return;
    }

    if (clickedPopover) {
        openMenus.forEach((menu) => {
            if (menu !== clickedPopover && menu.open) {
                menu.removeAttribute('open');
            }
        });
    }
}

function onDocumentKeyDown(event) {
    if (event.key === 'Escape') {
        closeAllHeaderPopovers(null);
    }
}

function bindDocumentListeners() {
    if (documentListenersBound) return;
    documentListenersBound = true;
    document.addEventListener('pointerdown', onDocumentPointerDown, true);
    document.addEventListener('keydown', onDocumentKeyDown);
}

function bindHeaderPopovers(root = document) {
    root.querySelectorAll(HEADER_POPOVER_SELECTOR).forEach((details) => {
        if (details.dataset.headerPopoverBound === '1') return;
        details.dataset.headerPopoverBound = '1';

        const summary = details.querySelector('summary');
        summary?.addEventListener(
            'pointerdown',
            () => {
                closeAllHeaderPopovers(details);
            },
            true,
        );

        details.addEventListener('toggle', () => {
            if (!details.open) return;
            closeAllHeaderPopovers(details);
            if (details.matches('[data-header-notifications]')) {
                positionNotificationsPanel(details);
            }
        });

        if (details.matches('[data-header-notifications]')) {
            window.addEventListener(
                'resize',
                () => {
                    if (details.open) positionNotificationsPanel(details);
                },
                { passive: true },
            );
        }
    });
}

export function initHeaderNotifications(root = document) {
    bindHeaderPopovers(root);
    bindDocumentListeners();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initHeaderNotifications(document), { once: true });
} else {
    initHeaderNotifications(document);
}

document.addEventListener('spa:navigated', (event) => {
    const root = event.detail?.root ?? document;
    initHeaderNotifications(root);
});
