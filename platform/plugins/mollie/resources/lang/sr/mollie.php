<?php

return [
    'payment_description' => 'Kupac može kupiti proizvod i platiti direktno koristeći Visa, kreditnu karticu preko :name',
    'api_key' => 'API ključ',
    'api_key_helper' => 'Nabavite svoj API ključ sa Mollie kontrolne table',
    'webhook_secret' => 'Webhook Secret (opciono)',
    'webhook_secret_helper' => 'Opciono: Dodajte webhook secret za poboljšanu bezbednost. Konfigurišite ovo na Mollie kontrolnoj tabli pod Developers > Webhooks',
    'register_account' => 'Registrujte nalog na :name',
    'after_registration' => 'Nakon registracije na :name, imaćete API ključ',
    'enter_api_key' => 'Unesite API ključ u polje sa desne strane',
    'webhook_configuration' => 'Konfiguracija Webhooka:',
    'webhook_url_instruction' => 'Na Mollie kontrolnoj tabli konfigurišite webhook URL kao:',
    'webhook_note' => 'Napomena: Zamenite {token} sa stvarnim tokenom plaćanja. Webhook će automatski biti pozvan od strane Mollie da ažurira status plaćanja.',
    'security_optional' => 'Bezbednost (opciono):',
    'security_instruction' => 'Za poboljšanu bezbednost možete konfigurisati webhook secret na Mollie kontrolnoj tabli pod Developers > Webhooks, zatim ga unesite u polje Webhook Secret.',
];
