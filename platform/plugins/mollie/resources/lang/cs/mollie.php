<?php

return [
    'payment_description' => 'Zákazník může nakoupit produkt a zaplatit přímo pomocí Visa, kreditní karty přes :name',
    'api_key' => 'API klíč',
    'api_key_helper' => 'Získejte svůj API klíč z vašeho Mollie Dashboardu',
    'webhook_secret' => 'Webhook Secret (volitelné)',
    'webhook_secret_helper' => 'Volitelné: Přidejte webhook secret pro vylepšené zabezpečení. Nakonfigurujte to ve vašem Mollie Dashboardu pod Developers > Webhooks',
    'register_account' => 'Zaregistrujte účet na :name',
    'after_registration' => 'Po registraci na :name budete mít API klíč',
    'enter_api_key' => 'Zadejte API klíč do pole napravo',
    'webhook_configuration' => 'Konfigurace Webhooku:',
    'webhook_url_instruction' => 'Ve vašem Mollie Dashboardu nakonfigurujte webhook URL jako:',
    'webhook_note' => 'Poznámka: Nahraďte {token} skutečným platebním tokenem. Webhook bude automaticky volán Mollie pro aktualizaci stavu platby.',
    'security_optional' => 'Zabezpečení (volitelné):',
    'security_instruction' => 'Pro vylepšené zabezpečení můžete nakonfigurovat webhook secret ve vašem Mollie Dashboardu pod Developers > Webhooks a poté jej zadat do pole Webhook Secret.',
];
