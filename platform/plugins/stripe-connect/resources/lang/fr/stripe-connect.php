<?php

return [
    'stripe_account_id' => 'ID du compte Stripe',
    'go_to_dashboard' => 'Aller au tableau de bord Express',
    'connect' => [
        'label' => 'Se connecter avec Stripe',
        'description' => 'Connectez votre compte Stripe pour collecter les paiements.',
    ],
    'disconnect' => [
        'label' => 'Déconnecter Stripe',
        'confirm' => 'Êtes-vous sûr de vouloir déconnecter votre compte Stripe ?',
    ],
    'notifications' => [
        'connected' => 'Votre compte Stripe a été connecté.',
        'disconnected' => 'Votre compte Stripe a été déconnecté.',
        'now_active' => 'Votre compte Stripe est maintenant actif.',
    ],
    'withdrawal' => [
        'payout_info' => 'Votre paiement sera automatiquement transféré sur votre compte Stripe avec l\'ID : :stripe_account_id.',
    ],
];
