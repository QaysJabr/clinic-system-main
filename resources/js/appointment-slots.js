/**
 * Inline slot picker for appointment create/edit forms.
 */

async function fetchSlots(url, doctorId, date) {
    const params = new URLSearchParams({ doctor_id: doctorId, date });
    const res = await fetch(`${url}?${params}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });
    if (!res.ok) return [];
    const data = await res.json();
    return data.slots ?? [];
}

/**
 * @param {HTMLElement} root
 */
export function initAppointmentSlots(root) {
    const url = root.dataset.slotsUrl;
    const doctorSelect = document.querySelector(root.dataset.doctorSelect || '#doctor_id');
    const dateInput = document.querySelector(root.dataset.dateSelect || '#appointment_date');
    const startInput = document.querySelector(root.dataset.startSelect || '#start_time');
    const grid = root.querySelector('[data-slot-grid]');
    const emptyMsg = root.querySelector('[data-slot-empty]');

    if (!url || !doctorSelect || !dateInput || !startInput || !grid) return;

    async function render() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        grid.innerHTML = '';
        if (!doctorId || !date) {
            emptyMsg?.classList.add('hidden');
            return;
        }

        const slots = await fetchSlots(url, doctorId, date);
        if (!slots.length) {
            emptyMsg?.classList.remove('hidden');
            return;
        }
        emptyMsg?.classList.add('hidden');

        slots.forEach((slot) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = slot.start;
            btn.className = slot.available
                ? 'rounded-lg border border-[#0F4C81]/30 bg-blue-50 px-2 py-2 text-xs font-semibold text-[#0F4C81] hover:bg-blue-100 dark:bg-blue-900/20 dark:text-[#93C5FD]'
                : 'rounded-lg border border-gray-200 bg-gray-100 px-2 py-2 text-xs text-gray-400 cursor-not-allowed dark:border-[#374151] dark:bg-[#111827]';
            btn.disabled = !slot.available;
            if (startInput.value === slot.start) {
                btn.classList.add('ring-2', 'ring-[#0F4C81]');
            }
            btn.addEventListener('click', () => {
                if (!slot.available) return;
                startInput.value = slot.start;
                const endInput = document.querySelector('#end_time');
                if (endInput) endInput.value = slot.end;
                grid.querySelectorAll('button').forEach((b) => b.classList.remove('ring-2', 'ring-[#0F4C81]'));
                btn.classList.add('ring-2', 'ring-[#0F4C81]');
            });
            grid.appendChild(btn);
        });
    }

    doctorSelect.addEventListener('change', render);
    dateInput.addEventListener('change', render);
    render();
}

function boot() {
    document.querySelectorAll('[data-appointment-slots]').forEach(initAppointmentSlots);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
