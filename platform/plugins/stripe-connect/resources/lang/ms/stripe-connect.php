<?php

return [
    'stripe_account_id' => 'ID Akaun Stripe',
    'go_to_dashboard' => 'Pergi ke Papan Pemuka Express',
    'connect' => [
        'label' => 'Sambung dengan Stripe',
        'description' => 'Sambungkan akaun Stripe anda untuk mengumpul pembayaran.',
    ],
    'disconnect' => [
        'label' => 'Putuskan Stripe',
        'confirm' => 'Adakah anda pasti mahu memutuskan akaun Stripe anda?',
    ],
    'notifications' => [
        'connected' => 'Akaun Stripe anda telah disambungkan.',
        'disconnected' => 'Akaun Stripe anda telah diputuskan.',
        'now_active' => 'Akaun Stripe anda kini aktif.',
    ],
    'withdrawal' => [
        'payout_info' => 'Pembayaran anda akan dipindahkan secara automatik ke akaun Stripe anda dengan ID: :stripe_account_id.',
    ],
];
