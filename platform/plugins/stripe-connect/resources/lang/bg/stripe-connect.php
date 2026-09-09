<?php

return [
    'stripe_account_id' => 'Stripe акаунт ID',
    'go_to_dashboard' => 'Отидете в експресното табло',
    'connect' => [
        'label' => 'Свържете се със Stripe',
        'description' => 'Свържете вашия Stripe акаунт, за да събирате плащания.',
    ],
    'disconnect' => [
        'label' => 'Прекъснете връзката със Stripe',
        'confirm' => 'Сигурни ли сте, че искате да прекъснете връзката с вашия Stripe акаунт?',
    ],
    'notifications' => [
        'connected' => 'Вашият Stripe акаунт е свързан.',
        'disconnected' => 'Вашият Stripe акаунт е прекъснат.',
        'now_active' => 'Вашият Stripe акаунт вече е активен.',
    ],
    'withdrawal' => [
        'payout_info' => 'Вашето плащане ще бъде автоматично прехвърлено към вашия Stripe акаунт с ID: :stripe_account_id.',
    ],
];
