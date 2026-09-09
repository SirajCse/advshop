<?php

return [
    'webhook_secret' => 'Webhook titok',
    'webhook_secret_helper' => 'Ezt a Stripe Dashboard-on találja a Developers > Webhooks > Signing secret alatt.',
    'public_key_helper' => 'Ezt a Stripe Dashboard-on találja a Developers > API keys alatt.',
    'secret_key_helper' => 'Ezt a Stripe Dashboard-on találja a Developers > API keys alatt. Tartsa bizalmasan ezt a kulcsot.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook telepítési útmutató',
        'description' => 'Kövesse ezeket a lépéseket a Stripe webhook beállításához',
        'step_1_label' => 'Jelentkezzen be a Stripe irányítópultjába',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Válassza ki az eseményt és konfigurálja a végpontot',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Végpont hozzáadása',
        'step_3_description' => 'Kattintson a "Végpont hozzáadása" gombra a webhook mentéséhez.',
        'step_4_label' => 'Aláírási titok másolása',
        'step_4_description' => 'Másolja ki a "Signing Secret" értéket a "Webhook Details" részből, és illessze be a "Stripe Webhook Secret" mezőbe a "Stripe" részben a "Beállítások" oldal "Fizetés" fülén.',
    ],
    'no_payment_charge' => 'Nincs fizetési díj. Kérjük, próbálja újra!',
    'payment_failed' => 'A fizetés sikertelen!',
    'payment_type' => 'Fizetési típus',
];
