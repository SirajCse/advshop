<?php

return [
    'stripe_account_id' => 'ID cont Stripe',
    'go_to_dashboard' => 'Mergi la tabloul de bord Express',
    'connect' => [
        'label' => 'Conectați-vă cu Stripe',
        'description' => 'Conectați contul Stripe pentru a colecta plăți.',
    ],
    'disconnect' => [
        'label' => 'Deconectați Stripe',
        'confirm' => 'Sigur doriți să deconectați contul Stripe?',
    ],
    'notifications' => [
        'connected' => 'Contul Stripe a fost conectat.',
        'disconnected' => 'Contul Stripe a fost deconectat.',
        'now_active' => 'Contul Stripe este acum activ.',
    ],
    'withdrawal' => [
        'payout_info' => 'Plata dvs. va fi transferată automat în contul Stripe cu ID: :stripe_account_id.',
    ],
];
