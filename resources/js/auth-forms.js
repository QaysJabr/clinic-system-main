/**
 * Guest auth forms: show loading UI without disabling submit (disabling aborts native POST).
 */
export function initAuthForms() {
    document.querySelectorAll('form[data-auth-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.authBound === '1') return;
        form.dataset.authBound = '1';

        const showLoading = () => {
            form.classList.add('is-auth-submitting');
            form.querySelectorAll('.auth-submit-btn').forEach((btn) => {
                btn.setAttribute('aria-busy', 'true');
                btn.querySelector('.auth-submit-btn__label')?.classList.add('hidden');
                btn.querySelector('.auth-submit-btn__loading')?.classList.remove('hidden');
            });
        };

        const hideLoading = () => {
            form.classList.remove('is-auth-submitting');
            form.querySelectorAll('.auth-submit-btn').forEach((btn) => {
                btn.setAttribute('aria-busy', 'false');
                btn.querySelector('.auth-submit-btn__label')?.classList.remove('hidden');
                btn.querySelector('.auth-submit-btn__loading')?.classList.add('hidden');
            });
        };

        hideLoading();

        form.addEventListener('submit', showLoading);
        window.addEventListener('pageshow', () => hideLoading());
        form.addEventListener('invalid', hideLoading, true);
    });
}
