/**
 * Flash messages (session success/error) that fade out and remove themselves — used app-wide.
 * Mark any root element with [data-flash-auto-dismiss] (optional value = milliseconds, default 5200).
 * Optional child button [data-flash-dismiss-btn] closes immediately.
 */

const DEFAULT_MS = 5200;

/**
 * @param {ParentNode} [root]
 */
export function initFlashAutoDismiss(root = document) {
    if (!root || typeof root.querySelectorAll !== 'function') {
        return;
    }

    root.querySelectorAll('[data-flash-auto-dismiss]').forEach((el) => {
        if (!(el instanceof HTMLElement)) {
            return;
        }
        if (el.dataset.flashDismissBound === '1') {
            return;
        }
        el.dataset.flashDismissBound = '1';

        const raw = el.getAttribute('data-flash-auto-dismiss');
        const ms = raw === '' || raw === null ? DEFAULT_MS : parseInt(raw, 10) || DEFAULT_MS;

        const timer = window.setTimeout(() => dismiss(el), ms);
        /** @type {HTMLElement & { _flashDismissTimer?: ReturnType<typeof setTimeout> }} */
        const host = el;
        host._flashDismissTimer = timer;

        const clearAndDismiss = () => {
            if (host._flashDismissTimer) {
                window.clearTimeout(host._flashDismissTimer);
                host._flashDismissTimer = undefined;
            }
            dismiss(el);
        };

        const btn = el.querySelector('[data-flash-dismiss-btn]');
        if (btn) {
            btn.addEventListener('click', clearAndDismiss);
        }
    });
}

/**
 * @param {HTMLElement} el
 */
function dismiss(el) {
    if (!el.isConnected) {
        return;
    }
    if (el.dataset.flashDismissed === '1') {
        return;
    }
    el.dataset.flashDismissed = '1';
    if (/** @type {HTMLElement & { _flashDismissTimer?: ReturnType<typeof setTimeout> }} */ (el)._flashDismissTimer) {
        window.clearTimeout(/** @type {any} */ (el)._flashDismissTimer);
    }
    el.style.transition = 'opacity 0.38s ease, transform 0.38s ease, margin 0.38s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-6px)';
    el.style.marginBottom = '0';
    window.setTimeout(() => {
        if (el.isConnected) {
            el.remove();
        }
    }, 400);
}
