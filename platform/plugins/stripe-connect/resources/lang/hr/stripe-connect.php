<?php

return [
    'stripe_account_id' => 'Stripe račun ID',
    'go_to_dashboard' => 'Idi na Express nadzornu ploču',
    'connect' => [
        'label' => 'Poveži se sa Stripe',
        'description' => 'Povežite svoj Stripe račun za prikupljanje plaćanja.',
    ],
    'disconnect' => [
        'label' => 'Prekini vezu sa Stripe',
        'confirm' => 'Jeste li sigurni da želite prekinuti vezu sa svojim Stripe računom?',
    ],
    'notifications' => [
        'connected' => 'Vaš Stripe račun je povezan.',
        'disconnected' => 'Veza s vašim Stripe računom je prekinuta.',
        'now_active' => 'Vaš Stripe račun je sada aktivan.',
    ],
    'withdrawal' => [
        'payout_info' => 'Vaša isplata bit će automatski prebačena na vaš Stripe račun s ID-om: :stripe_account_id.',
    ],
];
