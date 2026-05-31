/**
 * SPA navigation: intercept internal links, fetch HTML, swap main region without full reload.
 * Supports server partials OR full HTML documents (extract #app-content / #locale-swap-root).
 */

import { initFlashAutoDismiss } from './flash-auto-dismiss';
import { alpineDestroyTree, alpineInitTree, alpineTeardownBeforeSwap } from './alpine-swap-utils';

/** @type {Map<string, { html: string, at: number }>} */
const spaCache = new Map();
const SPA_CACHE_MAX = 24;
const SPA_CACHE_TTL_MS = 300000;

function spaCacheKey(url) {
    const lang = document.documentElement.getAttribute('lang') || 'ar';
    return `${url}|${lang}`;
}

function spaCacheSet(url, html) {
    spaCache.set(spaCacheKey(url), { html, at: Date.now() });
    if (spaCache.size <= SPA_CACHE_MAX) return;
    const oldest = [...spaCache.entries()].sort((a, b) => a[1].at - b[1].at)[0]?.[0];
    if (oldest) spaCache.delete(oldest);
}

function spaCacheGet(url) {
    const entry = spaCache.get(spaCacheKey(url));
    if (!entry) return null;
    if (Date.now() - entry.at > SPA_CACHE_TTL_MS) {
        spaCache.delete(spaCacheKey(url));
        return null;
    }
    return entry.html;
}

/** Abort in-flight SPA fetches and drop cached HTML (e.g. after locale change). */
export function resetSpaStateForLocaleChange() {
    spaCache.clear();
    spaInflightLoads.clear();
    if (spaFetchController) {
        spaFetchController.abort();
        spaFetchController = null;
    }
    spaInflightUrl = null;
    spaNavGeneration++;
    cancelDeferredProgress();
    hideLoader();
}

/** Store a full HTML document for the current URL after locale swap. */
export function seedSpaCacheForUrl(url, html) {
    if (typeof html === 'string' && html.trim() !== '') {
        spaCacheSet(url, html);
    }
}
/** @type {Map<string, Promise<string|null>>} */
const spaInflightLoads = new Map();
const spaPrefetchTimers = {};
let spaFetchController = null;
/** @type {string|null} */
let spaInflightUrl = null;
let spaNavGeneration = 0;

let spaProgressShown = false;
let spaShowProgressTimer = null;
let spaProgressHideTimer = null;

function spaSameOrigin(url) {
    try {
        const u = new URL(url, location.href);
        return u.origin === location.origin;
    } catch {
        return false;
    }
}

function spaShouldFullNavigation(link) {
    if (!link || link.tagName !== 'A') return true;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#')) return true;
    if (link.hasAttribute('data-no-spa')) return true;
    if (link.target === '_blank' || link.hasAttribute('download')) return true;
    if (!spaSameOrigin(link.href)) return true;

    const hrefLower = href.trim().toLowerCase();
    if (
        hrefLower.startsWith('mailto:') ||
        hrefLower.startsWith('tel:') ||
        hrefLower.startsWith('javascript:')
    ) {
        return true;
    }

    let path;
    try {
        path = new URL(link.href).pathname;
    } catch {
        return true;
    }
    if (spaIsAuthBoundaryPath(path)) return true;
    if (path.includes('/export')) return true;
    if (path.includes('/print')) return true;
    if (path.includes('/pdf')) return true;
    if (path.includes('/attachments/') && path.includes('/download')) return true;
    if (path.includes('/backups/') && path.endsWith('/download')) return true;
    return false;
}

function spaIsAuthBoundaryPath(pathname) {
    const path = String(pathname || '').toLowerCase();
    return (
        path === '/login' ||
        path === '/register' ||
        path === '/register-clinic' ||
        path === '/logout' ||
        path.startsWith('/forgot-password') ||
        path.startsWith('/reset-password') ||
        path.startsWith('/verify-email') ||
        path.startsWith('/email/verification') ||
        path.startsWith('/confirm-password') ||
        path.startsWith('/two-factor') ||
        path.startsWith('/pricing') ||
        path.startsWith('/book/') ||
        path === '/'
    );
}

function cancelDeferredProgress() {
    if (spaShowProgressTimer !== null) {
        clearTimeout(spaShowProgressTimer);
        spaShowProgressTimer = null;
    }
}

function scheduleDeferredProgress() {
    cancelDeferredProgress();
    spaShowProgressTimer = window.setTimeout(() => {
        spaShowProgressTimer = null;
        showLoader();
    }, 430);
}

