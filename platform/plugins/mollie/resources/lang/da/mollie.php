<?php

return [
    'payment_description' => 'Kunden kan købe produkt og betale direkte med Visa, kreditkort via :name',
    'api_key' => 'API-nøgle',
    'api_key_helper' => 'Få din API-nøgle fra dit Mollie Dashboard',
    'webhook_secret' => 'Webhook Secret (valgfrit)',
    'webhook_secret_helper' => 'Valgfrit: Tilføj en webhook secret for forbedret sikkerhed. Konfigurer dette i dit Mollie Dashboard under Developers > Webhooks',
    'register_account' => 'Opret en konto på :name',
    'after_registration' => 'Efter registrering på :name vil du have en API-nøgle',
    'enter_api_key' => 'Indtast API-nøgle i boksen til højre',
    'webhook_configuration' => 'Webhook-konfiguration:',
    'webhook_url_instruction' => 'I dit Mollie Dashboard skal du konfigurere webhook URL\'en som:',
    'webhook_note' => 'Bemærk: Erstat {token} med det faktiske betalingstoken. Webhook\'en vil automatisk blive kaldt af Mollie for at opdatere betalingsstatus.',
    'security_optional' => 'Sikkerhed (valgfrit):',
    'security_instruction' => 'For forbedret sikkerhed kan du konfigurere en webhook secret i dit Mollie Dashboard under Developers > Webhooks og derefter indtaste den i feltet Webhook Secret.',
];
