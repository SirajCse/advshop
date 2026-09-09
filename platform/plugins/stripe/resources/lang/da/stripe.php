<?php

return [
    'webhook_secret' => 'Webhook hemmelighed',
    'webhook_secret_helper' => 'Find dette i dit Stripe Dashboard under Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Find dette i dit Stripe Dashboard under Developers > API keys.',
    'secret_key_helper' => 'Find dette i dit Stripe Dashboard under Developers > API keys. Hold denne nøgle fortrolig.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook opsætningsvejledning',
        'description' => 'Følg disse trin for at konfigurere en Stripe webhook',
        'step_1_label' => 'Log ind på Stripe-dashboardet',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Vælg begivenhed og konfigurer slutpunkt',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Tilføj slutpunkt',
        'step_3_description' => 'Klik på knappen "Tilføj slutpunkt" for at gemme webhooken.',
        'step_4_label' => 'Kopier signeringshemmelighed',
        'step_4_description' => 'Kopier værdien "Signing Secret" fra sektionen "Webhook Details", og indsæt den i feltet "Stripe Webhook Secret" i sektionen "Stripe" på fanen "Betaling" på siden "Indstillinger".',
    ],
    'no_payment_charge' => 'Ingen betalingsgebyr. Prøv venligst igen!',
    'payment_failed' => 'Betaling mislykkedes!',
    'payment_type' => 'Betalingstype',
];
