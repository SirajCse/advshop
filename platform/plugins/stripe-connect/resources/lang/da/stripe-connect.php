<?php

return [
    'stripe_account_id' => 'Stripe konto-ID',
    'go_to_dashboard' => 'Gå til Express Dashboard',
    'connect' => [
        'label' => 'Forbind med Stripe',
        'description' => 'Forbind din Stripe-konto for at indsamle betalinger.',
    ],
    'disconnect' => [
        'label' => 'Afbryd Stripe',
        'confirm' => 'Er du sikker på, at du vil afbryde din Stripe-konto?',
    ],
    'notifications' => [
        'connected' => 'Din Stripe-konto er blevet forbundet.',
        'disconnected' => 'Din Stripe-konto er blevet afbrudt.',
        'now_active' => 'Din Stripe-konto er nu aktiv.',
    ],
    'withdrawal' => [
        'payout_info' => 'Din udbetaling vil automatisk blive overført til din Stripe-konto med ID: :stripe_account_id.',
    ],
];
