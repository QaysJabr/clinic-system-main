/**
 * Pricing cards: choose billing cycle + subscribe via JSON (Stripe URL or manual).
 * Uses document-level delegation so it works after SPA/locale swaps without re-binding.
 */

const billingCycleByRoot = new WeakMap();

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function tMsg(key, fallback) {
    return window.AppI18n?.messages?.[key] ?? fallback ?? key;
}

function getDefaultCycle(root) {
    return root.dataset.defaultCycle === 'yearly' ? 'yearly' : 'monthly';
}

function getBillingCycle(root) {
    return billingCycleByRoot.get(root) ?? getDefaultCycle(root);
}

function setBillingCycle(root, cycle) {
    billingCycleByRoot.set(root, cycle);
}

function syncCycleUI(root) {
    const billingCycle = getBillingCycle(root);

    root.querySelectorAll('[data-cycle-set]').forEach((b) => {
        const on = b.getAttribute('data-cycle-set') === billingCycle;
        b.classList.toggle('ring-2', on);
        b.classList.toggle('ring-[#0F4C81]', on);
        b.classList.toggle('bg-[#0F4C81]', on);
        b.classList.toggle('text-white', on);
        b.classList.toggle('dark:bg-[#3B82F6]', on);
        b.classList.toggle('dark:ring-[#3B82F6]', on);
        b.classList.toggle('border-[#0F4C81]', on);
        b.classList.toggle('border-slate-200', !on);
        b.classList.toggle('bg-white', !on);
        b.classList.toggle('text-slate-800', !on);
        b.classList.toggle('dark:border-[#4B5563]', !on);
        b.classList.toggle('dark:bg-[#111827]', !on);
        b.classList.toggle('dark:text-[#F3F4F6]', !on);
    });

    root.querySelectorAll('[data-price-panel]').forEach((panel) => {
        const show = panel.getAttribute('data-price-panel') === billingCycle;
        panel.classList.toggle('hidden', !show);
    });
}

function setMessage(msgEl, text, isError) {
    if (!msgEl) return;
    msgEl.textContent = text || '';
    msgEl.classList.toggle('hidden', !text);
    msgEl.classList.toggle('border-red-200', !!isError);
    msgEl.classList.toggle('bg-red-50', !!isError);
    msgEl.classList.toggle('text-red-900', !!isError);
    msgEl.classList.toggle('dark:border-red-900/50', !!isError);
    msgEl.classList.toggle('dark:bg-red-950/35', !!isError);
    msgEl.classList.toggle('dark:text-red-100', !!isError);
    msgEl.classList.toggle('border-emerald-200', !isError && !!text);
    msgEl.classList.toggle('bg-emerald-50', !isError && !!text);
    msgEl.classList.toggle('text-emerald-900', !isError && !!text);
}

function eventTargetElement(e) {
    const t = e.target;
    if (t instanceof Element) return t;
    if (t instanceof Node && t.parentElement) return t.parentElement;
    return null;
}

let delegationBound = false;

function bindSaasPricingDelegation() {
    if (delegationBound) return;
    delegationBound = true;

    // capture: true — يعمل قبل أي stopPropagation على العناصر الداخلية
    document.addEventListener('click', async (e) => {
        const el = eventTargetElement(e);
        const root = el?.closest('#saas-pricing-root');
        if (!root) return;

        const cycleBtn = el?.closest('[data-cycle-set]');
        if (cycleBtn && root.contains(cycleBtn)) {
            const cycle = cycleBtn.getAttribute('data-cycle-set') === 'yearly' ? 'yearly' : 'monthly';
            setBillingCycle(root, cycle);
            syncCycleUI(root);
            return;
        }

        const btn = el?.closest('.saas-choose-plan');
        if (!btn || !root.contains(btn)) return;

        const url = btn.getAttribute('data-checkout-url');
        if (!url) return;

        const msgEl = root.querySelector('#saas-pricing-message');
        const billingCycle = getBillingCycle(root);
        const promoInput = document.getElementById('saas-promotion-code');
        const promotionCode = promoInput?.value?.trim() || '';

        btn.disabled = true;
        setMessage(msgEl, '', false);
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    billing_cycle: billingCycle,
                    promotion_code: promotionCode || null,
                }),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                setMessage(msgEl, json.message || tMsg('saasCheckoutFail', ''), true);

                return;
            }
            if (json.data?.mode === 'stripe' && json.data?.url) {
                window.location.href = json.data.url;

                return;
            }
            if (json.data?.mode === 'manual' || json.data?.mode === 'swapped') {
                setMessage(msgEl, json.message || tMsg('saasManualActivated', ''), false);
                setTimeout(() => {
                    window.location.href = root.dataset.billingUrl || '/billing';
                }, 600);

                return;
            }
            setMessage(msgEl, json.message || tMsg('saasGenericOk', ''), false);
        } catch {
            setMessage(msgEl, tMsg('saasNetworkError', ''), true);
        } finally {
            btn.disabled = false;
        }
    }, true);
}

/** يُستدعى من Blade عند الحاجة (onclick) إذا تعطّل التفويض لأي سبب */
function windowSetSaasPricingCycle(root, cycle) {
    if (!root || (cycle !== 'yearly' && cycle !== 'monthly')) return;
    bindSaasPricingDelegation();
    setBillingCycle(root, cycle);
    syncCycleUI(root);
}

if (typeof window !== 'undefined') {
    window.__saasPricingSetCycle = windowSetSaasPricingCycle;
}

function initSaasPricing() {
    bindSaasPricingDelegation();
    document.querySelectorAll('#saas-pricing-root').forEach((root) => {
        if (!billingCycleByRoot.has(root)) {
            billingCycleByRoot.set(root, getDefaultCycle(root));
        }
        syncCycleUI(root);
    });
}

function bootSaasPricing() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSaasPricing);
    } else {
        initSaasPricing();
    }
    document.addEventListener('spa:navigated', initSaasPricing);
}

bootSaasPricing();
