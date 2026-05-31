/**
 * Billing page: cancel subscription via JSON + update summary fields.
 */

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function t(key, fallback) {
    return window.AppI18n?.messages?.[key] ?? fallback ?? key;
}

function applyBillingPayload(root, b) {
    const dash = t('emDash', '—');
    const setText = (id, val) => {
        const el = root.querySelector(`#${id}`);
        if (el) el.textContent = val ?? dash;
    };

    setText('saas-billing-plan-name', b.plan_name);
    setText('saas-billing-expires', b.expires_at);
    setText('saas-billing-days', b.days_remaining != null ? String(b.days_remaining) : dash);
    setText('saas-billing-stripe', b.stripe_status_label ?? b.stripe_status ?? dash);
    setText('saas-billing-clinic-sub-status', b.clinic_subscription_status_label ?? dash);

    const badge = root.querySelector('#saas-billing-status-badge');
    if (badge) {
        badge.textContent = b.subscription_status_label ?? dash;
        const expired =
            b.subscription_status_expired === true ||
            (b.days_remaining != null && b.days_remaining < 0);
        badge.className = expired
            ? 'inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-900 dark:bg-rose-950/45 dark:text-rose-200'
            : 'inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-900 dark:bg-emerald-950/45 dark:text-emerald-200';
    }
}

function initSaasBilling(scope = document) {
    const root = scope.querySelector?.('#saas-billing-root') ?? document.getElementById('saas-billing-root');
    if (!root || root.dataset.saasBillingBound === '1') return;
    root.dataset.saasBillingBound = '1';

    const cancelUrl = root.dataset.cancelUrl;
    const btn = root.querySelector('#saas-billing-cancel-btn');
    const msg = root.querySelector('#saas-billing-action-message');

    btn?.addEventListener('click', async () => {
        if (!cancelUrl) return;
        if (!window.confirm(t('saasCancelConfirm', ''))) return;

        btn.disabled = true;
        if (msg) {
            msg.textContent = '';
            msg.classList.add('hidden');
        }
        try {
            const res = await fetch(cancelUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                if (msg) {
                    msg.textContent = json.message || t('saasCancelFail', '');
                    msg.classList.remove('hidden');
                }

                return;
            }
            if (json.data?.billing) applyBillingPayload(root, json.data.billing);
            if (msg) {
                msg.textContent = json.message || t('saasCancelled', '');
                msg.classList.remove('hidden');
            }
        } catch {
            if (msg) {
                msg.textContent = t('saasNetworkError', '');
                msg.classList.remove('hidden');
            }
        } finally {
            btn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', initSaasBilling);
document.addEventListener('spa:navigated', (event) => {
    const root = event.detail?.root ?? document.getElementById('app-content') ?? document;
    initSaasBilling(root);
});
