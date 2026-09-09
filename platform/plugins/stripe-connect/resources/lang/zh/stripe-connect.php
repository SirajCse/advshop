<?php

return [
    'stripe_account_id' => 'Stripe 账户 ID',
    'go_to_dashboard' => '前往 Express 仪表板',
    'connect' => [
        'label' => '连接到 Stripe',
        'description' => '连接您的 Stripe 账户以收取付款。',
    ],
    'disconnect' => [
        'label' => '断开 Stripe',
        'confirm' => '您确定要断开您的 Stripe 账户吗？',
    ],
    'notifications' => [
        'connected' => '您的 Stripe 账户已连接。',
        'disconnected' => '您的 Stripe 账户已断开。',
        'now_active' => '您的 Stripe 账户现已激活。',
    ],
    'withdrawal' => [
        'payout_info' => '您的付款将自动转入您的 Stripe 账户，账户 ID 为：:stripe_account_id。',
    ],
];
