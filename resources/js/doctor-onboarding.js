/**
 * Doctor onboarding: switch between new account vs existing user.
 */
export function initDoctorOnboardingForm() {
    const form = document.getElementById('doctor-onboarding-form');
    if (!form) {
        return;
    }

    const blockNew = document.getElementById('onboarding-block-new');
    const blockExisting = document.getElementById('onboarding-block-existing');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const userSelect = document.getElementById('user_id');
    const emailHint = document.getElementById('onboarding-existing-email');
    const modes = form.querySelectorAll('input[name="account_mode"]');
    const emailLabel = form.dataset.existingEmailLabel || '';

    function setMode(mode) {
        const isNew = mode === 'new';
        blockNew?.classList.toggle('hidden', !isNew);
        blockExisting?.classList.toggle('hidden', isNew);
        if (emailInput) {
            emailInput.required = isNew;
            emailInput.disabled = !isNew;
        }
        if (passwordInput) {
            passwordInput.required = isNew;
        }
        if (userSelect) {
            userSelect.required = !isNew;
        }
        if (!isNew) {
            syncExistingEmail();
        }
    }

    function syncExistingEmail() {
        if (!userSelect || !emailHint) {
            return;
        }
        const opt = userSelect.selectedOptions[0];
        const email = opt?.dataset?.email || '';
        emailHint.textContent = email && emailLabel ? `${emailLabel}: ${email}` : '';
    }

    modes.forEach((r) => r.addEventListener('change', () => setMode(r.value)));
    userSelect?.addEventListener('change', syncExistingEmail);
    setMode(form.querySelector('input[name="account_mode"]:checked')?.value || 'new');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDoctorOnboardingForm);
} else {
    initDoctorOnboardingForm();
}
