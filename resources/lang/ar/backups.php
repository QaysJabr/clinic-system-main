<?php

return [
    'page_title' => 'النسخ الاحتياطية',
    'subtitle' => 'إنشاء نسخة من قاعدة البيانات وتنزيلها أو حذفها.',

    'hint_db' => 'قاعدة البيانات النشطة: :connection (:driver)',
    'hint_storage' => 'الملفات تُخزَّن على خادم التطبيق. نزّل نسخاً للاحتفاظ خارج الخادم.',
    'hint_schedule' => 'نسخة تلقائية يومياً الساعة :time — يُحتفظ بآخر :days يوماً. تأكد من تشغيل مجدول Laravel (cron).',

    'stat_count' => 'عدد ملفات النسخ',
    'stat_total_size' => 'الحجم الإجمالي على القرص',
    'stat_latest' => 'أحدث نسخة',
    'stat_latest_none' => '—',

    'section_files' => 'الملفات',

    'empty_title' => 'لا توجد نسخ احتياطية',
    'empty_hint' => 'أنشئ نسخة لالتقاط قاعدة البيانات الحالية. التنزيل مناسب للأرشفة خارج الخادم.',

    'btn_create' => 'إنشاء نسخة احتياطية',

    'col_filename' => 'اسم الملف',
    'col_size' => 'الحجم',
    'col_modified' => 'آخر تعديل',
    'col_actions' => 'إجراءات',

    'btn_download' => 'تنزيل',
    'btn_delete' => 'حذف',

    'confirm_delete_title' => 'حذف نسخة احتياطية',
    'confirm_delete_body' => 'حذف هذه النسخة نهائياً؟',
    'empty' => 'لا توجد نسخ احتياطية. أنشئ نسخة جديدة من الزر أعلاه.',

    'error_generic' => 'تحقق من إعدادات قاعدة البيانات وأداة النسخ الاحتياطي على الخادم.',
    'error_pg_dump' => 'تعذر الاتصال بقاعدة PostgreSQL أو تشغيل أداة النسخ. تأكد أن PostgreSQL يعمل على هذا الجهاز.',
    'error_mysql' => 'تعذر تشغيل أداة نسخ MySQL. تأكد من إعدادات قاعدة البيانات.',
    'error_restrict_key' => 'تعذر تشغيل أداة PostgreSQL للنسخ الاحتياطي. تأكد أن عميل PostgreSQL مثبت وأن قاعدة البيانات تعمل.',

    'audit_create' => 'إنشاء نسخة احتياطية: :filename',
    'audit_download' => 'تنزيل نسخة احتياطية: :filename',
    'audit_delete' => 'حذف نسخة احتياطية: :filename',

    'flash_created' => 'تم إنشاء النسخة الاحتياطية بنجاح: :filename',
    'flash_create_failed' => 'تعذر إنشاء النسخة الاحتياطية. :message',
    'flash_delete_failed' => 'تعذر حذف الملف.',
    'flash_deleted' => 'تم حذف النسخة الاحتياطية بنجاح.',

    'notify_success_title' => 'نسخة احتياطية ناجحة',
    'notify_failure_title' => 'فشل النسخ الاحتياطي',
    'notify_success_message' => 'تم إنشاء نسخة قاعدة البيانات (:file) في :time. حُذفت :pruned نسخة قديمة.',
    'notify_failure_message' => 'فشل النسخ الاحتياطي التلقائي في :time. السبب: :error',

    'mail_success_subject' => ':app — نسخة احتياطية ناجحة',
    'mail_failure_subject' => ':app — فشل النسخ الاحتياطي',
    'mail_success_heading' => 'نسخة احتياطية ناجحة',
    'mail_failure_heading' => 'تنبيه: فشل النسخ الاحتياطي',
    'mail_open_app' => 'فتح النظام',
    'mail_whatsapp_cta' => 'إبلاغ الفريق على واتساب',
    'mail_footer' => 'هذا تنبيه تلقائي من مجدول النسخ الاحتياطي.',
];
