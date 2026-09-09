<?php

return [
    'stripe_account_id' => 'ID облікового запису Stripe',
    'go_to_dashboard' => 'Перейти до панелі Express',
    'connect' => [
        'label' => 'Підключитися до Stripe',
        'description' => 'Підключіть свій обліковий запис Stripe для збору платежів.',
    ],
    'disconnect' => [
        'label' => 'Від\'єднати Stripe',
        'confirm' => 'Ви впевнені, що хочете від\'єднати свій обліковий запис Stripe?',
    ],
    'notifications' => [
        'connected' => 'Ваш обліковий запис Stripe підключено.',
        'disconnected' => 'Ваш обліковий запис Stripe від\'єднано.',
        'now_active' => 'Ваш обліковий запис Stripe тепер активний.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ваша виплата буде автоматично переведена на ваш обліковий запис Stripe з ID: :stripe_account_id.',
    ],
];
