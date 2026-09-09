<?php

return [
    'stripe_account_id' => 'รหัสบัญชี Stripe',
    'go_to_dashboard' => 'ไปที่แดชบอร์ด Express',
    'connect' => [
        'label' => 'เชื่อมต่อกับ Stripe',
        'description' => 'เชื่อมต่อบัญชี Stripe ของคุณเพื่อรับชำระเงิน',
    ],
    'disconnect' => [
        'label' => 'ตัดการเชื่อมต่อ Stripe',
        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการตัดการเชื่อมต่อบัญชี Stripe ของคุณ?',
    ],
    'notifications' => [
        'connected' => 'บัญชี Stripe ของคุณได้เชื่อมต่อแล้ว',
        'disconnected' => 'บัญชี Stripe ของคุณได้ถูกตัดการเชื่อมต่อแล้ว',
        'now_active' => 'บัญชี Stripe ของคุณใช้งานได้แล้ว',
    ],
    'withdrawal' => [
        'payout_info' => 'การจ่ายเงินของคุณจะถูกโอนไปยังบัญชี Stripe ของคุณโดยอัตโนมัติด้วยรหัส: :stripe_account_id',
    ],
];