function showLoader() {
    const el = document.getElementById('spa-loader');
    if (!el) return;
    if (spaProgressHideTimer) {
        clearTimeout(spaProgressHideTimer);
        spaProgressHideTimer = null;
    }
    spaProgressShown = true;
    el.style.transition = 'width 0.22s ease-out, opacity 0.12s ease-out';
    el.classList.remove('opacity-0');
    el.style.opacity = '0.75';
    el.style.width = '0%';
    void el.offsetWidth;
    el.style.width = '62%';
}

function hideLoader() {
    const el = document.getElementById('spa-loader');
    if (!el) return;
    cancelDeferredProgress();
    if (!spaProgressShown) {
        el.style.width = '0%';
        el.classList.add('opacity-0');
        el.style.opacity = '0';
        return;
    }
    spaProgressShown = false;
    el.style.width = '100%';
    spaProgressHideTimer = window.setTimeout(() => {
        spaProgressHideTimer = null;
        el.style.transition = 'width 0.18s ease-out, opacity 0.18s ease-out';
        el.style.width = '0%';
        el.classList.add('opacity-0');
        el.style.opacity = '0';
    }, 120);
}

function spaIsFragmentHtml(html) {
    const t = String(html).trim();
    if (!t) return false;
    return !t.startsWith('<!DOCTYPE') && !t.startsWith('<!doctype') && !t.startsWith('<html');
}

/**
 * @returns {'app'|'shell'|null}
 */
function getSpaMode() {
    if (spaIsAuthBoundaryPath(location.pathname)) {
        return null;
    }
    if (document.getElementById('app-content')) return 'app';
    if (document.getElementById('locale-swap-root')) return 'shell';
    return null;
}

/**
 * @typedef {{ type: 'fragment', html: string, title: string|null }} SpaPayloadFragment
 * @typedef {{ type: 'app', html: string, title: string|null }} SpaPayloadApp
 * @typedef {{ type: 'shell', html: string, title: string|null, lang: string|null, dir: string|null }} SpaPayloadShell
 */

/**
 * @param {string} html
 * @returns {SpaPayloadFragment|SpaPayloadApp|SpaPayloadShell|null}
 */
