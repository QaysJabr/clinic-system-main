<?php

return [
    'title' => 'Settings',
    'nav_sidebar' => 'Settings',

    'clinic_page_title' => 'System settings',

    'page_heading' => 'System settings',
    'hero_subtitle' => 'Clinic information used on invoices, printouts, and reports.',

    'nav_aria' => 'Settings sections',
    'nav_clinic' => 'Clinic settings',
    'nav_users' => 'Users',
    'nav_audit' => 'Audit log',
    'nav_backups' => 'Backups',

    'stat_clinic' => 'Clinic',
    'stat_no_logo' => '—',
    'stat_currency' => 'Currency',
    'stat_opening_cash' => 'Opening cash',
    'stat_rules_active' => 'Active rules',

    'error_save_failed_title' => 'Could not save settings',

    'section_identity' => 'Identity & branding',
    'section_contact' => 'Contact',
    'section_finance' => 'Finance',
    'section_documents' => 'Documents & printouts',
    'section_scheduling' => 'Scheduling & reminders',
    'section_public_booking' => 'Online booking link',
    'label_slot_minutes' => 'Default slot length (minutes)',
    'label_buffer_minutes' => 'Buffer between appointments (minutes)',
    'label_day_start' => 'Day starts at',
    'label_day_end' => 'Day ends at',
    'checkbox_reminders_label' => 'Send appointment reminders',
    'checkbox_reminders_help' => 'Email reminders to the clinic owner inbox before upcoming appointments.',
    'checkbox_overbooking_label' => 'Allow overbooking',
    'checkbox_overbooking_help' => 'When enabled, the system may allow overlapping appointments (use with caution).',
    'public_booking_intro' => 'Generate a shareable link so patients can book by name and phone without staff login.',
    'public_booking_generate' => 'Generate booking link',
    'public_booking_link_label' => 'Your public booking URL',
    'public_booking_link_hint' => 'Share this link on WhatsApp, your website, or reception desk. Copy and store it — a new link replaces the previous token.',
    'flash_booking_link_created' => 'Public booking link created. Copy it below.',
    'audit_public_booking_link' => 'Generated public booking link',
    'section_clinic_data' => 'Clinic details',
    'label_clinic_name' => 'Clinic name',
    'label_clinic_logo' => 'Clinic logo',
    'logo_current_hint' => 'Current logo — upload a new file to replace it.',
    'logo_alt' => 'Clinic logo',
    'logo_file_hint' => 'Common image format (PNG, JPG…) — optional, up to 4 MB',

    'label_phone' => 'Phone',
    'label_email' => 'Email',
    'label_address' => 'Address',
    'label_currency' => 'Currency',
    'currency_placeholder' => 'e.g. SAR / USD',

    'label_opening_cash' => 'Opening cash balance (till)',
    'opening_cash_help' => 'Used for cash balance on the dashboard: opening balance plus cumulative net flow from payments in the system.',

    'label_invoice_notes' => 'Invoice notes',
    'label_report_footer' => 'Reports footer',

    'section_visit_invoice_rules' => 'Visit & invoice rules',
    'checkbox_require_invoice_visit_label' => 'Do not complete a visit without a linked invoice',
    'checkbox_require_invoice_visit_help' => 'When enabled, a visit cannot be saved as «completed» unless an invoice exists for that visit (does not apply to a new visit before first save).',
    'checkbox_one_invoice_visit_label' => 'One invoice per visit',
    'checkbox_one_invoice_visit_help' => 'Prevents creating more than one invoice for the same visit.',

    'btn_save' => 'Save settings',

    'flash_saved' => 'Settings saved successfully.',
    'audit_updated' => 'Clinic settings updated',

    /* Users (same module area) */
    'users_nav_sidebar' => 'Users',
    'users_page_title_index' => 'User management',
    'users_heading' => 'User management',
    'users_intro' => 'View accounts and assign roles within the permission system.',

    'users_stat_total' => 'Total users',
    'users_stat_admins' => 'Administrators',
    'users_stat_doctors' => 'Doctors',
    'users_stat_staff' => 'Reception & finance',
    'users_section_filter' => 'Filter',
    'users_filter_heading' => 'Search criteria',
    'users_filter_search' => 'Search',
    'users_filter_search_placeholder' => 'Name or email…',
    'users_filter_all_roles' => 'All roles',
    'users_filter_apply' => 'Apply',
    'users_filter_reset' => 'Reset',
    'users_results_count' => 'Showing :from–:to of :total',
    'users_you_badge' => 'You',
    'users_back_to_list' => 'Back to users',
    'users_role_clinic_owner' => 'Clinic owner',
    'users_empty_title' => 'No users',
    'users_empty_filtered' => 'No users match your filters.',

    'users_section_list' => 'User list',
    'users_panel_title' => 'All users',
    'users_btn_add' => 'Add user',

    'users_th_name' => 'Name',
    'users_th_email' => 'Email',
    'users_th_role' => 'Role',
    'users_th_created' => 'Created',
    'users_th_actions' => 'Actions',

    'users_action_edit' => 'Edit',
    'users_action_delete' => 'Delete',

    'users_confirm_delete_title' => 'Delete user',
    'users_confirm_delete' => 'Delete user «:name» (:email)? This cannot be undone.',

    'users_empty' => 'No users.',

    'users_create_heading' => 'Add user',
    'users_create_intro' => 'Grant login to an existing staff member, or create a standalone account.',
    'users_create_intro_staff' => 'Name and email are taken from the staff record when linking.',
    'users_create_hint_title' => 'Link staff to login',
    'users_create_hint_body' => 'If you already added the doctor under Staff/Doctors, pick them here and set a password only — do not re-type name and email.',
    'users_account_mode_legend' => 'How to add',
    'users_mode_from_staff' => 'Link existing staff (recommended)',
    'users_mode_new_account' => 'New account without staff',
    'users_pick_staff' => 'Select staff member',
    'users_pick_staff_placeholder' => '— choose from staff list —',
    'users_staff_no_email' => 'no email',
    'users_no_staff_without_login' => 'No staff without login. Add staff first or use “New account”.',
    'users_doctor_onboarding_title' => 'Brand-new doctor',
    'users_doctor_onboarding_body' => 'To create doctor + staff + clinical record from scratch, use doctor onboarding — not an empty user here.',
    'users_doctor_onboarding_link' => 'Open doctor onboarding',
    'users_doctor_use_onboarding' => 'New doctors must be added via doctor onboarding, not manual user creation.',
    'users_error_staff_already_linked' => 'This staff member already has a linked user.',
    'users_error_staff_needs_email' => 'Add an email on the staff record first (edit staff), then link the account.',
    'users_error_staff_email_taken' => 'The staff email is already used by another user.',
    'users_flash_linked_staff' => 'Login created and linked to staff «:name».',
    'audit_user_create_from_staff' => 'User from staff: :email ← :staff',

    'users_create_section' => 'User details',

    'users_section_identity' => 'Identity',
    'users_section_access' => 'Access & credentials',
    'users_member_since' => 'Member since',
    'users_password_optional_hint' => 'Leave blank to keep the current password.',
    'users_btn_create' => 'Create user',

    'users_label_password' => 'Password',
    'users_label_password_confirm' => 'Confirm password',
    'users_label_role' => 'Role',
    'users_role_placeholder' => '— Choose role —',

    'users_create_error_banner' => 'Could not save data',

    'users_edit_heading' => 'Edit user',
    'users_edit_intro' => 'Update name, email, and role; leave password blank to keep it unchanged.',
    'users_edit_section' => 'User details',

    'users_label_password_optional' => 'Password (optional)',

    'users_edit_error_banner' => 'Could not save changes',

    'users_flash_created' => 'User created and role assigned successfully.',
    'users_flash_updated' => 'User updated successfully.',
    'users_flash_deleted' => 'User deleted.',

    'users_error_plan_limit' => 'The maximum number of users for your current subscription plan has been reached.',

    'users_error_cannot_demote_last_admin' => 'This user\'s role cannot be changed: at least one admin must remain for this clinic.',
    'users_error_cannot_delete_self' => 'You cannot delete your account here. Use «danger zone» in your profile if you want to delete the account.',
    'users_error_cannot_delete_platform_owner' => 'The platform owner account cannot be deleted from user management.',
    'users_error_cannot_delete_last_admin' => 'The last administrator for this clinic cannot be deleted.',

    'audit_user_create' => 'Create user: :email',
    'audit_user_update' => 'Update user: :email',
    'audit_user_delete' => 'Delete user: :email',
];
