/**
 * Clinic theme: class-based dark mode, persisted in localStorage.
 * Must align with inline script in resources/views/partials/theme-init.blade.php
 */
const STORAGE_KEY = 'clinic-theme';

function applyTheme(mode) {
    const root = document.documentElement;
    if (mode === 'dark') {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
}

/** Call after DOM; syncs class with localStorage / system preference */
export function initTheme() {
    if (typeof document !== 'undefined' && document.body?.getAttribute('data-force-theme') === 'light') {
        applyTheme('light');
        return;
    }

    let mode = 'light';
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            mode = stored;
        } else {
            mode = window.matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark'
                : 'light';
        }
    } catch {
        mode = 'light';
    }
    applyTheme(mode);
}

export function setTheme(mode) {
    try {
        localStorage.setItem(STORAGE_KEY, mode === 'dark' ? 'dark' : 'light');
    } catch {
        /* ignore */
    }
    applyTheme(mode === 'dark' ? 'dark' : 'light');
    document.dispatchEvent(new CustomEvent('clinic:theme-changed'));
}

export function toggleTheme() {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    setTheme(next);
    return next;
}

export function bindGlobalToggle() {
    window.clinicToggleTheme = () => {
        toggleTheme();
    };
    window.clinicSetTheme = (mode) => {
        setTheme(mode);
    };

    // CSP-safe: no inline onclick handlers required.
    if (!document.body.dataset.themeToggleBound) {
        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;
            const toggleBtn = target.closest('[data-theme-toggle]');
            if (!toggleBtn) return;
            toggleTheme();
        });
        document.body.dataset.themeToggleBound = '1';
    }
}
