/**
 * Toggle doctor-specific fields when role_type is "doctor" on staff create/edit forms.
 */
export function initStaffDoctorFields() {
    const roleSelect = document.getElementById('role_type');
    const doctorBlock = document.getElementById('staff-doctor-fields');
    if (!roleSelect || !doctorBlock) {
        return;
    }

    const toggle = () => doctorBlock.classList.toggle('hidden', roleSelect.value !== 'doctor');
    roleSelect.addEventListener('change', toggle);
    toggle();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStaffDoctorFields);
} else {
    initStaffDoctorFields();
}
