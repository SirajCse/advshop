<?php

return [
    'payment_description' => 'Клиент может купить продукт и оплатить напрямую используя Visa, кредитную карту через :name',
    'api_key' => 'API ключ',
    'api_key_helper' => 'Получите свой API ключ из панели управления Mollie',
    'webhook_secret' => 'Webhook Secret (необязательно)',
    'webhook_secret_helper' => 'Необязательно: Добавьте webhook secret для повышения безопасности. Настройте это в панели управления Mollie в разделе Developers > Webhooks',
    'register_account' => 'Зарегистрируйте учетную запись на :name',
    'after_registration' => 'После регистрации на :name у вас будет API ключ',
    'enter_api_key' => 'Введите API ключ в поле справа',
    'webhook_configuration' => 'Конфигурация Webhook:',
    'webhook_url_instruction' => 'В панели управления Mollie настройте URL webhook как:',
    'webhook_note' => 'Примечание: Замените {token} на фактический токен платежа. Webhook будет автоматически вызван Mollie для обновления статуса платежа.',
    'security_optional' => 'Безопасность (необязательно):',
    'security_instruction' => 'Для повышения безопасности вы можете настроить webhook secret в панели управления Mollie в разделе Developers > Webhooks, затем ввести его в поле Webhook Secret.',
];
