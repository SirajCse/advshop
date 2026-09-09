<?php

return [
    'stripe_account_id' => 'Stripe ID налога',
    'go_to_dashboard' => 'Идите на Express контролну таблу',
    'connect' => [
        'label' => 'Повежите се са Stripe',
        'description' => 'Повежите свој Stripe налог да бисте прикупљали плаћања.',
    ],
    'disconnect' => [
        'label' => 'Прекините везу са Stripe',
        'confirm' => 'Да ли сте сигурни да желите да прекинете везу са вашим Stripe налогом?',
    ],
    'notifications' => [
        'connected' => 'Ваш Stripe налог је повезан.',
        'disconnected' => 'Ваш Stripe налог је прекинут.',
        'now_active' => 'Ваш Stripe налог је сада активан.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ваша исплата ће бити аутоматски пребачена на ваш Stripe налог са ID: :stripe_account_id.',
    ],
];
