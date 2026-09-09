<?php

return [
    'webhook_secret' => 'Webhook tajemství',
    'webhook_secret_helper' => 'Najdete to v Stripe Dashboard pod Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Najdete to v Stripe Dashboard pod Developers > API keys.',
    'secret_key_helper' => 'Najdete to v Stripe Dashboard pod Developers > API keys. Tento klíč uchovávejte v tajnosti.',
    'webhook_setup_guide' => [
        'title' => 'Průvodce nastavením Stripe Webhook',
        'description' => 'Postupujte podle těchto kroků a nastavte webhook Stripe',
        'step_1_label' => 'Přihlaste se k ovládacímu panelu Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Vyberte událost a nakonfigurujte koncový bod',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Přidat koncový bod',
        'step_3_description' => 'Kliknutím na tlačítko "Přidat koncový bod" uložte webhook.',
        'step_4_label' => 'Zkopírujte podpisové tajemství',
        'step_4_description' => 'Zkopírujte hodnotu "Signing Secret" z části "Webhook Details" a vložte ji do pole "Stripe Webhook Secret" v části "Stripe" na kartě "Platba" na stránce "Nastavení".',
    ],
    'no_payment_charge' => 'Žádný platební poplatek. Zkuste to prosím znovu!',
    'payment_failed' => 'Platba selhala!',
    'payment_type' => 'Typ platby',
];
