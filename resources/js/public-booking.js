/**
 * Public self-booking slot picker.
 */

async function fetchSlots(url, doctorId, date) {
    const params = new URLSearchParams({ doctor_id: doctorId, date });
    const res = await fetch(`${url}?${params}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) return [];
    const data = await res.json();
    return data.slots ?? [];
}

function boot() {
    const form = document.querySelector('[data-public-booking]');
    if (!form) return;

    const url = form.dataset.slotsUrl;
    const doctorSelect = form.querySelector('#doctor_id');
    const dateInput = form.querySelector('#appointment_date');
    const startInput = form.querySelector('#start_time');
    const grid = form.querySelector('#public-booking-slots');
    const hint = form.querySelector('#public-booking-slots-hint');
    const loading = form.querySelector('#public-booking-slots-loading');
    const emptyMsg = form.querySelector('#public-booking-slots-empty');

    const slotBtnClass =
        'rounded-lg border border-[#0F4C81]/40 bg-white px-2 py-2.5 text-xs font-semibold text-[#0F4C81] shadow-sm transition hover:bg-blue-50 dark:border-[#3B82F6]/50 dark:bg-slate-800 dark:text-[#93C5FD] dark:hover:bg-[#1E3A5F]/50';

    function showState(state) {
        hint?.classList.toggle('hidden', state !== 'hint');
        loading?.classList.toggle('hidden', state !== 'loading');
        grid?.classList.toggle('hidden', state !== 'grid');
        emptyMsg?.classList.toggle('hidden', state !== 'empty');
    }

    async function render() {
        const doctorId = doctorSelect?.value;
        const date = dateInput?.value;

        if (!doctorId || !date || !url) {
            grid.innerHTML = '';
            showState('hint');
            return;
        }

        const previousSelection = startInput.value;
        grid.innerHTML = '';
        startInput.value = '';

        showState('loading');

        const slots = await fetchSlots(url, doctorId, date);
        const available = slots.filter((s) => s.available);

        if (!available.length) {
            showState('empty');
            return;
        }

        showState('grid');

        available.forEach((slot) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = slot.start;
            btn.className = slotBtnClass;
            btn.addEventListener('click', () => {
                startInput.value = slot.start;
                grid.querySelectorAll('button').forEach((b) => {
                    b.classList.remove('ring-2', 'ring-[#0F4C81]', 'dark:ring-[#3B82F6]');
                });
                btn.classList.add('ring-2', 'ring-[#0F4C81]', 'dark:ring-[#3B82F6]');
            });
            grid.appendChild(btn);
        });

        if (previousSelection) {
            grid.querySelectorAll('button').forEach((btn) => {
                if (btn.textContent === previousSelection) {
                    btn.click();
                }
            });
        }
    }

    doctorSelect?.addEventListener('change', render);
    dateInput?.addEventListener('change', render);
    dateInput?.addEventListener('input', render);

    render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
