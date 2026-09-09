<?php

return [
    'stripe_account_id' => 'Stripe 帳戶 ID',
    'go_to_dashboard' => '前往 Express 控制台',
    'connect' => [
        'label' => '連接到 Stripe',
        'description' => '連接您的 Stripe 帳戶以收取付款。',
    ],
    'disconnect' => [
        'label' => '中斷 Stripe',
        'confirm' => '您確定要中斷您的 Stripe 帳戶嗎？',
    ],
    'notifications' => [
        'connected' => '您的 Stripe 帳戶已連接。',
        'disconnected' => '您的 Stripe 帳戶已中斷。',
        'now_active' => '您的 Stripe 帳戶現已啟用。',
    ],
    'withdrawal' => [
        'payout_info' => '您的付款將自動轉入您的 Stripe 帳戶，帳戶 ID 為：:stripe_account_id。',
    ],
];
