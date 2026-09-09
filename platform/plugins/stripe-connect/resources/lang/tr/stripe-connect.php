<?php

return [
    'stripe_account_id' => 'Stripe Hesap Kimliği',
    'go_to_dashboard' => 'Express Paneline Git',
    'connect' => [
        'label' => 'Stripe ile Bağlan',
        'description' => 'Ödemeleri toplamak için Stripe hesabınızı bağlayın.',
    ],
    'disconnect' => [
        'label' => 'Stripe Bağlantısını Kes',
        'confirm' => 'Stripe hesabınızın bağlantısını kesmek istediğinizden emin misiniz?',
    ],
    'notifications' => [
        'connected' => 'Stripe hesabınız bağlandı.',
        'disconnected' => 'Stripe hesabınızın bağlantısı kesildi.',
        'now_active' => 'Stripe hesabınız artık aktif.',
    ],
    'withdrawal' => [
        'payout_info' => 'Ödemeniz otomatik olarak :stripe_account_id kimliğine sahip Stripe hesabınıza aktarılacaktır.',
    ],
];
