/**
 * Admin plans CRUD (JSON + fetch).
 */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function t(key, fallback) {
    return window.AppI18n?.messages?.[key] ?? fallback;
}

function showAlert(root, message, isError) {
    const el = root.querySelector('#admin-plans-alert');
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('hidden', !message);
    el.classList.toggle('border-red-200', !!isError);
    el.classList.toggle('bg-red-50', !!isError);
    el.classList.toggle('text-red-900', !!isError);
    el.classList.toggle('dark:border-red-900/50', !!isError);
    el.classList.toggle('dark:bg-red-950/35', !!isError);
    el.classList.toggle('dark:text-red-100', !!isError);
    el.classList.toggle('border-slate-200', !isError);
    el.classList.toggle('bg-slate-50', !isError);
}

function clearFieldErrors(form) {
    form.querySelectorAll('[data-error-for]').forEach((el) => el.remove());
}

function showFieldErrors(form, errors) {
    if (!errors || typeof errors !== 'object') return;
    for (const [field, messages] of Object.entries(errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;
        const small = document.createElement('p');
        small.className = 'mt-1 text-xs text-red-700 dark:text-red-300';
        small.dataset.errorFor = field;
        small.textContent = Array.isArray(messages) ? messages.join(' ') : String(messages);
        input.insertAdjacentElement('afterend', small);
    }
}

function planUrls(base, id) {
    const b = base.replace(/\/$/, '');

    return {
        update: `${b}/${id}`,
        destroy: `${b}/${id}`,
        toggle: `${b}/${id}/toggle-active`,
    };
}

function limitsCell(p) {
    const mp = p.max_patients != null ? p.max_patients : '∞';
    const mu = p.max_users != null ? p.max_users : '∞';

    return `${t('patientsLabel', 'Patients')} ${mp} / ${t('usersLabel', 'Users')} ${mu}`;
}

function stripeCell(p) {
    const m = p.stripe_price_id ? `${t('monthly', 'Monthly')} ✓` : `${t('monthly', 'Monthly')} —`;
    const y = p.stripe_price_yearly_id ? `${t('yearly', 'Yearly')} ✓` : `${t('yearly', 'Yearly')} —`;

    return `${m} · ${y}`;
}

function renderRows(tbody, plans) {
    tbody.innerHTML = '';
    if (!plans.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-slate-500 dark:text-[#9CA3AF]">${t('noResults', 'No results found')}</td></tr>`;

        return;
    }
    for (const p of plans) {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100 dark:border-[#374151]';
        const active = !!p.is_active;
        tr.innerHTML = `
            <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-[#9CA3AF]">${p.sort_order}</td>
            <td class="px-4 py-3 font-medium text-slate-900 dark:text-[#F3F4F6]">${escapeHtml(p.name)}</td>
            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-[#9CA3AF]" dir="ltr">${escapeHtml(p.slug)}</td>
            <td class="px-4 py-3 tabular-nums">${escapeHtml(p.display_monthly)}</td>
            <td class="px-4 py-3 tabular-nums">${escapeHtml(p.display_yearly)}</td>
            <td class="px-4 py-3 text-xs text-slate-600 dark:text-[#9CA3AF]">${escapeHtml(limitsCell(p))}</td>
            <td class="px-4 py-3 text-xs text-slate-600 dark:text-[#9CA3AF]">${escapeHtml(stripeCell(p))}</td>
            <td class="px-4 py-3">${active ? `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/45 dark:text-emerald-300">${t('active', 'Active')}</span>` : `<span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">${t('inactive', 'Inactive')}</span>`}</td>
            <td class="px-4 py-3 tabular-nums text-slate-600 dark:text-[#9CA3AF]">${p.clinics_count}</td>
            <td class="px-4 py-3 align-top">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="admin-plans-toggle rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-800 hover:bg-slate-100 dark:border-[#374151] dark:bg-[#1F2937] dark:text-[#E5E7EB] dark:hover:bg-[#374151]" data-id="${p.id}">${active ? t('deactivate', 'Deactivate') : t('activate', 'Activate')}</button>
                    <button type="button" class="admin-plans-edit rounded border border-[#0F4C81]/30 bg-[#0F4C81]/10 px-2 py-1 text-xs font-semibold text-[#0F4C81] hover:bg-[#0F4C81]/15 dark:border-[#3B82F6]/40 dark:bg-blue-950/30 dark:text-[#93C5FD]" data-id="${p.id}">${t('edit', 'Edit')}</button>
                    <button type="button" class="admin-plans-delete rounded border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-800 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200" data-id="${p.id}" data-name="${escapeHtml(p.name)}">${t('delete', 'Delete')}</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    }
}

function escapeHtml(s) {
    return String(s)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function parseFeaturesTextarea(text) {
    return String(text || '')
        .split(/\r?\n/)
        .map((l) => l.trim())
        .filter(Boolean);
}

function findPlanById(store, id) {
    if (!store || id == null || id === '') return null;

    return store[id] ?? store[String(id)] ?? store[Number(id)] ?? null;
}

/** Move modal to body so fixed positioning is not clipped by #app-content overflow. */
function portalModal(root) {
    const existing = document.getElementById('admin-plans-modal');
    if (existing && existing.parentElement === document.body) {
        return existing;
    }
    if (existing && existing !== root.querySelector('#admin-plans-modal')) {
        existing.remove();
    }

    const modal = root.querySelector('#admin-plans-modal');
    if (!modal) return null;

    document.body.appendChild(modal);

    return modal;
}

function lockBodyScroll(lock) {
    document.documentElement.classList.toggle('overflow-hidden', lock);
    document.body.classList.toggle('overflow-hidden', lock);
}

function setModalOpen(open) {
    const modal = document.getElementById('admin-plans-modal');
    if (!modal) return;
    modal.classList.toggle('is-open', open);
    modal.hidden = !open;
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    lockBodyScroll(open);
}

function openModal(root, mode, plan) {
    const modal = portalModal(root);
    const title = document.getElementById('admin-plans-modal-title');
    const subtitle = document.getElementById('admin-plans-modal-subtitle');
    const form = document.getElementById('admin-plans-form');
    const submitBtn = document.getElementById('admin-plans-modal-submit');
    if (!modal || !title || !form) return;

    form.reset();
    clearFieldErrors(form);

    const idField = document.getElementById('admin-plans-field-id');
    if (idField) idField.value = mode === 'edit' && plan ? String(plan.id) : '';

    if (mode === 'edit' && plan) {
        title.textContent = t('editPlanTitle', 'Edit plan');
        if (subtitle) {
            subtitle.textContent = plan.name ?? '';
            subtitle.classList.remove('hidden');
        }
        if (submitBtn) submitBtn.textContent = t('save', 'Save');
    } else {
        title.textContent = t('createPlanTitle', 'Add new plan');
        if (subtitle) {
            subtitle.textContent = '';
            subtitle.classList.add('hidden');
        }
        if (submitBtn) submitBtn.textContent = t('create', 'Create');
    }

    if (mode === 'edit' && plan) {
        document.getElementById('admin-plans-field-name').value = plan.name ?? '';
        document.getElementById('admin-plans-field-slug').value = plan.slug ?? '';
        document.getElementById('admin-plans-field-pm').value = plan.price_monthly ?? '';
        document.getElementById('admin-plans-field-py').value = plan.price_yearly ?? '';
        document.getElementById('admin-plans-field-mp').value = plan.max_patients ?? '';
        document.getElementById('admin-plans-field-mu').value = plan.max_users ?? '';
        document.getElementById('admin-plans-field-features').value = Array.isArray(plan.features) ? plan.features.join('\n') : '';
        document.getElementById('admin-plans-field-trial').value = plan.trial_days ?? 0;
        document.getElementById('admin-plans-field-sort').value = plan.sort_order ?? 0;
        document.getElementById('admin-plans-field-stripe-m').value = plan.stripe_price_id ?? '';
        document.getElementById('admin-plans-field-stripe-y').value = plan.stripe_price_yearly_id ?? '';
        document.getElementById('admin-plans-field-active').checked = !!plan.is_active;
    } else {
        document.getElementById('admin-plans-field-active').checked = true;
    }

    setModalOpen(true);

    window.setTimeout(() => {
        document.getElementById('admin-plans-field-name')?.focus();
    }, 50);
}

function closeModal() {
    setModalOpen(false);
}

function cleanupAdminPlansModal() {
    closeModal();
    const modal = document.getElementById('admin-plans-modal');
    if (modal?.parentElement === document.body && !document.getElementById('admin-plans-page')) {
        modal.remove();
    }
}

function buildPayload(form) {
    const fd = new FormData(form);
    const features = parseFeaturesTextarea(fd.get('features_text'));
    const activeEl = form.querySelector('#admin-plans-field-active');

    return {
        name: fd.get('name'),
        slug: fd.get('slug'),
        price_monthly: fd.get('price_monthly'),
        price_yearly: fd.get('price_yearly'),
        max_patients: fd.get('max_patients') === '' ? null : Number(fd.get('max_patients')),
        max_users: fd.get('max_users') === '' ? null : Number(fd.get('max_users')),
        features,
        trial_days: fd.get('trial_days') === '' ? 0 : Number(fd.get('trial_days')),
        stripe_price_id: fd.get('stripe_price_id') || null,
        stripe_price_yearly_id: fd.get('stripe_price_yearly_id') || null,
        sort_order: fd.get('sort_order') === '' ? 0 : Number(fd.get('sort_order')),
        is_active: !!(activeEl && 'checked' in activeEl && activeEl.checked),
    };
}

async function loadPlans(root) {
    const url = root.dataset.urlData;
    const tbody = root.querySelector('#admin-plans-tbody');
    const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || t('error', 'An error occurred'));
    const plans = json.data.plans || [];
    root._adminPlansById = Object.fromEntries(plans.map((p) => [p.id, p]));
    renderRows(tbody, plans);
}

let adminPlansEscapeBound = false;

function initAdminPlans() {
    const root = document.getElementById('admin-plans-page');
    if (!root || root.dataset.init === '1') return;
    root.dataset.init = '1';

    portalModal(root);

    const tbody = root.querySelector('#admin-plans-tbody');
    const base = root.dataset.urlBase || '';

    loadPlans(root).catch((e) => {
        showAlert(root, e.message || t('error', 'An error occurred'), true);
        tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-red-600">${t('error', 'An error occurred')}</td></tr>`;
    });

    root.querySelector('#admin-plans-btn-new')?.addEventListener('click', () => openModal(root, 'create', null));

    document.getElementById('admin-plans-modal-close')?.addEventListener('click', () => closeModal());
    document.getElementById('admin-plans-modal-cancel')?.addEventListener('click', () => closeModal());
    const modalEl = document.getElementById('admin-plans-modal');
    modalEl?.addEventListener('click', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (
            target === modalEl ||
            target.classList.contains('admin-plans-modal__backdrop') ||
            target.classList.contains('admin-plans-modal__dialog')
        ) {
            closeModal();
        }
    });
    modalEl?.querySelector('.admin-plans-modal__panel')?.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    if (!adminPlansEscapeBound) {
        adminPlansEscapeBound = true;
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            const modal = document.getElementById('admin-plans-modal');
            if (modal?.classList.contains('is-open')) closeModal();
        });
    }

    document.getElementById('admin-plans-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton?.disabled) return;
        clearFieldErrors(form);
        const id = form.querySelector('#admin-plans-field-id')?.value;
        const payload = buildPayload(form);
        const isEdit = !!id;
        const url = isEdit ? planUrls(base, id).update : root.dataset.urlStore;
        const method = isEdit ? 'PUT' : 'POST';

        try {
            if (submitButton) submitButton.disabled = true;
            const res = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                if (json.errors) showFieldErrors(form, json.errors);
                const msg = json.message || (json.errors ? Object.values(json.errors).flat().join(' ') : t('error', 'An error occurred'));
                showAlert(root, msg, true);
                return;
            }
            showAlert(root, json.message || t('success', 'Completed successfully'), false);
            closeModal();
            await loadPlans(root);
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    root.addEventListener('click', async (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;

        if (target.classList.contains('admin-plans-toggle')) {
            const id = target.dataset.id;
            const url = planUrls(base, id).toggle;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                showAlert(root, json.message || t('error', 'An error occurred'), true);

                return;
            }
            showAlert(root, json.message || t('success', 'Completed successfully'), false);
            await loadPlans(root);
        }

        if (target.classList.contains('admin-plans-edit')) {
            const plan = findPlanById(root._adminPlansById, target.dataset.id);
            if (plan) {
                openModal(root, 'edit', plan);
            } else {
                showAlert(root, t('error', 'An error occurred'), true);
            }
        }

        if (target.classList.contains('admin-plans-delete')) {
            const id = target.dataset.id;
            const name = target.dataset.name || '';
            const promptTpl = t('adminPlanDeletePrompt', 'Permanently delete plan ":name"?');
            if (!window.confirm(promptTpl.replace(':name', name))) return;
            const url = planUrls(base, id).destroy;
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok || !json.success) {
                showAlert(root, json.message || t('adminPlanDeleteFail', ''), true);

                return;
            }
            showAlert(root, json.message || t('adminPlanDeleted', ''), false);
            await loadPlans(root);
        }
    });
}

document.addEventListener('DOMContentLoaded', initAdminPlans);
document.addEventListener('spa:navigated', () => {
    cleanupAdminPlansModal();
    initAdminPlans();
});
