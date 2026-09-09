<?php

return [
    'payment_description' => 'Kupac može kupiti proizvod i platiti izravno koristeći Visa, kreditnu karticu putem :name',
    'api_key' => 'API ključ',
    'api_key_helper' => 'Nabavite svoj API ključ s vašeg Mollie Dashboarda',
    'webhook_secret' => 'Webhook Secret (opcionalno)',
    'webhook_secret_helper' => 'Opcionalno: Dodajte webhook secret za poboljšanu sigurnost. Konfigurirajte ovo na vašem Mollie Dashboardu pod Developers > Webhooks',
    'register_account' => 'Registrirajte račun na :name',
    'after_registration' => 'Nakon registracije na :name, imat ćete API ključ',
    'enter_api_key' => 'Unesite API ključ u okvir s desne strane',
    'webhook_configuration' => 'Konfiguracija Webhooka:',
    'webhook_url_instruction' => 'Na vašem Mollie Dashboardu konfigurirajte webhook URL kao:',
    'webhook_note' => 'Napomena: Zamijenite {token} stvarnim tokenom plaćanja. Webhook će automatski biti pozvan od Mollie za ažuriranje statusa plaćanja.',
    'security_optional' => 'Sigurnost (opcionalno):',
    'security_instruction' => 'Za poboljšanu sigurnost možete konfigurirati webhook secret na vašem Mollie Dashboardu pod Developers > Webhooks, zatim ga unesite u polje Webhook Secret.',
];
