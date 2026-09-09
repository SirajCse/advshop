<?php

return [
    'payment_description' => 'يمكن للعميل شراء المنتج والدفع مباشرة باستخدام Visa أو بطاقة الائتمان عبر :name',
    'api_key' => 'مفتاح API',
    'api_key_helper' => 'احصل على مفتاح API الخاص بك من لوحة تحكم Mollie',
    'webhook_secret' => 'سر Webhook (اختياري)',
    'webhook_secret_helper' => 'اختياري: أضف سر webhook لتعزيز الأمان. قم بتكوين هذا في لوحة تحكم Mollie ضمن المطورين > Webhooks',
    'register_account' => 'سجل حساباً على :name',
    'after_registration' => 'بعد التسجيل في :name، سيكون لديك مفتاح API',
    'enter_api_key' => 'أدخل مفتاح API في المربع على اليمين',
    'webhook_configuration' => 'تكوين Webhook:',
    'webhook_url_instruction' => 'في لوحة تحكم Mollie، قم بتكوين عنوان URL الخاص بـ webhook كـ:',
    'webhook_note' => 'ملاحظة: استبدل {token} بالرمز الفعلي للدفع. سيتم استدعاء webhook تلقائياً بواسطة Mollie لتحديث حالة الدفع.',
    'security_optional' => 'الأمان (اختياري):',
    'security_instruction' => 'لتعزيز الأمان، يمكنك تكوين سر webhook في لوحة تحكم Mollie ضمن المطورين > Webhooks، ثم أدخله في حقل سر Webhook.',
];
