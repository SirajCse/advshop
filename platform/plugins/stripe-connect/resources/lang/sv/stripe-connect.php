<?php

return [
    'stripe_account_id' => 'Stripe konto-ID',
    'go_to_dashboard' => 'Gå till Express Dashboard',
    'connect' => [
        'label' => 'Anslut med Stripe',
        'description' => 'Anslut ditt Stripe-konto för att samla in betalningar.',
    ],
    'disconnect' => [
        'label' => 'Koppla från Stripe',
        'confirm' => 'Är du säker på att du vill koppla från ditt Stripe-konto?',
    ],
    'notifications' => [
        'connected' => 'Ditt Stripe-konto har anslutits.',
        'disconnected' => 'Ditt Stripe-konto har kopplats från.',
        'now_active' => 'Ditt Stripe-konto är nu aktivt.',
    ],
    'withdrawal' => [
        'payout_info' => 'Din utbetalning kommer automatiskt att överföras till ditt Stripe-konto med ID: :stripe_account_id.',
    ],
];
