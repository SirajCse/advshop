<?php

return [
    'webhook_secret' => 'Webhook noslēpums',
    'webhook_secret_helper' => 'Atrodiet to savā Stripe Dashboard sadaļā Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Atrodiet to savā Stripe Dashboard sadaļā Developers > API keys.',
    'secret_key_helper' => 'Atrodiet to savā Stripe Dashboard sadaļā Developers > API keys. Glabājiet šo atslēgu konfidenciāli.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook iestatīšanas rokasgrāmata',
        'description' => 'Izpildiet šīs darbības, lai iestatītu Stripe webhook',
        'step_1_label' => 'Piesakieties Stripe informācijas panelī',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Atlasiet notikumu un konfigurējiet galapunktu',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Pievienot galapunktu',
        'step_3_description' => 'Noklikšķiniet uz pogas "Pievienot galapunktu", lai saglabātu webhook.',
        'step_4_label' => 'Kopēt parakstīšanas noslēpumu',
        'step_4_description' => 'Nokopējiet "Signing Secret" vērtību no "Webhook Details" sadaļas un ielīmējiet to "Stripe Webhook Secret" laukā "Stripe" sadaļā "Maksājums" cilnē "Iestatījumi" lapā.',
    ],
    'no_payment_charge' => 'Nav maksājuma maksas. Lūdzu, mēģiniet vēlreiz!',
    'payment_failed' => 'Maksājums neizdevās!',
    'payment_type' => 'Maksājuma veids',
];
