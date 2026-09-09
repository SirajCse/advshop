<?php

return [
    'payment_description' => 'Клиентът може да купи продукт и да плати директно чрез Visa, кредитна карта чрез :name',
    'api_key' => 'API ключ',
    'api_key_helper' => 'Вземете вашия API ключ от вашия Mollie таблото',
    'webhook_secret' => 'Webhook Secret (по избор)',
    'webhook_secret_helper' => 'По избор: Добавете webhook secret за повишена сигурност. Конфигурирайте това в вашия Mollie таблото под Developers > Webhooks',
    'register_account' => 'Регистрирайте акаунт в :name',
    'after_registration' => 'След регистрация в :name, ще имате API ключ',
    'enter_api_key' => 'Въведете API ключ в полето вдясно',
    'webhook_configuration' => 'Конфигурация на Webhook:',
    'webhook_url_instruction' => 'В таблото на Mollie конфигурирайте URL адреса на webhook като:',
    'webhook_note' => 'Забележка: Заменете {token} с действителния токен за плащане. Webhook ще бъде автоматично извикан от Mollie за актуализиране на статуса на плащането.',
    'security_optional' => 'Сигурност (по избор):',
    'security_instruction' => 'За повишена сигурност можете да конфигурирате webhook secret в таблото на Mollie под Developers > Webhooks, след което го въведете в полето Webhook Secret.',
];
