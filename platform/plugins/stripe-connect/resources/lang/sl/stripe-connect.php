<?php

return [
    'stripe_account_id' => 'ID računa Stripe',
    'go_to_dashboard' => 'Pojdi na nadzorno ploščo Express',
    'connect' => [
        'label' => 'Poveži se s Stripe',
        'description' => 'Povežite svoj račun Stripe za zbiranje plačil.',
    ],
    'disconnect' => [
        'label' => 'Prekini povezavo s Stripe',
        'confirm' => 'Ali ste prepričani, da želite prekiniti povezavo z vašim računom Stripe?',
    ],
    'notifications' => [
        'connected' => 'Vaš račun Stripe je bil povezan.',
        'disconnected' => 'Vaš račun Stripe je bil odklopljen.',
        'now_active' => 'Vaš račun Stripe je zdaj aktiven.',
    ],
    'withdrawal' => [
        'payout_info' => 'Vaše izplačilo bo samodejno preneseno na vaš račun Stripe z ID: :stripe_account_id.',
    ],
];
