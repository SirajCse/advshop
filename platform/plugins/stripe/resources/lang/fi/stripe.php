<?php

return [
    'webhook_secret' => 'Webhook salaisuus',
    'webhook_secret_helper' => 'Löydät tämän Stripe Dashboard -palvelustasi kohdasta Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Löydät tämän Stripe Dashboard -palvelustasi kohdasta Developers > API keys.',
    'secret_key_helper' => 'Löydät tämän Stripe Dashboard -palvelustasi kohdasta Developers > API keys. Pidä tämä avain luottamuksellisena.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook -asennusopas',
        'description' => 'Noudata näitä vaiheita Stripe webhookin määrittämiseksi',
        'step_1_label' => 'Kirjaudu Stripe-hallintapaneeliin',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Valitse tapahtuma ja määritä päätepiste',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Lisää päätepiste',
        'step_3_description' => 'Tallenna webhook napsauttamalla "Lisää päätepiste" -painiketta.',
        'step_4_label' => 'Kopioi allekirjoitussalaisuus',
        'step_4_description' => 'Kopioi "Signing Secret" -arvo "Webhook Details" -osasta ja liitä se "Stripe Webhook Secret" -kenttään "Stripe"-osiossa "Maksu"-välilehdellä "Asetukset"-sivulla.',
    ],
    'no_payment_charge' => 'Ei maksumaksua. Yritä uudelleen!',
    'payment_failed' => 'Maksu epäonnistui!',
    'payment_type' => 'Maksutyyppi',
];
