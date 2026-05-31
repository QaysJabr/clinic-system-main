/**
 * Switch locale via POST, then reload the page so shell + content stay in sync
 * without nested server requests that could invalidate the session.
 */
import { resetSpaStateForLocaleChange } from './spa-navigation';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function getLocaleSwapRoot() {
    return document.getElementById('locale-swap-root');
}

async function switchLocaleAjax(locale) {
    const menu = document.querySelector('[data-locale-menu][data-apply-url]');
    const applyUrl = menu?.getAttribute('data-apply-url');

    if (!applyUrl) {
        window.location.assign(`/locale/${encodeURIComponent(locale)}`);
        return;
    }

    resetSpaStateForLocaleChange();

    const root = getLocaleSwapRoot();
    root?.classList.add('opacity-90', 'pointer-events-none', 'transition-opacity', 'duration-150');

    try {
        const res = await fetch(applyUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                locale,
                path: `${window.location.pathname}${window.location.search}`,
            }),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.ok) {
            if (typeof data.redirect === 'string' && data.redirect !== '') {
                window.location.assign(data.redirect);
                return;
            }
            window.location.reload();
            return;
        }

        window.location.reload();
    } catch {
        window.location.reload();
    }
}

document.addEventListener('click', (e) => {
    const t = e.target;
    if (!(t instanceof Element)) return;
    const btn = t.closest('button[data-locale]');
    if (!(btn instanceof HTMLButtonElement)) return;
    const menu = btn.closest('[data-locale-menu]');
    if (!menu) return;
    const locale = btn.getAttribute('data-locale');
    if (!locale) return;
    e.preventDefault();
    void switchLocaleAjax(locale);
    if (menu instanceof HTMLDetailsElement) {
        menu.removeAttribute('open');
    }
});
