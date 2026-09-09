<?php

return [
    'stripe_account_id' => 'Stripe-tilin tunnus',
    'go_to_dashboard' => 'Siirry Express-hallintapaneeliin',
    'connect' => [
        'label' => 'Yhdistä Stripeen',
        'description' => 'Yhdistä Stripe-tilisi maksujen keräämiseksi.',
    ],
    'disconnect' => [
        'label' => 'Katkaise Stripe-yhteys',
        'confirm' => 'Haluatko varmasti katkaista yhteyden Stripe-tiliisi?',
    ],
    'notifications' => [
        'connected' => 'Stripe-tilisi on yhdistetty.',
        'disconnected' => 'Stripe-tilisi yhteys on katkaistu.',
        'now_active' => 'Stripe-tilisi on nyt aktiivinen.',
    ],
    'withdrawal' => [
        'payout_info' => 'Maksusi siirretään automaattisesti Stripe-tilillesi, jonka tunnus on: :stripe_account_id.',
    ],
];
