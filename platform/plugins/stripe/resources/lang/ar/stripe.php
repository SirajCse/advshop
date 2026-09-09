<?php

return [
    'webhook_secret' => 'سر الويب هوك',
    'webhook_secret_helper' => 'يمكنك العثور على هذا في لوحة تحكم Stripe ضمن Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'يمكنك العثور على هذا في لوحة تحكم Stripe ضمن Developers > API keys.',
    'secret_key_helper' => 'يمكنك العثور على هذا في لوحة تحكم Stripe ضمن Developers > API keys. احتفظ بهذا المفتاح سرياً.',
    'webhook_setup_guide' => [
        'title' => 'دليل إعداد خطاف Webhook الشريطي',
        'description' => 'اتبع هذه الخطوات لإعداد خطاف ويب Stripe',
        'step_1_label' => 'قم بتسجيل الدخول إلى لوحة تحكم Stripe',
        'step_1_description' => 'قم بالوصول إلى :link وانقر على زر "Add Endpoint" في قسم "Webhooks" من علامة التبويب "Developers".',
        'step_2_label' => 'حدد الحدث وقم بتكوين نقطة النهاية',
        'step_2_description' => 'حدد حدث "payment_intent.succeeded" وأدخل عنوان URL التالي في حقل "Endpoint URL": :url',
        'step_3_label' => 'أضف نقطة النهاية',
        'step_3_description' => 'انقر فوق الزر "إضافة نقطة نهاية" لحفظ خطاف الويب.',
        'step_4_label' => 'نسخ سر التوقيع',
        'step_4_description' => 'انسخ قيمة "سر التوقيع" من قسم "تفاصيل Webhook" وألصقها في الحقل "Stripe Webhook Secret" في قسم "Stripe" ضمن علامة التبويب "الدفع" في صفحة "الإعدادات".',
    ],
    'no_payment_charge' => 'لا توجد رسوم دفع. يرجى المحاولة مرة أخرى!',
    'payment_failed' => 'فشل الدفع!',
    'payment_type' => 'نوع الدفع',
];
