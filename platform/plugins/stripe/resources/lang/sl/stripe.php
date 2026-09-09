<?php

return [
    'webhook_secret' => 'Webhook skrivnost',
    'webhook_secret_helper' => 'Najdete to v Stripe Dashboard pod Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Najdete to v Stripe Dashboard pod Developers > API keys.',
    'secret_key_helper' => 'Najdete to v Stripe Dashboard pod Developers > API keys. Ohranite ta ključ zaupnega.',
    'webhook_setup_guide' => [
        'title' => 'Navodila za nastavitev Stripe Webhook',
        'description' => 'Sledite tem korakom za nastavitev Stripe webhook',
        'step_1_label' => 'Prijavite se v nadzorno ploščo Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Izberite dogodek in konfigurirajte končno točko',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Dodaj končno točko',
        'step_3_description' => 'Kliknite gumb "Dodaj končno točko", da shranite webhook.',
        'step_4_label' => 'Kopirajte skrivnost podpisa',
        'step_4_description' => 'Kopirajte vrednost "Signing Secret" iz razdelka "Webhook Details" in jo prilepite v polje "Stripe Webhook Secret" v razdelku "Stripe" na zavihku "Plačilo" na strani "Nastavitve".',
    ],
    'no_payment_charge' => 'Brez plačilne provizije. Prosimo, poskusite znova!',
    'payment_failed' => 'Plačilo ni uspelo!',
    'payment_type' => 'Vrsta plačila',
];
