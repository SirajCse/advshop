<?php

return [
    'webhook_secret' => 'Webhook tajna',
    'webhook_secret_helper' => 'Pronađite ovo u svom Stripe Dashboard-u pod Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Pronađite ovo u svom Stripe Dashboard-u pod Developers > API keys.',
    'secret_key_helper' => 'Pronađite ovo u svom Stripe Dashboard-u pod Developers > API keys. Čuvajte ovaj ključ povjerljivim.',
    'webhook_setup_guide' => [
        'title' => 'Vodič za postavljanje Stripe Webhook-a',
        'description' => 'Slijedite ove korake za postavljanje Stripe webhook-a',
        'step_1_label' => 'Prijavite se na Stripe nadzornu ploču',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Odaberite događaj i konfigurirajte krajnju točku',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Dodaj krajnju točku',
        'step_3_description' => 'Kliknite na gumb "Dodaj krajnju točku" za spremanje webhook-a.',
        'step_4_label' => 'Kopiraj tajnu potpisa',
        'step_4_description' => 'Kopirajte vrijednost "Signing Secret" iz odjeljka "Webhook Details" i zalijepite je u polje "Stripe Webhook Secret" u odjeljku "Stripe" na kartici "Plaćanje" na stranici "Postavke".',
    ],
    'no_payment_charge' => 'Nema naknade za plaćanje. Molimo pokušajte ponovno!',
    'payment_failed' => 'Plaćanje nije uspjelo!',
    'payment_type' => 'Tip plaćanja',
];
