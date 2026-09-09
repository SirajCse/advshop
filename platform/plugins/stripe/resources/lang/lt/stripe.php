<?php

return [
    'webhook_secret' => 'Webhook slaptažodis',
    'webhook_secret_helper' => 'Tai rasite savo Stripe Dashboard skiltyje Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Tai rasite savo Stripe Dashboard skiltyje Developers > API keys.',
    'secret_key_helper' => 'Tai rasite savo Stripe Dashboard skiltyje Developers > API keys. Laikykite šį raktą konfidencialų.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook nustatymo vadovas',
        'description' => 'Vykdykite šiuos veiksmus, kad nustatytumėte Stripe webhook',
        'step_1_label' => 'Prisijunkite prie Stripe informacijos skydelio',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Pasirinkite įvykį ir sukonfigūruokite galutinį tašką',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Pridėti galutinį tašką',
        'step_3_description' => 'Spustelėkite mygtuką "Pridėti galutinį tašką", kad išsaugotumėte webhook.',
        'step_4_label' => 'Nukopijuokite pasirašymo slaptažodį',
        'step_4_description' => 'Nukopijuokite "Signing Secret" reikšmę iš "Webhook Details" skyriaus ir įklijuokite ją į "Stripe Webhook Secret" lauką "Stripe" skyriuje "Mokėjimas" skirtuke "Nustatymai" puslapyje.',
    ],
    'no_payment_charge' => 'Nėra mokėjimo mokesčio. Prašome bandyti dar kartą!',
    'payment_failed' => 'Mokėjimas nepavyko!',
    'payment_type' => 'Mokėjimo tipas',
];
