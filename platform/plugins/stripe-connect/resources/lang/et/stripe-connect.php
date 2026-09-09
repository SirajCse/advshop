<?php

return [
    'stripe_account_id' => 'Stripe konto ID',
    'go_to_dashboard' => 'Mine kiire armatuurlauale',
    'connect' => [
        'label' => 'Ühenda Stripe\'iga',
        'description' => 'Ühendage oma Stripe konto maksete kogumiseks.',
    ],
    'disconnect' => [
        'label' => 'Katkesta Stripe ühendus',
        'confirm' => 'Kas olete kindel, et soovite oma Stripe konto ühenduse katkestada?',
    ],
    'notifications' => [
        'connected' => 'Teie Stripe konto on ühendatud.',
        'disconnected' => 'Teie Stripe konto ühendus on katkestatud.',
        'now_active' => 'Teie Stripe konto on nüüd aktiivne.',
    ],
    'withdrawal' => [
        'payout_info' => 'Teie väljamakse kantakse automaatselt üle teie Stripe kontole ID-ga: :stripe_account_id.',
    ],
];
