<?php

return [
    'stripe_account_id' => 'ID account Stripe',
    'go_to_dashboard' => 'Vai alla Dashboard Express',
    'connect' => [
        'label' => 'Connetti con Stripe',
        'description' => 'Collega il tuo account Stripe per raccogliere i pagamenti.',
    ],
    'disconnect' => [
        'label' => 'Disconnetti Stripe',
        'confirm' => 'Sei sicuro di voler disconnettere il tuo account Stripe?',
    ],
    'notifications' => [
        'connected' => 'Il tuo account Stripe è stato collegato.',
        'disconnected' => 'Il tuo account Stripe è stato disconnesso.',
        'now_active' => 'Il tuo account Stripe è ora attivo.',
    ],
    'withdrawal' => [
        'payout_info' => 'Il tuo pagamento sarà automaticamente trasferito al tuo account Stripe con ID: :stripe_account_id.',
    ],
];
