/**
 * Live payroll totals preview on staff payment create/edit forms (SPA-safe).
 */

const FIELD_IDS = ['base_amount', 'bonus', 'deduction', 'paid_amount'];

/** @type {WeakMap<HTMLElement, () => void>} */
const listeners = new WeakMap();

function fieldEl(scope, id) {
    if (scope instanceof Document) {
        return scope.getElementById(id);
    }

    return scope.querySelector(`#${CSS.escape(id)}`) ?? document.getElementById(id);
}

function num(scope, id) {
    const el = fieldEl(scope, id);
    if (!el) {
        return 0;
    }

    const v = parseFloat(String(el.value).replace(',', '.'));
    return Number.isNaN(v) ? 0 : v;
}

function fmt(n) {
    return (Math.round(n * 100) / 100).toFixed(2);
}

function previewEl(scope, id) {
    if (scope instanceof Document) {
        return document.getElementById(id);
    }

    return scope.querySelector(`#${CSS.escape(id)}`) ?? document.getElementById(id);
}

function recalc(scope) {
    const base = num(scope, 'base_amount');
    const bonus = num(scope, 'bonus');
    const ded = num(scope, 'deduction');
    const paid = num(scope, 'paid_amount');
    const total = base + bonus - ded;
    const rem = total - paid;

    const td = previewEl(scope, 'sp_total_due');
    const rm = previewEl(scope, 'sp_remaining');

    if (td) {
        td.textContent = fmt(total);
    }

    if (rm) {
        rm.textContent = fmt(rem);
        rm.classList.toggle('text-red-600', rem < -0.0000001);
        rm.classList.toggle('dark:text-red-400', rem < -0.0000001);
        rm.classList.toggle('text-gray-900', rem >= -0.0000001);
        rm.classList.toggle('dark:text-[#F3F4F6]', rem >= -0.0000001);
    }
}

function unbindFields(scope) {
    FIELD_IDS.forEach((id) => {
        const el = fieldEl(scope, id);
        if (!el) {
            return;
        }

        const handler = listeners.get(el);
        if (!handler) {
            return;
        }

        el.removeEventListener('input', handler);
        el.removeEventListener('change', handler);
        listeners.delete(el);
    });
}

/**
 * @param {Document|ParentNode} [root]
 */
export function initStaffPayrollPreview(root = document) {
    const scope = root instanceof Document ? document : root;

    if (!previewEl(scope, 'sp_total_due')) {
        return;
    }

    unbindFields(scope);

    const handler = () => recalc(scope);
    FIELD_IDS.forEach((id) => {
        const el = fieldEl(scope, id);
        if (!el) {
            return;
        }

        listeners.set(el, handler);
        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });

    recalc(scope);
}
