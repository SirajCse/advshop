<?php

return [
    'stripe_account_id' => 'Stripe-Konto-ID',
    'go_to_dashboard' => 'Zum Express-Dashboard',
    'connect' => [
        'label' => 'Mit Stripe verbinden',
        'description' => 'Verbinden Sie Ihr Stripe-Konto, um Zahlungen einzuziehen.',
    ],
    'disconnect' => [
        'label' => 'Stripe trennen',
        'confirm' => 'Sind Sie sicher, dass Sie Ihr Stripe-Konto trennen möchten?',
    ],
    'notifications' => [
        'connected' => 'Ihr Stripe-Konto wurde verbunden.',
        'disconnected' => 'Ihr Stripe-Konto wurde getrennt.',
        'now_active' => 'Ihr Stripe-Konto ist jetzt aktiv.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ihre Auszahlung wird automatisch auf Ihr Stripe-Konto mit der ID :stripe_account_id übertragen.',
    ],
];
