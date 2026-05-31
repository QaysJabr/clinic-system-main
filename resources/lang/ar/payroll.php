<?php

return [
    'title' => 'الرواتب',
    'nav' => 'الرواتب',

    'page_title_index' => 'رواتب الموظفين',
    'page_title_create' => 'تسجيل دفعة رواتب',
    'page_title_edit' => 'تعديل دفعة رواتب',

    'cycle_weekly' => 'أسبوعي',
    'cycle_monthly' => 'شهري',

    'runs_page_title' => 'فترات الرواتب',
    'runs_subtitle' => 'تعريف فترة دفع (أسبوعية أو شهرية)، ثم توليد بنود الدفعات للموظفين الذين تطابق <span class="font-semibold">دورة الدفع</span> في ملف التعويض مع نوع الفترة هنا.',
    'runs_flash_could_not_save' => 'تعذر الحفظ',
    'runs_section_register_period' => 'تسجيل فترة',
    'runs_heading_new_period' => 'فترة جديدة',
    'runs_field_period_kind' => 'نوع الفترة',
    'runs_field_period_from' => 'بداية الفترة',
    'runs_field_period_until' => 'نهاية الفترة',
    'runs_notes_optional' => 'ملاحظات (اختياري)',
    'runs_submit_register' => 'تسجيل الفترة',
    'runs_registered_periods_heading' => 'الفترات المسجّلة',
    'runs_payroll_cycles_table_heading' => 'فترات الرواتب',
    'runs_link_staff_payments' => 'سجل الدفعات',
    'runs_th_from' => 'من',
    'runs_th_until' => 'إلى',
    'runs_th_notes' => 'ملاحظات',
    'runs_th_line_items' => 'البنود',
    'runs_generate_items' => 'توليد البنود',
    'runs_empty_no_periods' => 'لا توجد فترات مسجّلة بعد.',
    'runs_th_action' => 'إجراء',

    'runs_validation_period_exists' => 'هذه الفترة مسجّلة مسبقاً.',
    'runs_flash_period_saved_draft' => 'تم تسجيل فترة الرواتب (مسودة). استخدم «توليد البنود» لإنشاء سجلات الدفعات.',
    'runs_audit_generate_summary' => 'توليد رواتب #:id: جديد :created، موجود :existing، غير نشط :inactive، قبل الملف :before_profile، ملف ناقص :incomplete.',
    'runs_flash_generate_summary' => 'تم التوليد: :created بند جديد. تخطّي: :existing موجود، :inactive غير نشط، :before_profile قبل بداية الملف، :incomplete ملف ناقص.',

    'run_status_draft' => 'مسودة',
    'run_status_generated' => 'مُولَّد',

    'role_doctor' => 'طبيب',
    'role_receptionist' => 'استقبال',
    'role_accountant' => 'محاسب',
    'role_nurse' => 'ممرض / ممرضة',
    'role_worker' => 'عامل',
    'role_cleaner' => 'عامل نظافة',
    'role_assistant' => 'مساعد',
    'role_other' => 'أخرى',

    'period_range_separator' => '—',
    'period_date_arrow' => '←',

    'status_completed' => 'مكتمل',
    'status_partial' => 'جزئي',
    'status_unpaid' => 'غير مدفوع',

    'notifications' => [
        'current_period_note' => ' (الفترة الحالية)',
        'outstanding_partial_title' => 'راتب مدفوع جزئياً',
        'outstanding_partial_body' => 'راتب الموظف :staff عن :period:note متبقٍ :remaining:currency_suffix من المستحق.',
        'outstanding_unpaid_title' => 'راتب غير مكتمل الدفع',
        'outstanding_unpaid_body' => 'راتب الموظف :staff عن :period:note لم يُسدد المستحق بالكامل — المتبقي :remaining:currency_suffix.',
        'settled_title' => 'اكتمال دفع الراتب',
        'settled_body' => 'تم سداد راتب الموظف :staff بالكامل للفترة :period.',
    ],

    'validation_deduction_vs_base_bonus' => 'لا يمكن أن يتجاوز الخصم مجموع الأساس والعلاوة.',
    'validation_paid_exceeds_due' => 'المبلغ المدفوع لا يجوز أن يتجاوز المستحق (الأساس + العلاوة − الخصم).',
    'validation_negative_remaining' => 'المتبقي لا يجوز أن يكون سالباً.',
    'validation_duplicate_period' => 'يوجد بالفعل سجل دفعة لنفس الموظف ونفس فترة البداية والنهاية.',

    'flash_created' => 'تم تسجيل دفعة الرواتب بنجاح.',
    'flash_updated' => 'تم تحديث دفعة الرواتب بنجاح.',
    'flash_deleted' => 'تم حذف دفعة الرواتب بنجاح.',

    'audit_create' => 'تسجيل دفعة رواتب — موظف :staff_id — :period',
    'audit_update' => 'تحديث دفعة رواتب رقم :id',
    'audit_delete' => 'حذف دفعة رواتب رقم :id',

    'reports_dashboard_accrual_label' => 'الرواتب المسجّلة',
    'reports_dashboard_accrual_hint' => 'رواتب الموظفين المسجّلة محاسبياً (استحقاق).',
    'reports_row_payroll_accrual' => 'رواتب مسجّلة (استحقاق)',
    'reports_row_payroll_cash' => 'صرف رواتب (نقد)',
    'reports_calendar_row_payroll_accrual' => 'رواتب مسجّلة (استحقاق)',
    'reports_calendar_row_payroll_cash' => 'صرف رواتب (نقد)',
    'reports_calendar_row_operating_accrual_hint' => 'حصص أطباء + مصروفات مسجّلة + رواتب مسجّلة',
    'reports_row_expense_payroll_cash_sub' => 'دفعات مصروفات + صرف رواتب + تسويات أطباء (كل الوقت)',
    'reports_profitability_note' => 'صفوف الربحية: إيراد وحصص ومصروفات/رواتب مسجّلة (استحقاق) وتكلفة تشغيل استحقاق وصافي ربح. صفوف النقد: تحصيل من المرضى، دفعات مصروفات، صرف رواتب، تسويات أطباء، إجمالي صادر، صافي تدفق نقدي. لا تُخلط الأعمدة بين الطبقتين. «آخر 30 يوماً» تشمل اليوم.',

    'reports_table_row_doctor_share_expense' => 'حصص الأطباء (مصروف)',
    'reports_table_row_accrued_expenses' => 'مصروفات مسجّلة (استحقاق)',
    'reports_table_row_accrued_payroll' => 'رواتب مسجّلة (استحقاق)',
    'reports_th_payroll_accrual' => 'رواتب (استحقاق)',
    'reports_table_row_expense_cash_out' => 'دفعات مصروفات (نقد)',
    'reports_table_row_payroll_cash_out' => 'صرف رواتب (نقد)',
    'reports_operating_cost_accrual_title' => 'تكلفة التشغيل (استحقاق)',
    'reports_operating_cost_accrual_sub' => 'حصص + مصروفات مسجّلة + رواتب مسجّلة',

    'reports_summary_expenses_accrual_short' => 'مجموع المصروفات في الجدول',
    'reports_summary_payroll_accrual_short' => 'سجلات الرواتب — منفصل عن حصص النسبة',
    'reports_summary_operating_mix' => 'حصص أطباء + مصروفات مسجّلة + رواتب مسجّلة',
    'reports_cashflow_patient_in' => 'تحصيل من المرضى (دفعات)',
    'reports_cashflow_out_sub' => 'دفعات مصروفات + صرف رواتب + تسويات أطباء مدفوعة',

    'dashboard_nav_payroll' => 'الرواتب',
    'dashboard_accrual_cards_title' => 'حصص الأطباء + المصروفات + الرواتب (استحقاق)',
    'dashboard_net_profit_line' => 'صافي الربح',
    'dashboard_net_profit_formula' => '= الإيرادات − حصص الأطباء − المصروفات − الرواتب.',
    'dashboard_net_profit_operating_card_note' => 'بطاقة «تكلفة التشغيل الكاملة» أدناه تجمع (حصص + مصروفات + رواتب) للمقارنة السريعة.',

    'th_staff' => 'الموظف',
    'th_period' => 'الفترة',
    'th_total_due' => 'المستحق',
    'th_paid' => 'المدفوع',
    'th_remaining' => 'المتبقي',
    'th_status' => 'الحالة',
    'th_pay_date' => 'تاريخ الدفع',
    'th_actions' => 'الإجراءات',
    'btn_add_record' => 'تسجيل دفعة',
    'filter_role' => 'الدور',
    'filter_period_type' => 'نوع الفترة',
    'filter_period_from' => 'من تاريخ الفترة',
    'filter_period_to' => 'إلى تاريخ الفترة',

    'nav_sidebar' => 'الرواتب / المدفوعات',

    'subtitle_index' => 'تسجيل يدوي لدفعات الرواتب حسب الموظف والفترة (أسبوعية أو شهرية).',

    'stat_month_paid' => 'مدفوع هذا الشهر',
    'stat_month_due' => 'مستحق هذا الشهر',
    'stat_outstanding' => 'بمتبقي مستحق',
    'stat_month_records' => 'سجلات هذا الشهر',
    'quick_this_month' => 'هذا الشهر',
    'empty_title' => 'لا توجد سجلات رواتب',

    'list_section_label' => 'سجل الرواتب',
    'table_main_heading' => 'رواتب الموظفين',

    'link_compensation_profiles' => 'نماذج التعويض',
    'link_payroll_runs' => 'فترات الرواتب',

    'th_staff_full_name' => 'اسم الموظف',
    'th_role_type' => 'نوع الدور',
    'th_period_kind' => 'نوع الفترة',
    'th_period_start' => 'بداية الفترة',
    'th_period_end' => 'نهاية الفترة',
    'th_base' => 'الأساس',
    'th_bonus' => 'علاوة',
    'th_deduction' => 'خصم',

    'subtitle_create' => 'إدخال يدوي للأساس والعلاوة والخصم والمدفوع؛ يُحسب المستحق والمتبقي تلقائياً.',

    'alert_no_staff_title' => 'لا يوجد موظفون في السجل',
    'alert_no_staff_body' => 'يجب إضافة موظف من شاشة الموظفين قبل تسجيل أي دفعة رواتب.',

    'form_section_payment_data' => 'بيانات الدفعة',

    'placeholder_select_staff' => '— اختر الموظف —',

    'field_period_start_short' => 'بداية الفترة',
    'field_period_end_short' => 'نهاية الفترة',

    'field_compensation_profile_optional' => 'ملف تعويض (اختياري)',
    'placeholder_none' => '— بدون —',

    'confirm_delete_record_title' => 'حذف سجل رواتب',
    'confirm_delete_record_body' => 'حذف هذا السجل؟',
    'no_matching_records' => 'لا توجد دفعات مطابقة.',

    'btn_save_payroll_record' => 'حفظ',
    'btn_update_payroll_record' => 'تحديث',

    'field_days_worked_optional' => 'أيام العمل (اختياري)',
    'field_base_amount' => 'المبلغ الأساسي',
    'field_bonus_optional' => 'علاوة (اختياري)',
    'field_deduction_optional' => 'خصم (اختياري)',
    'field_paid_required' => 'المبلغ المدفوع',
    'field_payment_date_optional' => 'تاريخ الدفع (اختياري)',
    'field_payment_method_optional' => 'طريقة الدفع (اختياري)',
    'field_notes_optional' => 'ملاحظات (اختياري)',
    'btn_back_to_list' => 'رجوع',
    'btn_add_staff' => 'إضافة موظف',

    'preview_title' => 'معاينة الحساب',
    'preview_total_due_label' => 'المستحق:',
    'preview_remaining_label' => 'المتبقي:',
    'preview_hint' => 'المستحق = الأساس + العلاوة − الخصم. المتبقي = المستحق − المدفوع.',

    'reports_cash_cards_out_subnote' => 'مصروفات + رواتب + تسويات أطباء (نقد فقط)',
];
