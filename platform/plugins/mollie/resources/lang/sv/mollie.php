<?php

return [
    'payment_description' => 'Kunden kan köpa produkt och betala direkt med Visa, kreditkort via :name',
    'api_key' => 'API-nyckel',
    'api_key_helper' => 'Hämta din API-nyckel från din Mollie Dashboard',
    'webhook_secret' => 'Webhook Secret (valfritt)',
    'webhook_secret_helper' => 'Valfritt: Lägg till en webhook secret för förbättrad säkerhet. Konfigurera detta i din Mollie Dashboard under Developers > Webhooks',
    'register_account' => 'Registrera ett konto på :name',
    'after_registration' => 'Efter registrering på :name kommer du att ha en API-nyckel',
    'enter_api_key' => 'Ange API-nyckel i rutan till höger',
    'webhook_configuration' => 'Webhook-konfiguration:',
    'webhook_url_instruction' => 'I din Mollie Dashboard, konfigurera webhook-URL:en som:',
    'webhook_note' => 'Obs: Ersätt {token} med den faktiska betalningstoken. Webhooken kommer automatiskt att anropas av Mollie för att uppdatera betalningsstatus.',
    'security_optional' => 'Säkerhet (valfritt):',
    'security_instruction' => 'För förbättrad säkerhet kan du konfigurera en webhook secret i din Mollie Dashboard under Developers > Webhooks och sedan ange den i fältet Webhook Secret.',
];
