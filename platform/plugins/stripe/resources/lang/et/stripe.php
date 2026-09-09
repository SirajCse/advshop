<?php

return [
    'webhook_secret' => 'Webhook saladus',
    'webhook_secret_helper' => 'Leiate selle oma Stripe Dashboard\'is Developers > Webhooks > Signing secret alt.',
    'public_key_helper' => 'Leiate selle oma Stripe Dashboard\'is Developers > API keys alt.',
    'secret_key_helper' => 'Leiate selle oma Stripe Dashboard\'is Developers > API keys alt. Hoidke see võti konfidentsiaalsena.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook seadistamise juhend',
        'description' => 'Järgige neid samme Stripe webhook\'i seadistamiseks',
        'step_1_label' => 'Logige sisse Stripe\'i armatuurlauale',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Valige sündmus ja konfigureerige lõpp-punkt',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Lisa lõpp-punkt',
        'step_3_description' => 'Klõpsake webhook\'i salvestamiseks nuppu "Lisa lõpp-punkt".',
        'step_4_label' => 'Kopeeri allkirjastamise saladus',
        'step_4_description' => 'Kopeerige väärtus "Signing Secret" jaotisest "Webhook Details" ja kleepige see väljale "Stripe Webhook Secret" jaotises "Stripe" vahekaardil "Makse" lehel "Seaded".',
    ],
    'no_payment_charge' => 'Maksetasu puudub. Palun proovige uuesti!',
    'payment_failed' => 'Makse ebaõnnestus!',
    'payment_type' => 'Makse tüüp',
];
