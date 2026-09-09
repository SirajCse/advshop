<?php

return [
    'stripe_account_id' => 'ID Akun Stripe',
    'go_to_dashboard' => 'Buka Dasbor Express',
    'connect' => [
        'label' => 'Hubungkan dengan Stripe',
        'description' => 'Hubungkan akun Stripe Anda untuk mengumpulkan pembayaran.',
    ],
    'disconnect' => [
        'label' => 'Putuskan Stripe',
        'confirm' => 'Apakah Anda yakin ingin memutuskan akun Stripe Anda?',
    ],
    'notifications' => [
        'connected' => 'Akun Stripe Anda telah terhubung.',
        'disconnected' => 'Akun Stripe Anda telah terputus.',
        'now_active' => 'Akun Stripe Anda sekarang aktif.',
    ],
    'withdrawal' => [
        'payout_info' => 'Pembayaran Anda akan otomatis ditransfer ke akun Stripe Anda dengan ID: :stripe_account_id.',
    ],
];
