/**
 * FullCalendar v6 — clinic appointment scheduling.
 */
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

/** @type {Calendar|null} */
let calendarInstance = null;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function urlWithId(template, id) {
    return template.replace('__ID__', String(id));
}

async function jsonFetch(url, options = {}) {
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
        ...options,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg = data?.message || data?.errors?.start_time?.[0] || data?.errors?.status?.[0] || 'Request failed';
        throw new Error(msg);
    }
    return data;
}

/**
 * @param {HTMLElement} root
 */
export function initAppointmentCalendar(root) {
    const mount = root.querySelector('#appointment-calendar');
    if (!mount) return;

    destroyAppointmentCalendar();

    const eventsUrl = root.dataset.eventsUrl;
    const rescheduleTpl = root.dataset.rescheduleUrl;
    const statusTpl = root.dataset.statusUrl;
    const checkInTpl = root.dataset.checkInUrl;
    const locale = root.dataset.locale || 'en';
    const dir = root.dataset.dir || 'ltr';
    const initialView = root.dataset.initialView || 'timeGridWeek';
    const doctorFilter = root.querySelector('#calendar-doctor-filter');
    const modal = root.querySelector('#appointment-calendar-modal');
    const modalTitle = root.querySelector('#appointment-calendar-modal-title');
    const modalMeta = root.querySelector('#appointment-calendar-modal-meta');
    const modalStatus = root.querySelector('#appointment-calendar-status');
    const modalError = root.querySelector('#appointment-calendar-modal-error');
    const btnClose = root.querySelector('#appointment-calendar-modal-close');
    const btnSave = root.querySelector('#appointment-calendar-save-status');
    const btnCheckIn = root.querySelector('#appointment-calendar-check-in');
    const btnEdit = root.querySelector('#appointment-calendar-edit');

    /** @type {import('@fullcalendar/core').EventApi|null} */
    let activeEvent = null;

    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    calendarInstance = new Calendar(mount, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        locale,
        direction: dir,
        initialView: isMobile ? 'listWeek' : initialView,
        headerToolbar: isMobile
            ? { left: 'prev,next today', center: 'title', right: 'listWeek,dayGridMonth' }
            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
        height: 'auto',
        expandRows: true,
        nowIndicator: true,
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        allDaySlot: false,
        editable: true,
        eventDurationEditable: true,
        eventStartEditable: true,
        selectable: true,
        selectMirror: true,
        events: (info, success, failure) => {
            const doctorId = doctorFilter?.value || '';
            const params = new URLSearchParams({
                start: info.startStr.slice(0, 10),
                end: info.endStr.slice(0, 10),
            });
            if (doctorId) params.set('doctor_id', doctorId);
            fetch(`${eventsUrl}?${params}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((r) => r.json())
                .then(success)
                .catch(failure);
        },
        eventDrop: async (info) => {
            try {
                const data = await jsonFetch(urlWithId(rescheduleTpl, info.event.id), {
                    method: 'PATCH',
                    body: JSON.stringify({
                        start: info.event.start?.toISOString(),
                        end: info.event.end?.toISOString(),
                    }),
                });
                if (data.event) info.event.setProp('backgroundColor', data.event.backgroundColor);
            } catch (e) {
                info.revert();
                alert(e.message);
            }
        },
        eventResize: async (info) => {
            try {
                await jsonFetch(urlWithId(rescheduleTpl, info.event.id), {
                    method: 'PATCH',
                    body: JSON.stringify({
                        start: info.event.start?.toISOString(),
                        end: info.event.end?.toISOString(),
                    }),
                });
            } catch (e) {
                info.revert();
                alert(e.message);
            }
        },
        eventClick: (info) => {
            activeEvent = info.event;
            modalTitle.textContent = info.event.title;
            modalMeta.textContent = info.event.start?.toLocaleString(locale) ?? '';
            modalStatus.value = info.event.extendedProps?.status ?? 'scheduled';
            btnEdit.href = info.event.extendedProps?.editUrl ?? '#';
            modalError.classList.add('hidden');
            modal?.showModal();
        },
        dateClick: (info) => {
            const createUrl = root.dataset.createUrl;
            if (!createUrl) return;
            const date = info.dateStr.slice(0, 10);
            window.location.href = `${createUrl}?appointment_date=${date}`;
        },
    });

    calendarInstance.render();

    doctorFilter?.addEventListener('change', () => {
        calendarInstance?.refetchEvents();
    });

    btnClose?.addEventListener('click', () => modal?.close());
    btnSave?.addEventListener('click', async () => {
        if (!activeEvent) return;
        modalError.classList.add('hidden');
        try {
            const data = await jsonFetch(urlWithId(statusTpl, activeEvent.id), {
                method: 'PATCH',
                body: JSON.stringify({ status: modalStatus.value }),
            });
            if (data.event) {
                activeEvent.setProp('backgroundColor', data.event.backgroundColor);
                activeEvent.setProp('borderColor', data.event.borderColor);
                activeEvent.setExtendedProp('status', data.status);
            }
            modal?.close();
            calendarInstance?.refetchEvents();
        } catch (e) {
            modalError.textContent = e.message;
            modalError.classList.remove('hidden');
        }
    });

    btnCheckIn?.addEventListener('click', async () => {
        if (!activeEvent) return;
        modalError.classList.add('hidden');
        try {
            const data = await jsonFetch(urlWithId(checkInTpl, activeEvent.id), { method: 'POST', body: '{}' });
            if (data.visit_url) window.location.href = data.visit_url;
            else {
                modal?.close();
                calendarInstance?.refetchEvents();
            }
        } catch (e) {
            modalError.textContent = e.message;
            modalError.classList.remove('hidden');
        }
    });
}

export function destroyAppointmentCalendar() {
    if (calendarInstance) {
        calendarInstance.destroy();
        calendarInstance = null;
    }
}

function boot() {
    const root = document.querySelector('[data-appointment-calendar]');
    if (root) initAppointmentCalendar(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
