<?php

return [
    'stripe_account_id' => 'Stripe Account ID',
    'go_to_dashboard' => 'Pumunta sa Express Dashboard',
    'connect' => [
        'label' => 'Kumonekta sa Stripe',
        'description' => 'Ikonekta ang iyong Stripe account upang kolektahin ang mga bayad.',
    ],
    'disconnect' => [
        'label' => 'Idiskonekta ang Stripe',
        'confirm' => 'Sigurado ka ba na gusto mong idiskonekta ang iyong Stripe account?',
    ],
    'notifications' => [
        'connected' => 'Ang iyong Stripe account ay naikonekta na.',
        'disconnected' => 'Ang iyong Stripe account ay naidiskonekta na.',
        'now_active' => 'Ang iyong Stripe account ay aktibo na ngayon.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ang iyong payout ay awtomatikong ililipat sa iyong Stripe account na may ID: :stripe_account_id.',
    ],
];
