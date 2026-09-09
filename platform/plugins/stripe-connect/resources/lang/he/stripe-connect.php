<?php

return [
    'stripe_account_id' => 'מזהה חשבון Stripe',
    'go_to_dashboard' => 'עבור ללוח המחוונים Express',
    'connect' => [
        'label' => 'התחבר עם Stripe',
        'description' => 'חבר את חשבון Stripe שלך כדי לאסוף תשלומים.',
    ],
    'disconnect' => [
        'label' => 'נתק את Stripe',
        'confirm' => 'האם אתה בטוח שברצונך לנתק את חשבון Stripe שלך?',
    ],
    'notifications' => [
        'connected' => 'חשבון Stripe שלך חובר.',
        'disconnected' => 'חשבון Stripe שלך נותק.',
        'now_active' => 'חשבון Stripe שלך פעיל כעת.',
    ],
    'withdrawal' => [
        'payout_info' => 'התשלום שלך יועבר אוטומטית לחשבון Stripe שלך עם המזהה: :stripe_account_id.',
    ],
];
