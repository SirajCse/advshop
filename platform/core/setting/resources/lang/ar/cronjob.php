<?php

return [
    'name' => 'المهام المجدولة',
    'description' => 'قم بإعداد المهام الخلفية الآلية للحفاظ على تشغيل موقعك بسلاسة.',
    'is_not_ready' => 'لم يتم تكوين المهمة المجدولة بعد',
    'is_not_ready_description' => 'يرجى اتباع التعليمات أدناه لإعداد المهمة المجدولة. هذا مطلوب لميزات مثل تذكيرات سلة التسوق المهجورة وجدولة البريد الإلكتروني والمهام الآلية الأخرى.',
    'is_working' => 'المهمة المجدولة تعمل بشكل صحيح!',
    'is_not_working' => 'توقفت المهمة المجدولة عن العمل',
    'is_not_working_description' => 'لم يتم تشغيل المهمة المجدولة في آخر 10 دقائق. يرجى التحقق من تكوين الخادم الخاص بك أو الاتصال بمزود الاستضافة.',
    'last_checked' => 'آخر نشاط: :time',
    'copy_button' => 'نسخ الأمر',
    'what_is' => [
        'title' => 'ما هي المهمة المجدولة؟',
        'description' => 'المهمة المجدولة هي مهمة آلية تعمل في الخلفية على الخادم الخاص بك. تتيح لموقعك تنفيذ المهام المهمة تلقائياً دون الحاجة إلى القيام بأي شيء يدوياً.',
        'examples' => 'أمثلة',
        'features' => 'إرسال تذكيرات سلة التسوق المهجورة، معالجة رسائل البريد الإلكتروني المجدولة، تنظيف البيانات القديمة، إنشاء التقارير، والمزيد.',
    ],
    'command' => [
        'title' => 'أمر المهمة المجدولة الخاص بك',
        'description' => 'انسخ هذا الأمر وأضفه إلى لوحة تحكم الاستضافة الخاصة بك. يحتاج هذا الأمر إلى التشغيل كل دقيقة للحفاظ على عمل مهامك الآلية.',
    ],
    'setup' => [
        'name' => 'كيفية الإعداد',
        'copied' => 'تم النسخ إلى الحافظة!',
        'choose_hosting' => 'اختر لوحة تحكم الاستضافة الخاصة بك أدناه واتبع التعليمات خطوة بخطوة:',
    ],
    'cpanel' => [
        'step1' => 'قم بتسجيل الدخول إلى حساب <strong>cPanel</strong> الخاص بك',
        'step2' => 'ابحث وانقر على <strong>"Cron Jobs"</strong> في قسم "Advanced"',
        'step3' => 'ضمن "Add New Cron Job"، اختر <strong>"Once Per Minute (* * * * *)"</strong> من القائمة المنسدلة',
        'step4' => '<strong>الصق الأمر</strong> الذي نسخته أعلاه في حقل "Command"',
        'step5' => 'انقر على <strong>"Add New Cron Job"</strong> للحفظ',
    ],
    'plesk' => [
        'step1' => 'قم بتسجيل الدخول إلى لوحة تحكم <strong>Plesk</strong> الخاصة بك',
        'step2' => 'انتقل إلى <strong>"Scheduled Tasks"</strong> (أو "Cron Jobs")',
        'step3' => 'انقر على <strong>"Add Task"</strong> أو <strong>"Schedule a Task"</strong>',
        'step4' => 'اضبط الجدول للتشغيل <strong>كل دقيقة</strong> والصق الأمر',
        'step5' => 'انقر على <strong>"OK"</strong> أو <strong>"Apply"</strong> للحفظ',
    ],
    'directadmin' => [
        'step1' => 'قم بتسجيل الدخول إلى لوحة <strong>DirectAdmin</strong> الخاصة بك',
        'step2' => 'انتقل إلى <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'انقر على <strong>"Add Cron Job"</strong>',
        'step4' => 'اضبط جميع حقول الوقت على <strong>*</strong> (كل دقيقة) والصق الأمر',
        'step5' => 'انقر على <strong>"Add"</strong> لحفظ المهمة المجدولة',
    ],
    'ssh' => [
        'step1' => 'اتصل بالخادم الخاص بك عبر <strong>SSH</strong> باستخدام Terminal أو PuTTY',
        'step2' => 'اكتب <code>crontab -e</code> واضغط Enter لتعديل ملف crontab',
        'step3' => 'أضف سطراً جديداً في الأسفل و<strong>الصق الأمر</strong>',
        'step4' => 'اضغط <strong>Ctrl+X</strong>، ثم <strong>Y</strong>، ثم <strong>Enter</strong> للحفظ (لمحرر nano)',
        'step5' => 'المهمة المجدولة نشطة الآن وستعمل كل دقيقة',
    ],
    'need_help' => 'هل تحتاج مساعدة؟ اتصل بمزود الاستضافة الخاص بك واطلب منهم إعداد مهمة مجدولة تعمل كل دقيقة مع الأمر الموضح أعلاه.',
];
