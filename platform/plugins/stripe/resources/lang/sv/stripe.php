<?php

return [
    'webhook_secret' => 'Webhook hemlighet',
    'webhook_secret_helper' => 'Hitta detta i din Stripe Dashboard under Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Hitta detta i din Stripe Dashboard under Developers > API keys.',
    'secret_key_helper' => 'Hitta detta i din Stripe Dashboard under Developers > API keys. Håll denna nyckel konfidentiell.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook installationsguide',
        'description' => 'Följ dessa steg för att konfigurera en Stripe webhook',
        'step_1_label' => 'Logga in på Stripe-instrumentpanelen',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Välj händelse och konfigurera slutpunkt',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Lägg till slutpunkt',
        'step_3_description' => 'Klicka på knappen "Lägg till slutpunkt" för att spara webhooken.',
        'step_4_label' => 'Kopiera signeringshemlighet',
        'step_4_description' => 'Kopiera värdet "Signing Secret" från avsnittet "Webhook Details" och klistra in det i fältet "Stripe Webhook Secret" i avsnittet "Stripe" på fliken "Betalning" på sidan "Inställningar".',
    ],
    'no_payment_charge' => 'Ingen betalningsavgift. Vänligen försök igen!',
    'payment_failed' => 'Betalningen misslyckades!',
    'payment_type' => 'Betalningstyp',
];
