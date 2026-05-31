/**
 * App-wide confirm dialog for <form data-confirm="..."> (optional data-confirm-title).
 * Single document listener + Alpine store (avoids duplicate handlers / stale state).
 */

function defaultHeading() {
    const raw = window.AppI18n?.messages?.confirmDelete;
    if (typeof raw === 'string' && raw.trim() !== '') {
        return raw.trim();
    }
    return 'Confirm action';
}

/** @param {typeof import('alpinejs').default} AlpineInstance */
function bindConfirmSubmitListener(AlpineInstance) {
    if (document.documentElement.dataset.clinicConfirmBound === '1') {
        return;
    }
    document.documentElement.dataset.clinicConfirmBound = '1';

    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (form.dataset.confirmBypass === '1') {
                return;
            }
            if (!form.hasAttribute('data-confirm')) {
                return;
            }
            const message = form.getAttribute('data-confirm');
            if (message === null || message.trim() === '') {
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            const titleAttr = form.getAttribute('data-confirm-title');
            AlpineInstance.store('clinicConfirm').open({
                form,
                body: message.trim(),
                heading:
                    titleAttr && titleAttr.trim() !== '' ? titleAttr.trim() : defaultHeading(),
            });
        },
        true,
    );

    const close = () => AlpineInstance.store('clinicConfirm').close();
    document.addEventListener('spa:navigated', close);
    document.addEventListener('clinic:close-confirm', close);
}

export function registerClinicConfirmDialog(AlpineInstance) {
    AlpineInstance.store('clinicConfirm', {
        dialogVisible: false,
        headingText: '',
        bodyText: '',
        /** @type {HTMLFormElement|null} */
        pendingForm: null,

        open({ form, body, heading }) {
            this.pendingForm = form;
            this.bodyText = body;
            this.headingText = heading;
            this.dialogVisible = true;
            document.body.classList.add('overflow-y-hidden');
        },

        close() {
            this.pendingForm = null;
            this.bodyText = '';
            this.headingText = '';
            this.dialogVisible = false;
            document.body.classList.remove('overflow-y-hidden');
        },

        confirm() {
            const form = this.pendingForm;
            if (!form) {
                this.close();
                return;
            }
            form.dataset.confirmBypass = '1';
            this.close();
            HTMLFormElement.prototype.submit.call(form);
        },
    });

    bindConfirmSubmitListener(AlpineInstance);

    AlpineInstance.data('clinicConfirmDialog', () => ({
        init() {
            AlpineInstance.store('clinicConfirm').close();
        },

        get dialogVisible() {
            const store = AlpineInstance.store('clinicConfirm');
            return store.dialogVisible && (store.bodyText || '').trim() !== '';
        },

        get headingText() {
            return AlpineInstance.store('clinicConfirm').headingText;
        },

        get bodyText() {
            return AlpineInstance.store('clinicConfirm').bodyText;
        },

        cancel() {
            AlpineInstance.store('clinicConfirm').close();
        },

        confirmSubmit() {
            AlpineInstance.store('clinicConfirm').confirm();
        },
    }));
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.Alpine) {
        window.Alpine.store('clinicConfirm')?.close();
    }
});
