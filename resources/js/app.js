import './bootstrap';
import { initTheme, bindGlobalToggle } from './theme';
import { initSpaNavigation } from './spa-navigation';
import { initInternalChat } from './internal-chat';
import { initFlashAutoDismiss } from './flash-auto-dismiss';
import { registerClinicConfirmDialog } from './confirm-dialog';
import { initSidebarMobileDelegation } from './sidebar-delegation';
import { initHeaderNotifications } from './header-notifications';
import { initAuthForms } from './auth-forms';
import { initStaffPayrollPreview } from './staff-payroll-preview';
import './locale-switch';
import './saas-pricing';
import './inventory-purchase-form';
import './saas-billing';

async function mountClinicDashboardCharts(root = document) {
    const scope =
        root instanceof Document ? (root.getElementById('app-content') ?? root.documentElement) : root;
    if (!scope?.querySelector?.('[data-clinic-dashboard]')) {
        return;
    }
    const mod = await import('./clinic-dashboard');
    mod.destroyClinicDashboardCharts?.();
    mod.initClinicDashboard(scope);
}

initTheme();
bindGlobalToggle();

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    registerClinicConfirmDialog(Alpine);
});

window.Alpine = Alpine;

Alpine.start();

function resetBodyScrollLock() {
    document.body.classList.remove('overflow-y-hidden');
}

document.addEventListener(
    'submit',
    (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-portal-link-form')) return;
        const btn = form.querySelector('button[type="submit"]');
        if (btn && !btn.disabled) {
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
        }
    },
    true,
);

function initBackupCreateForms(root = document) {
    const scope = root instanceof Document ? root : root;
    const forms = scope.querySelectorAll
        ? scope.querySelectorAll('[data-backup-create-form]')
        : [];

    forms.forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.backupBound === '1') {
            return;
        }
        form.dataset.backupBound = '1';
        form.addEventListener('submit', () => {
            const btn = form.querySelector('[data-backup-submit]');
            const label = form.querySelector('[data-backup-submit-label]');
            if (btn instanceof HTMLButtonElement) {
                btn.disabled = true;
            }
            if (label) {
                label.textContent =
                    document.documentElement.lang === 'ar'
                        ? 'جاري إنشاء النسخة…'
                        : 'Creating backup…';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    resetBodyScrollLock();
    document.dispatchEvent(new CustomEvent('clinic:close-confirm'));
    initAuthForms();
    initSidebarMobileDelegation();
    initSpaNavigation();
    initFlashAutoDismiss(document);
    initInternalChat();
    initHeaderNotifications(document);
    initStaffPayrollPreview(document);
    initBackupCreateForms(document);
    void mountClinicDashboardCharts(document);
});

document.addEventListener('spa:navigated', (event) => {
    const root = event.detail?.root ?? document.getElementById('app-content') ?? document;
    initFlashAutoDismiss(root);
    initHeaderNotifications(root);
    initStaffPayrollPreview(root);
    initBackupCreateForms(root);
    void mountClinicDashboardCharts(root);
});

window.addEventListener('pageshow', resetBodyScrollLock);