function spaResolveFetchedHtml(html) {
    const raw = String(html).trim();
    if (!raw) return null;

    if (spaIsFragmentHtml(raw)) {
        return { type: 'fragment', html: raw, title: null };
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const appEl = doc.getElementById('app-content');
    if (appEl) {
        return {
            type: 'app',
            html: appEl.innerHTML,
            title: doc.title || null,
        };
    }

    const shellEl = doc.getElementById('locale-swap-root');
    if (shellEl) {
        const htmlEl = doc.documentElement;
        return {
            type: 'shell',
            html: shellEl.innerHTML,
            title: doc.title || null,
            lang: htmlEl.getAttribute('lang'),
            dir: htmlEl.getAttribute('dir'),
        };
    }

    return null;
}

/**
 * @param {SpaPayloadFragment|SpaPayloadApp|SpaPayloadShell} payload
 * @param {'app'|'shell'} mode
 */
function spaPayloadMatchesMode(payload, mode) {
    if (mode === 'app') {
        return payload.type === 'fragment' || payload.type === 'app';
    }
    return payload.type === 'shell';
}

function executeScriptsIn(container) {
    container.querySelectorAll('script').forEach((oldScript) => {
        const s = document.createElement('script');
        [...oldScript.attributes].forEach((attr) => s.setAttribute(attr.name, attr.value));
        s.textContent = oldScript.textContent;
        oldScript.parentNode?.replaceChild(s, oldScript);
    });
}

function updateSidebarActive() {
    const path = location.pathname.replace(/\/$/, '') || '/';
    document.querySelectorAll('a.sidebar-link').forEach((link) => {
        if (!(link instanceof HTMLAnchorElement)) return;
        let linkPath;
        try {
            linkPath = new URL(link.href).pathname.replace(/\/$/, '') || '/';
        } catch {
            return;
        }
        const active =
            path === linkPath ||
            (linkPath !== '/' && path.startsWith(linkPath + '/'));
        link.classList.toggle('sidebar-link-active', active);
    });
}

async function spaMountDashboardCharts(root) {
    if (!root?.querySelector?.('[data-clinic-dashboard]')) {
        return;
    }
    const mod = await import('./clinic-dashboard');
    mod.destroyClinicDashboardCharts();
    mod.initClinicDashboard(root);
}

async function spaMountAppointmentCalendar(root) {
    if (!root?.querySelector?.('[data-appointment-calendar]')) {
        return;
    }
    const mod = await import('./appointments-calendar');
    mod.destroyAppointmentCalendar();
    mod.initAppointmentCalendar(root.querySelector('[data-appointment-calendar]'));
}

async function spaMountAppointmentSlots(root) {
    if (!root?.querySelector?.('[data-appointment-slots]')) {
        return;
    }
    const mod = await import('./appointment-slots');
    root.querySelectorAll('[data-appointment-slots]').forEach((el) => mod.initAppointmentSlots(el));
}

function spaMountAlpine(root) {
    alpineInitTree(root);
    void spaMountDashboardCharts(root);
    void spaMountAppointmentCalendar(root);
    void spaMountAppointmentSlots(root);
}

/**
 * @param {SpaPayloadFragment|SpaPayloadApp|SpaPayloadShell} payload
 * @param {string} resolved
 * @param {boolean} push
 */
function spaApplyPayload(payload, resolved, push) {
    const mode = getSpaMode();
    if (!mode || !spaPayloadMatchesMode(payload, mode)) {
        window.location.assign(resolved);
        return;
    }

    if (payload.type === 'fragment' || payload.type === 'app') {
        const contentEl = document.getElementById('app-content');
        if (!contentEl) {
            window.location.assign(resolved);
            return;
        }
        alpineTeardownBeforeSwap();
        alpineDestroyTree(contentEl);
        contentEl.innerHTML = payload.html;
        if (push) {
            window.history.pushState({ spa: true }, '', resolved);
        }
        const t = contentEl.querySelector('[data-spa-page-title]');
        if (t?.textContent?.trim()) {
            document.title = t.textContent.trim();
        } else if (payload.title) {
            document.title = payload.title;
        }
        window.scrollTo(0, 0);
        updateSidebarActive();
        spaMountAlpine(contentEl);
        initFlashAutoDismiss(contentEl);
        contentEl.dispatchEvent(
            new CustomEvent('spa:navigated', {
                bubbles: true,
                detail: { url: resolved, root: contentEl },
            }),
        );
        return;
    }

    const rootEl = document.getElementById('locale-swap-root');
    if (!rootEl) {
        window.location.assign(resolved);
        return;
    }
    alpineTeardownBeforeSwap();
    alpineDestroyTree(rootEl);
    rootEl.innerHTML = payload.html;
    if (payload.lang) {
        document.documentElement.setAttribute('lang', payload.lang);
    }
    if (payload.dir === 'rtl' || payload.dir === 'ltr') {
        document.documentElement.setAttribute('dir', payload.dir);
    }
    if (payload.title) {
        document.title = payload.title;
    }
    if (push) {
        window.history.pushState({ spa: true }, '', resolved);
    }
    window.scrollTo(0, 0);
    executeScriptsIn(rootEl);
    initFlashAutoDismiss(rootEl);
    spaMountAlpine(rootEl);
    document.dispatchEvent(new CustomEvent('clinic:close-confirm'));
    rootEl.dispatchEvent(
        new CustomEvent('spa:navigated', {
            bubbles: true,
            detail: { url: resolved, root: rootEl },
        }),
    );
}

/**
 * @param {string} html
 * @param {string} resolved
 * @param {boolean} push
 */
function spaApplyRawHtml(html, resolved, push) {
    const payload = spaResolveFetchedHtml(html);
    if (!payload) {
        window.location.assign(resolved);
        return;
    }
    spaApplyPayload(payload, resolved, push);
}

function spaFetchHtml(url, signal) {
    return fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        cache: 'no-store',
        signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html, application/xhtml+xml',
        },
    }).then((res) => {
        if (res.status === 401 || res.status === 403) {
            window.location.assign(url);
            return Promise.reject(new Error('auth'));
        }
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.text();
    });
}

/**
 * @param {string} url
 * @param {AbortSignal|undefined} signal
 */
function spaLoadFragmentOnce(url, signal) {
    const cached = spaCacheGet(url);
    if (cached) {
        return Promise.resolve(cached);
    }

    const cacheKey = spaCacheKey(url);
    const shared = spaInflightLoads.get(cacheKey);
    if (shared) {
        return shared.then((html) => {
            if (html) return html;
            return spaFetchHtml(url, signal);
        });
    }

    const p = spaFetchHtml(url, signal)
        .then((html) => {
            if (html) {
                spaCacheSet(url, html);
            }
            return html;
        })
        .catch((err) => {
            if (err?.name === 'AbortError') throw err;
            return null;
        })
        .finally(() => {
            spaInflightLoads.delete(cacheKey);
        });

    spaInflightLoads.set(cacheKey, p);
    return p;
}

function prefetchFragment(url) {
    const cacheKey = spaCacheKey(url);
    if (!spaSameOrigin(url) || spaCacheGet(url)) return;
    if (spaInflightLoads.has(cacheKey)) return;
    spaLoadFragmentOnce(url, undefined).catch(() => {});
}

