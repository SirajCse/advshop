<?php

return [
    'webhook_secret' => 'Webhook Secret',
    'webhook_secret_helper' => 'Hanapin ito sa iyong Stripe Dashboard sa ilalim ng Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Hanapin ito sa iyong Stripe Dashboard sa ilalim ng Developers > API keys.',
    'secret_key_helper' => 'Hanapin ito sa iyong Stripe Dashboard sa ilalim ng Developers > API keys. Panatilihing kumpidensyal ang key na ito.',
    'webhook_setup_guide' => [
        'title' => 'Gabay sa Pag-setup ng Stripe Webhook',
        'description' => 'Sundin ang mga hakbang na ito upang mag-set up ng Stripe webhook',
        'step_1_label' => 'Mag-login sa Stripe Dashboard',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Pumili ng event at i-configure ang endpoint',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Magdagdag ng endpoint',
        'step_3_description' => 'I-click ang button na "Add Endpoint" upang i-save ang webhook.',
        'step_4_label' => 'Kopyahin ang signing secret',
        'step_4_description' => 'Kopyahin ang "Signing Secret" na halaga mula sa seksyon ng "Webhook Details" at i-paste ito sa field na "Stripe Webhook Secret" sa seksyon ng "Stripe" ng "Payment" tab sa page ng "Settings".',
    ],
    'no_payment_charge' => 'Walang singil sa pagbabayad. Pakisubukan ulit!',
    'payment_failed' => 'Nabigo ang pagbabayad!',
    'payment_type' => 'Uri ng Pagbabayad',
];
