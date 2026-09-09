<?php

return [
    'stripe_account_id' => 'Stripe konto-ID',
    'go_to_dashboard' => 'Gå til Express-dashbordet',
    'connect' => [
        'label' => 'Koble til Stripe',
        'description' => 'Koble til Stripe-kontoen din for å samle inn betalinger.',
    ],
    'disconnect' => [
        'label' => 'Koble fra Stripe',
        'confirm' => 'Er du sikker på at du vil koble fra Stripe-kontoen din?',
    ],
    'notifications' => [
        'connected' => 'Stripe-kontoen din er koblet til.',
        'disconnected' => 'Stripe-kontoen din er koblet fra.',
        'now_active' => 'Stripe-kontoen din er nå aktiv.',
    ],
    'withdrawal' => [
        'payout_info' => 'Utbetalingen din vil automatisk overføres til Stripe-kontoen din med ID: :stripe_account_id.',
    ],
];
