<?php

return [
    'stripe_account_id' => 'Stripe paskyros ID',
    'go_to_dashboard' => 'Eiti į Express valdymo skydą',
    'connect' => [
        'label' => 'Prisijungti su Stripe',
        'description' => 'Prijunkite savo Stripe paskyrą mokėjimų rinkimui.',
    ],
    'disconnect' => [
        'label' => 'Atjungti Stripe',
        'confirm' => 'Ar tikrai norite atjungti savo Stripe paskyrą?',
    ],
    'notifications' => [
        'connected' => 'Jūsų Stripe paskyra prijungta.',
        'disconnected' => 'Jūsų Stripe paskyra atjungta.',
        'now_active' => 'Jūsų Stripe paskyra dabar aktyvi.',
    ],
    'withdrawal' => [
        'payout_info' => 'Jūsų išmoka bus automatiškai pervesta į jūsų Stripe paskyrą su ID: :stripe_account_id.',
    ],
];
