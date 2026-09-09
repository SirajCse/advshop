<?php

return [
    'webhook_secret' => 'Webhook hemmelighet',
    'webhook_secret_helper' => 'Finn dette i Stripe Dashboard under Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Finn dette i Stripe Dashboard under Developers > API keys.',
    'secret_key_helper' => 'Finn dette i Stripe Dashboard under Developers > API keys. Hold denne nøkkelen konfidensiell.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook oppsettsveiledning',
        'description' => 'Følg disse trinnene for å sette opp en Stripe webhook',
        'step_1_label' => 'Logg inn på Stripe-dashbordet',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Velg hendelse og konfigurer endepunkt',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Legg til endepunkt',
        'step_3_description' => 'Klikk på "Legg til endepunkt"-knappen for å lagre webhooken.',
        'step_4_label' => 'Kopier signeringshemmelighet',
        'step_4_description' => 'Kopier verdien "Signing Secret" fra "Webhook Details"-seksjonen og lim den inn i "Stripe Webhook Secret"-feltet i "Stripe"-seksjonen på "Betaling"-fanen på "Innstillinger"-siden.',
    ],
    'no_payment_charge' => 'Ingen betalingsgebyr. Vennligst prøv igjen!',
    'payment_failed' => 'Betaling mislyktes!',
    'payment_type' => 'Betalingstype',
];
