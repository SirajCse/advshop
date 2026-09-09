<?php

return [
    'stripe_account_id' => 'ID účtu Stripe',
    'go_to_dashboard' => 'Prejsť na Express Dashboard',
    'connect' => [
        'label' => 'Pripojiť sa so Stripe',
        'description' => 'Pripojte svoj účet Stripe na zber platieb.',
    ],
    'disconnect' => [
        'label' => 'Odpojiť Stripe',
        'confirm' => 'Naozaj chcete odpojiť svoj účet Stripe?',
    ],
    'notifications' => [
        'connected' => 'Váš účet Stripe bol pripojený.',
        'disconnected' => 'Váš účet Stripe bol odpojený.',
        'now_active' => 'Váš účet Stripe je teraz aktívny.',
    ],
    'withdrawal' => [
        'payout_info' => 'Vaša výplata bude automaticky prevedená na váš účet Stripe s ID: :stripe_account_id.',
    ],
];
