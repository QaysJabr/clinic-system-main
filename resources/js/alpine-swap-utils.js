/**
 * Teardown / mount Alpine on DOM fragments replaced via locale AJAX or SPA swaps.
 * Without destroyTree, replaced nodes can leave stale Alpine state so directives (e.g. header dropdown) stop working.
 */

/** Close overlays/modals before DOM swap (avoids stale nodes + DevTools "deferred DOM Node" warnings). */
export function alpineTeardownBeforeSwap() {
    document.body.classList.remove('overflow-y-hidden');
    document.dispatchEvent(new CustomEvent('clinic:close-confirm'));
    document.dispatchEvent(new CustomEvent('spa:before-swap', { bubbles: true }));
}

export function alpineDestroyTree(root) {
    if (!root || !window.Alpine || typeof window.Alpine.destroyTree !== 'function') return;
    try {
        window.Alpine.destroyTree(root);
    } catch {
        /* ignore */
    }
}

export function alpineInitTree(root) {
    if (root && window.Alpine && typeof window.Alpine.initTree === 'function') {
        window.Alpine.initTree(root);
    }
}
