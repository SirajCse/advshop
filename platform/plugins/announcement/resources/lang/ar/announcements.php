<?php

return [
    'name' => 'الإعلانات',

    'enums' => [
        'announce_placement' => [
            'top' => 'أعلى',
            'bottom' => 'ثابت في الأسفل',
            'popup' => 'نافذة منبثقة',
            'theme' => 'مدمج في القالب',
        ],

        'text_alignment' => [
            'start' => 'البداية',
            'center' => 'الوسط',
        ],
    ],

    'validation' => [
        'font_size' => 'يجب أن يكون حجم الخط قيمة صالحة لحجم خط CSS.',
        'text_color' => 'يجب أن يكون لون النص قيمة لون سداسية صالحة.',
    ],

    'create' => 'إنشاء إعلان جديد',
    'add_new' => 'إضافة جديد',
    'settings' => [
        'name' => 'الإعلان',
        'description' => 'إدارة إعدادات الإعلانات',
    ],

    'background_color' => 'لون الخلفية',
    'font_size' => 'حجم الخط',
    'font_size_help' => 'اتركه فارغاً لاستخدام الافتراضي. مثال: 1rem، 1em، 12px، ...',
    'text_color' => 'لون النص',
    'start_date' => 'تاريخ البدء',
    'end_date' => 'تاريخ الانتهاء',
    'has_action' => 'يحتوي على إجراء',
    'action_label' => 'تسمية الإجراء',
    'action_url' => 'رابط الإجراء',
    'action_open_new_tab' => 'فتح في علامة تبويب جديدة',
    'dismissible_label' => 'السماح للمستخدم بإغلاق الإعلان',
    'placement' => 'الموضع',
    'text_alignment' => 'محاذاة النص',
    'is_active' => 'مفعل',
    'max_width' => 'العرض الأقصى',
    'max_width_help' => 'اتركه فارغاً لاستخدام القيمة الافتراضية. مثال: 100%، 500px، ...',
    'max_width_unit' => 'وحدة العرض الأقصى',
    'font_size_unit' => 'وحدة حجم الخط',
    'autoplay_label' => 'التشغيل التلقائي',
    'autoplay_delay_label' => 'تأخير التشغيل التلقائي',
    'autoplay_delay_help' => 'التأخير بين كل إعلان بالميلي ثانية. اتركه فارغاً لاستخدام القيمة الافتراضية (5000).',
    'lazy_loading' => 'التحميل الكسول',
    'lazy_loading_description' => 'قم بتمكين هذا الخيار لتحسين سرعة تحميل الصفحة',
    'hide_on_mobile' => 'إخفاء على الهاتف المحمول',
    'dismiss' => 'إغلاق',

    // Placeholders and help texts
    'name_placeholder' => 'أدخل اسم الإعلان',
    'name_help' => 'الاسم للمرجع الداخلي فقط، غير مرئي للمستخدمين',
    'content_placeholder' => 'أدخل رسالة الإعلان هنا...',
    'content_help' => 'الرسالة التي سيتم عرضها للمستخدمين. يدعم تنسيق HTML.',
    'start_date_placeholder' => 'حدد تاريخ ووقت البدء',
    'start_date_help' => 'سيكون الإعلان مرئيًا من هذا التاريخ. اتركه فارغًا للبدء فورًا.',
    'end_date_placeholder' => 'حدد تاريخ ووقت الانتهاء',
    'end_date_help' => 'سيتم إخفاء الإعلان بعد هذا التاريخ. اتركه فارغًا لعدم انتهاء الصلاحية.',
    'has_action_help' => 'إضافة زر دعوة للعمل إلى إعلانك',
    'action_label_placeholder' => 'مثال: اعرف المزيد، تسوق الآن',
    'action_label_help' => 'النص المعروض على زر الإجراء',
    'action_url_placeholder' => 'https://example.com/page (رابط الإجراء)',
    'action_url_help' => 'الرابط الذي سيتم توجيه المستخدمين إليه عند النقر على زر الإجراء',
    'action_open_new_tab_help' => 'فتح رابط الإجراء في علامة تبويب متصفح جديدة',
    'is_active_help' => 'تمكين أو تعطيل هذا الإعلان دون حذفه',
];
