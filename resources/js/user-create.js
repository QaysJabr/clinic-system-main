/**
 * User create: link existing staff vs new account; sync name/email/role from staff.
 */
export function initUserCreateForm() {
    const form = document.getElementById('user-create-form');
    if (!form) {
        return;
    }

    const staffData = JSON.parse(form.dataset.staffPicker || '[]');
    const blockFromStaff = document.getElementById('user-block-from-staff');
    const blockNew = document.getElementById('user-block-new');
    const blockDoctorHint = document.getElementById('user-doctor-onboarding-hint');
    const staffSelect = document.getElementById('staff_id');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role');
    const staffPreview = document.getElementById('user-staff-preview');
    const modes = form.querySelectorAll('input[name="account_mode"]');

    const roleForStaffType = {
        doctor: 'doctor',
        receptionist: 'receptionist',
        accountant: 'accountant',
        nurse: 'receptionist',
        worker: 'receptionist',
        cleaner: 'receptionist',
        assistant: 'receptionist',
        other: 'receptionist',
    };

    function currentMode() {
        return form.querySelector('input[name="account_mode"]:checked')?.value || 'from_staff';
    }

    function setMode(mode) {
        const fromStaff = mode === 'from_staff';
        blockFromStaff?.classList.toggle('hidden', !fromStaff);
        blockNew?.classList.toggle('hidden', fromStaff);

        if (nameInput) {
            nameInput.required = !fromStaff;
            nameInput.readOnly = fromStaff;
        }
        if (emailInput) {
            emailInput.required = !fromStaff;
            emailInput.readOnly = fromStaff;
        }
        if (staffSelect) {
            staffSelect.required = fromStaff;
            staffSelect.disabled = !fromStaff;
        }

        syncFromStaff();
        syncDoctorHint();
    }

    function syncFromStaff() {
        if (currentMode() !== 'from_staff' || !staffSelect) {
            return;
        }

        const row = staffData.find((s) => String(s.id) === staffSelect.value);
        if (!row) {
            if (nameInput) {
                nameInput.value = '';
            }
            if (emailInput) {
                emailInput.value = '';
            }
            if (staffPreview) {
                staffPreview.classList.add('hidden');
                staffPreview.textContent = '';
            }
            return;
        }

        if (nameInput) {
            nameInput.value = row.full_name || '';
        }
        if (emailInput) {
            emailInput.value = row.email || '';
        }
        if (roleSelect && roleForStaffType[row.role_type]) {
            roleSelect.value = roleForStaffType[row.role_type];
        }
        if (staffPreview) {
            staffPreview.classList.remove('hidden');
            const email = row.email || form.dataset.noEmailLabel || '—';
            staffPreview.textContent = `${row.full_name} · ${email}`;
        }
    }

    function syncDoctorHint() {
        if (!blockDoctorHint || !roleSelect) {
            return;
        }
        const show = currentMode() === 'new' && roleSelect.value === 'doctor';
        blockDoctorHint.classList.toggle('hidden', !show);
    }

    modes.forEach((r) => r.addEventListener('change', () => setMode(r.value)));
    staffSelect?.addEventListener('change', syncFromStaff);
    roleSelect?.addEventListener('change', syncDoctorHint);

    setMode(currentMode());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserCreateForm);
} else {
    initUserCreateForm();
}
