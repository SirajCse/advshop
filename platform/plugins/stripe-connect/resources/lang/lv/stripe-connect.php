<?php

return [
    'stripe_account_id' => 'Stripe konta ID',
    'go_to_dashboard' => 'Doties uz Express informācijas paneli',
    'connect' => [
        'label' => 'Savienot ar Stripe',
        'description' => 'Savienojiet savu Stripe kontu, lai iekasētu maksājumus.',
    ],
    'disconnect' => [
        'label' => 'Atvienot Stripe',
        'confirm' => 'Vai tiešām vēlaties atvienot savu Stripe kontu?',
    ],
    'notifications' => [
        'connected' => 'Jūsu Stripe konts ir savienots.',
        'disconnected' => 'Jūsu Stripe konts ir atvienots.',
        'now_active' => 'Jūsu Stripe konts tagad ir aktīvs.',
    ],
    'withdrawal' => [
        'payout_info' => 'Jūsu izmaksa tiks automātiski pārskaitīta uz jūsu Stripe kontu ar ID: :stripe_account_id.',
    ],
];
