function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function t(key, fallback) {
    return window.AppI18n?.messages?.[key] ?? fallback;
}

function isClinicsIndexLink(anchor) {
    if (!(anchor instanceof HTMLAnchorElement) || !anchor.href) return false;
    if (anchor.hasAttribute('data-no-spa')) return false;
    try {
        const path = new URL(anchor.href, window.location.origin).pathname.replace(/\/$/, '') || '/';

        return path === '/platform/clinics';
    } catch {
        return false;
    }
}

function bindPagination(root) {
    const wrap = root.querySelector('#platform-clinics-table-wrap');
    if (!wrap) return;
    wrap.querySelectorAll('a').forEach((a) => {
        if (!isClinicsIndexLink(a)) return;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            void loadTable(root, new URL(a.href)).catch((err) => {
                window.alert(err instanceof Error ? err.message : t('error', 'An error occurred'));
            });
        });
    });
}

function setBusy(btn, busy) {
    if (!(btn instanceof HTMLButtonElement)) return;
    btn.disabled = busy;
    btn.classList.toggle('opacity-60', busy);
    btn.classList.toggle('cursor-not-allowed', busy);
}

function showLoadError(err) {
    window.alert(err instanceof Error ? err.message : t('error', 'An error occurred'));
}

async function loadTable(root, url = null) {
    const base = root.dataset.urlIndex;
    const filtersForm = root.querySelector('#platform-clinics-filters');
    const wrap = root.querySelector('#platform-clinics-table-wrap');
    if (!base || !filtersForm || !wrap) return;

    const targetUrl = url ?? new URL(base, window.location.origin);
    if (!url) {
        const query = new URLSearchParams(new FormData(filtersForm));
        targetUrl.search = query.toString();
    }
    wrap.classList.add('opacity-70');
    const res = await fetch(targetUrl.toString(), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const contentType = res.headers.get('content-type') ?? '';
    const json = contentType.includes('application/json') ? await res.json().catch(() => ({})) : {};
    wrap.classList.remove('opacity-70');
    if (!res.ok || !json.success) {
        throw new Error(json.message || t('error', 'An error occurred'));
    }
    wrap.innerHTML = json.data?.html ?? '';
    window.history.replaceState({}, '', targetUrl.toString());
    bindPagination(root);
}

function initPlatformClinics() {
    const root = document.getElementById('platform-clinics-page');
    if (!root || root.dataset.init === '1') return;
    root.dataset.init = '1';

    const filtersForm = root.querySelector('#platform-clinics-filters');
    let filterTimer = null;
    filtersForm?.addEventListener('input', () => {
        if (filterTimer) window.clearTimeout(filterTimer);
        filterTimer = window.setTimeout(() => {
            void loadTable(root).catch(showLoadError);
        }, 220);
    });
    filtersForm?.addEventListener('change', () => {
        void loadTable(root).catch(showLoadError);
    });

    root.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const btn = target.closest('button[data-clinic-action]');
        if (!(btn instanceof HTMLButtonElement)) return;
        const action = btn.dataset.clinicAction;
        const url = btn.dataset.url;
        if (!url) return;

        if (action === 'suspend') {
            const msg = btn.dataset.confirm || t('confirmDelete', 'Are you sure?');
            if (!window.confirm(msg)) return;
        }

        try {
            setBusy(btn, true);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) throw new Error(json.message || t('error', 'An error occurred'));
            await loadTable(root);
        } catch (e) {
            window.alert(e instanceof Error ? e.message : t('error', 'An error occurred'));
        } finally {
            setBusy(btn, false);
        }
    });

    bindPagination(root);
}

document.addEventListener('DOMContentLoaded', initPlatformClinics);
document.addEventListener('spa:navigated', initPlatformClinics);
