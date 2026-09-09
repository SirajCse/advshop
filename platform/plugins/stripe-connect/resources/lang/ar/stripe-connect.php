<?php

return [
    'stripe_account_id' => 'معرف حساب Stripe',
    'go_to_dashboard' => 'الانتقال إلى لوحة التحكم السريعة',
    'connect' => [
        'label' => 'الاتصال بـ Stripe',
        'description' => 'اربط حساب Stripe الخاص بك لتحصيل المدفوعات.',
    ],
    'disconnect' => [
        'label' => 'قطع الاتصال بـ Stripe',
        'confirm' => 'هل أنت متأكد من أنك تريد قطع الاتصال بحساب Stripe الخاص بك؟',
    ],
    'notifications' => [
        'connected' => 'تم ربط حساب Stripe الخاص بك.',
        'disconnected' => 'تم قطع الاتصال بحساب Stripe الخاص بك.',
        'now_active' => 'حساب Stripe الخاص بك نشط الآن.',
    ],
    'withdrawal' => [
        'payout_info' => 'سيتم تحويل دفعتك تلقائيًا إلى حساب Stripe الخاص بك بمعرف: :stripe_account_id.',
    ],
];
