<?php

return [
    'stripe_account_id' => 'ID konta Stripe',
    'go_to_dashboard' => 'Przejdź do panelu Express',
    'connect' => [
        'label' => 'Połącz ze Stripe',
        'description' => 'Połącz swoje konto Stripe, aby zbierać płatności.',
    ],
    'disconnect' => [
        'label' => 'Odłącz Stripe',
        'confirm' => 'Czy na pewno chcesz odłączyć swoje konto Stripe?',
    ],
    'notifications' => [
        'connected' => 'Twoje konto Stripe zostało połączone.',
        'disconnected' => 'Twoje konto Stripe zostało odłączone.',
        'now_active' => 'Twoje konto Stripe jest teraz aktywne.',
    ],
    'withdrawal' => [
        'payout_info' => 'Twoja wypłata zostanie automatycznie przelana na Twoje konto Stripe o ID: :stripe_account_id.',
    ],
];
