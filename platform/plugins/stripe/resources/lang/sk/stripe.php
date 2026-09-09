<?php

return [
    'webhook_secret' => 'Webhook tajomstvo',
    'webhook_secret_helper' => 'Nájdete to v Stripe Dashboard v časti Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Nájdete to v Stripe Dashboard v časti Developers > API keys.',
    'secret_key_helper' => 'Nájdete to v Stripe Dashboard v časti Developers > API keys. Uchovávajte tento kľúč v tajnosti.',
    'webhook_setup_guide' => [
        'title' => 'Príručka nastavenia Stripe Webhook',
        'description' => 'Postupujte podľa týchto krokov na nastavenie webhooku Stripe',
        'step_1_label' => 'Prihláste sa do ovládacieho panela Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Vyberte udalosť a nakonfigurujte koncový bod',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Pridať koncový bod',
        'step_3_description' => 'Kliknutím na tlačidlo "Pridať koncový bod" uložte webhook.',
        'step_4_label' => 'Skopírujte podpisové tajomstvo',
        'step_4_description' => 'Skopírujte hodnotu "Signing Secret" z časti "Webhook Details" a vložte ju do poľa "Stripe Webhook Secret" v časti "Stripe" na karte "Platba" na stránke "Nastavenia".',
    ],
    'no_payment_charge' => 'Žiadny platobný poplatok. Skúste to prosím znova!',
    'payment_failed' => 'Platba zlyhala!',
    'payment_type' => 'Typ platby',
];
