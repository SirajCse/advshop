<?php

return [
    'stripe_account_id' => 'ID аккаунта Stripe',
    'go_to_dashboard' => 'Перейти к панели Express',
    'connect' => [
        'label' => 'Подключиться к Stripe',
        'description' => 'Подключите свой аккаунт Stripe для сбора платежей.',
    ],
    'disconnect' => [
        'label' => 'Отключить Stripe',
        'confirm' => 'Вы уверены, что хотите отключить свой аккаунт Stripe?',
    ],
    'notifications' => [
        'connected' => 'Ваш аккаунт Stripe подключен.',
        'disconnected' => 'Ваш аккаунт Stripe отключен.',
        'now_active' => 'Ваш аккаунт Stripe теперь активен.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ваша выплата будет автоматически переведена на ваш аккаунт Stripe с ID: :stripe_account_id.',
    ],
];
