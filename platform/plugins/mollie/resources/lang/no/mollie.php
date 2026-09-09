<?php

return [
    'payment_description' => 'Kunden kan kjøpe produkt og betale direkte med Visa, kredittkort via :name',
    'api_key' => 'API-nøkkel',
    'api_key_helper' => 'Få din API-nøkkel fra ditt Mollie Dashboard',
    'webhook_secret' => 'Webhook Secret (valgfritt)',
    'webhook_secret_helper' => 'Valgfritt: Legg til en webhook secret for forbedret sikkerhet. Konfigurer dette i ditt Mollie Dashboard under Developers > Webhooks',
    'register_account' => 'Registrer en konto på :name',
    'after_registration' => 'Etter registrering på :name vil du ha en API-nøkkel',
    'enter_api_key' => 'Angi API-nøkkel i boksen til høyre',
    'webhook_configuration' => 'Webhook-konfigurasjon:',
    'webhook_url_instruction' => 'I ditt Mollie Dashboard, konfigurer webhook URL som:',
    'webhook_note' => 'Merk: Erstatt {token} med det faktiske betalingstokenet. Webhooken vil automatisk bli kalt av Mollie for å oppdatere betalingsstatus.',
    'security_optional' => 'Sikkerhet (valgfritt):',
    'security_instruction' => 'For forbedret sikkerhet kan du konfigurere en webhook secret i ditt Mollie Dashboard under Developers > Webhooks, og deretter angi den i Webhook Secret-feltet.',
];
