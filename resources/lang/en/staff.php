<?php

return [
    'title' => 'Staff',
    'page_list' => 'Staff',
    'page_create' => 'Add staff member',
    'page_edit' => 'Edit staff member',

    'subtitle' => 'Your clinic team — identity here; compensation is managed in compensation profiles.',

    'nav_aria' => 'Staff and payroll navigation',
    'nav_list' => 'Staff',
    'nav_compensation' => 'Compensation profiles',
    'nav_payments' => 'Payment log',
    'back_to_list' => 'Back to list',

    'stat_total' => 'Total staff',
    'stat_active' => 'Active',
    'stat_inactive' => 'Inactive',
    'stat_with_profile' => 'With compensation profile',

    'section_filter' => 'Filter results',
    'section_search_criteria' => 'Search criteria',
    'search_label' => 'Search',
    'search_placeholder' => 'Name, phone, email, or linked user…',
    'filter_all_roles' => 'All roles',
    'filter_all_status' => 'All statuses',
    'btn_search' => 'Search',
    'btn_reset' => 'Reset',
    'results_count' => ':from–:to of :total',
    'empty_title' => 'No staff members',
    'empty_filtered' => 'No results match your filters.',

    'col_contact' => 'Contact',
    'no_compensation_profile' => 'No compensation profile',

    'compensation_card_title' => 'Compensation profile',
    'compensation_card_missing' => 'No compensation profile defined yet.',
    'compensation_card_edit' => 'Edit compensation profile',
    'compensation_card_add' => 'Add compensation profile',

    'flash_created' => 'Staff member added successfully.',
    'flash_created_doctor' => 'Doctor added (staff + clinical profile) successfully.',
    'flash_updated' => 'Staff member updated successfully.',
    'flash_updated_doctor_linked' => 'Staff member and linked doctor profile updated.',
    'flash_deleted' => 'Staff member deleted.',

    'doctor_section_title' => 'Clinical doctor details',
    'doctor_section_intro' => 'Specialty, license, and room — name and contact are managed in the staff fields above.',

    'section_list_kicker' => 'Staff roster',
    'all_records' => 'All records',
    'add_staff_btn' => 'Add staff member',
    'add_doctor_btn' => 'Add doctor',
    'doctor_use_onboarding' => 'Doctors must be added via the doctor onboarding wizard (login + staff + clinical record).',
    'doctor_add_via_onboarding_title' => 'Adding a doctor',
    'doctor_add_via_onboarding_body' => 'Doctors who need system login must use the dedicated onboarding wizard — do not pick the doctor role here.',
    'doctor_add_via_onboarding_link' => 'Open doctor onboarding',
    'doctor_no_login_title' => 'No login account linked',
    'doctor_no_login_body' => 'To let this doctor sign in and see appointments, link a user account via doctor onboarding.',
    'doctor_no_login_link' => 'Link login account',

    'col_full_name' => 'Full name',
    'col_user_account' => 'User account',
    'col_role_type' => 'Role type',
    'col_compensation' => 'Compensation',
    'col_status' => 'Status',
    'col_actions' => 'Actions',

    'edit_link' => 'Edit',
    'delete_link' => 'Delete',
    'delete_confirm_title' => 'Delete staff member',
    'delete_confirm_message' => 'Are you sure you want to delete staff member ":name"? Their compensation profile and linked payroll entries will be removed, and any doctor link will be cleared.',

    'inactive_compensation_profile' => '(inactive profile)',

    'status_active' => 'Active',
    'status_inactive' => 'Inactive',

    'empty_list' => 'No staff members yet.',

    'create_subtitle' => 'Enter identity and contact details — add a compensation profile later from the compensation screen.',
    'error_save_header' => 'Could not save the data',

    'card_title' => 'Staff details',
    'card_intro_create' => 'Identity fields only — compensation and payroll are handled on the compensation screen.',
    'card_intro_edit' => 'Identity and basic details only — compensation and payroll are handled on the compensation screen.',

    'label_full_name' => 'Full name',
    'label_role_type' => 'Role type',
    'label_phone' => 'Phone',
    'label_email' => 'Email',
    'label_user_link' => 'Link to user account (optional)',
    'label_status' => 'Status',

    'placeholder_select_role' => '— Choose —',
    'placeholder_no_user' => '— Not linked —',
    'hint_one_user_staff' => 'A user account can only be linked to one staff member.',

    'btn_back' => 'Back',
    'btn_save' => 'Save',
    'btn_update' => 'Update',

    'comp_percentage' => 'Percentage',
    'comp_daily' => 'Daily',
    'comp_fixed' => 'Fixed salary',

    'summary_fixed' => 'Fixed amount: :amount — :cycle',
    'summary_percentage' => 'Percentage: :rate% — basis: :basis — :cycle',
    'summary_daily' => 'Daily wage: :amount — settlement: :cycle',

    'basis_invoice_paid_total' => 'Invoice payments total',
    'basis_gross_revenue' => 'Gross revenue',

    'comp_profiles_title' => 'Staff compensation profiles',
    'comp_profiles_subtitle' => 'Manage how each employee is compensated and linked payroll.',
    'comp_profiles_kicker' => 'Profiles',
    'comp_profiles_all' => 'All compensation profiles',
    'comp_profiles_payments_log' => 'Payments log',
    'comp_profiles_payroll_periods' => 'Payroll periods',
    'comp_profiles_add' => 'Add profile',
    'comp_profiles_col_staff' => 'Staff',
    'comp_profiles_col_model' => 'Model',
    'comp_profiles_col_cycle' => 'Cycle',
    'comp_profiles_col_summary' => 'Summary',
    'comp_profiles_col_start' => 'Starts',
    'comp_profiles_empty' => 'Add a profile to link pay rules to each staff member.',
    'comp_profiles_empty_title' => 'No compensation profiles yet',
    'comp_profiles_stat_total' => 'Total profiles',
    'comp_profiles_stat_unassigned' => 'Staff without profile',
    'comp_profiles_quick_active' => 'Active profiles',
    'comp_profiles_search_placeholder' => 'Staff name or email…',
    'comp_profiles_filter_all_models' => 'All models',

    'comp_profile_new_title' => 'New compensation profile',
    'comp_profile_edit_title' => 'Edit compensation profile',
    'comp_profile_single_constraint' => 'Each staff member can have only one active profile.',
    'comp_profile_all_assigned' => 'All staff members already have a profile.',
    'comp_profile_save_error_header' => 'Unable to save this profile',

    'comp_profile_card_title' => 'Profile',
    'comp_profile_staff' => 'Staff member',
    'comp_profile_pick_staff' => 'Select staff',

    'comp_profile_model_title' => 'Compensation model',
    'comp_profile_model_pick' => 'Model',
    'comp_profile_model_fixed_hint' => 'Fixed salary settled on each payroll cycle.',
    'comp_profile_model_percentage_hint' => 'Percentage applies to your selected revenue basis.',
    'comp_profile_model_daily_hint' => 'Daily wage is counted per settlement cycle.',

    'comp_profile_cycle_title' => 'Payment cycle',
    'comp_profile_cycle_pick' => 'Cycle',
    'comp_profile_cycle_weekly_hint' => 'Weekly payout window.',
    'comp_profile_cycle_monthly_hint' => 'Monthly payout window.',

    'comp_profile_start_date' => 'Start date',
    'comp_profile_status' => 'Status',

    'comp_profile_details_title' => 'Amount details',
    'comp_profile_base_salary' => 'Base salary',
    'comp_profile_percentage' => 'Percentage rate',
    'comp_profile_basis' => 'Calculation basis',
    'comp_profile_daily_wage' => 'Daily wage',
    'comp_profile_notes' => 'Notes',

    'comp_profile_employee_locked' => 'Employee:',
    'comp_profile_employee_locked_hint' => 'cannot be changed for an existing profile.',

    'comp_profile_save_success' => 'Compensation profile created.',
    'comp_profile_update_success' => 'Compensation profile updated.',
    'comp_profile_audit_create' => 'Created compensation profile for staff #:id.',
    'comp_profile_audit_update' => 'Updated compensation profile #:id.',
];
