<?php

return [
    'stripe_account_id' => 'Stripe account-ID',
    'go_to_dashboard' => 'Ga naar Express-dashboard',
    'connect' => [
        'label' => 'Verbinden met Stripe',
        'description' => 'Verbind uw Stripe-account om betalingen te verzamelen.',
    ],
    'disconnect' => [
        'label' => 'Stripe ontkoppelen',
        'confirm' => 'Weet u zeker dat u uw Stripe-account wilt ontkoppelen?',
    ],
    'notifications' => [
        'connected' => 'Uw Stripe-account is verbonden.',
        'disconnected' => 'Uw Stripe-account is ontkoppeld.',
        'now_active' => 'Uw Stripe-account is nu actief.',
    ],
    'withdrawal' => [
        'payout_info' => 'Uw uitbetaling wordt automatisch overgemaakt naar uw Stripe-account met ID: :stripe_account_id.',
    ],
];
