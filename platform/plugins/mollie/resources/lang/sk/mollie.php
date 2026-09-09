<?php

return [
    'payment_description' => 'Zákazník môže kúpiť produkt a zaplatiť priamo pomocou Visa, kreditnej karty cez :name',
    'api_key' => 'API kľúč',
    'api_key_helper' => 'Získajte svoj API kľúč z vášho Mollie Dashboardu',
    'webhook_secret' => 'Webhook Secret (voliteľné)',
    'webhook_secret_helper' => 'Voliteľné: Pridajte webhook secret pre zvýšenú bezpečnosť. Nakonfigurujte to vo vašom Mollie Dashboarde pod Developers > Webhooks',
    'register_account' => 'Zaregistrujte účet na :name',
    'after_registration' => 'Po registrácii na :name budete mať API kľúč',
    'enter_api_key' => 'Zadajte API kľúč do poľa napravo',
    'webhook_configuration' => 'Konfigurácia Webhooku:',
    'webhook_url_instruction' => 'Vo vašom Mollie Dashboarde nakonfigurujte webhook URL ako:',
    'webhook_note' => 'Poznámka: Nahraďte {token} skutočným platobným tokenom. Webhook bude automaticky volaný Mollie pre aktualizáciu stavu platby.',
    'security_optional' => 'Bezpečnosť (voliteľné):',
    'security_instruction' => 'Pre zvýšenú bezpečnosť môžete nakonfigurovať webhook secret vo vašom Mollie Dashboarde pod Developers > Webhooks a potom ho zadajte do poľa Webhook Secret.',
];