function schedulePrefetch(url) {
    if (!spaSameOrigin(url) || spaCacheGet(url)) return;
    if (spaPrefetchTimers[url]) cancelAnimationFrame(spaPrefetchTimers[url]);
    spaPrefetchTimers[url] = requestAnimationFrame(() => {
        delete spaPrefetchTimers[url];
        prefetchFragment(url);
    });
}

function spaLinkEligibleForPrefetch(link) {
    return link instanceof HTMLAnchorElement && !spaShouldFullNavigation(link);
}

function warmSidebarPrefetch() {
    const run = window.requestIdleCallback
        ? (cb) => requestIdleCallback(cb, { timeout: 2500 })
        : (cb) => setTimeout(cb, 300);
    run(() => {
        const links = Array.from(document.querySelectorAll('#sidebar a[href], #app-navbar a[href]')).filter(
            (el) => el instanceof HTMLAnchorElement && spaLinkEligibleForPrefetch(el),
        );
        links.forEach((link, idx) => {
            window.setTimeout(() => {
                prefetchFragment(link.href);
            }, idx * 150);
        });
    });
}

/**
 * @param {string} url
 * @param {boolean} push
 * @param {{ skipCache?: boolean }} opts
 */
export function loadPage(url, push = true, opts = {}) {
    if (!getSpaMode()) return;

    const resolved = new URL(url, location.href).toString();

    const cachedHtml = !opts.skipCache ? spaCacheGet(resolved) : null;
    if (cachedHtml) {
        spaApplyRawHtml(cachedHtml, resolved, push);
        return;
    }

    if (spaInflightUrl === resolved) {
        return;
    }

    if (spaFetchController) {
        spaFetchController.abort();
    }
    spaFetchController = new AbortController();
    const { signal } = spaFetchController;
    spaInflightUrl = resolved;
    const gen = ++spaNavGeneration;

    scheduleDeferredProgress();

    spaLoadFragmentOnce(resolved, signal)
        .then((html) => {
            if (gen !== spaNavGeneration) return;
            if (!html) {
                window.location.assign(resolved);
                return;
            }
            spaApplyRawHtml(html, resolved, push);
        })
        .catch((err) => {
            if (err?.name === 'AbortError') return;
            if (gen !== spaNavGeneration) return;
            window.location.assign(resolved);
        })
        .finally(() => {
            if (gen !== spaNavGeneration) {
                return;
            }
            hideLoader();
            spaInflightUrl = null;
            spaFetchController = null;
        });
}

export function initSpaNavigation() {
    if (spaIsAuthBoundaryPath(location.pathname)) {
        return;
    }
    if (!getSpaMode()) return;

    document.addEventListener(
        'pointerdown',
        (e) => {
            const link = e.target.closest('a[href]');
            if (!(link instanceof HTMLAnchorElement) || !spaLinkEligibleForPrefetch(link)) return;
            if (
                !link.closest('#sidebar') &&
                !link.closest('#app-navbar') &&
                !link.hasAttribute('data-spa-prefetch')
            ) {
                return;
            }
            if (e.button !== 0) return;
            prefetchFragment(link.href);
        },
        { capture: true, passive: true },
    );

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!(link instanceof HTMLAnchorElement) || e.defaultPrevented || e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (spaShouldFullNavigation(link)) return;

        e.preventDefault();
        loadPage(link.href, true);
    });

    document.addEventListener(
        'mouseover',
        (e) => {
            const link = e.target.closest('a[href]');
            if (!(link instanceof HTMLAnchorElement) || !spaLinkEligibleForPrefetch(link)) return;
            if (
                !link.closest('#sidebar') &&
                !link.closest('#app-navbar') &&
                !link.hasAttribute('data-spa-prefetch')
            ) {
                return;
            }
            schedulePrefetch(link.href);
        },
        { passive: true },
    );

    window.addEventListener('popstate', () => {
        loadPage(location.href, false);
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.method.toUpperCase() !== 'GET') return;
        if (form.hasAttribute('data-no-spa')) return;
        const inApp = form.closest('#app-content');
        const inShell = form.closest('#locale-swap-root');
        if (!inApp && !inShell) return;

        e.preventDefault();
        const action = form.getAttribute('action') || location.pathname;
        const next = new URL(action, location.href);
        const fd = new FormData(form);
        next.search = new URLSearchParams(fd).toString();
        loadPage(next.toString(), true, { skipCache: true });
    });

    updateSidebarActive();
    warmSidebarPrefetch();

    const bootRoot = document.getElementById('app-content') ?? document.getElementById('locale-swap-root');
    if (bootRoot) {
        void spaMountDashboardCharts(bootRoot);
        void spaMountAppointmentCalendar(bootRoot);
        void spaMountAppointmentSlots(bootRoot);
    }
}
