<?php

return [
    'webhook_secret' => 'Webhook geheim',
    'webhook_secret_helper' => 'Vind dit in uw Stripe Dashboard onder Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Vind dit in uw Stripe Dashboard onder Developers > API keys.',
    'secret_key_helper' => 'Vind dit in uw Stripe Dashboard onder Developers > API keys. Bewaar deze sleutel vertrouwelijk.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook installatiehandleiding',
        'description' => 'Volg deze stappen om een Stripe webhook in te stellen',
        'step_1_label' => 'Log in op het Stripe-dashboard',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Selecteer gebeurtenis en configureer eindpunt',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Eindpunt toevoegen',
        'step_3_description' => 'Klik op de knop "Eindpunt toevoegen" om de webhook op te slaan.',
        'step_4_label' => 'Kopieer ondertekeningsgeheim',
        'step_4_description' => 'Kopieer de waarde "Signing Secret" uit het gedeelte "Webhook Details" en plak deze in het veld "Stripe Webhook Secret" in het gedeelte "Stripe" van het tabblad "Betaling" op de pagina "Instellingen".',
    ],
    'no_payment_charge' => 'Geen betalingskosten. Probeer het opnieuw!',
    'payment_failed' => 'Betaling mislukt!',
    'payment_type' => 'Betalingstype',
];
