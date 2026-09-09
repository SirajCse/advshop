<?php

return [
    'stripe_account_id' => 'Stripe 계정 ID',
    'go_to_dashboard' => 'Express 대시보드로 이동',
    'connect' => [
        'label' => 'Stripe와 연결',
        'description' => 'Stripe 계정을 연결하여 결제를 수집하세요.',
    ],
    'disconnect' => [
        'label' => 'Stripe 연결 해제',
        'confirm' => 'Stripe 계정 연결을 해제하시겠습니까?',
    ],
    'notifications' => [
        'connected' => 'Stripe 계정이 연결되었습니다.',
        'disconnected' => 'Stripe 계정 연결이 해제되었습니다.',
        'now_active' => 'Stripe 계정이 활성화되었습니다.',
    ],
    'withdrawal' => [
        'payout_info' => '지급금은 자동으로 Stripe 계정 ID :stripe_account_id로 이체됩니다.',
    ],
];
