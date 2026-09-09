<?php

return [
    'payment_description' => 'Клієнт може купити продукт і оплатити безпосередньо використовуючи Visa, кредитну картку через :name',
    'api_key' => 'API ключ',
    'api_key_helper' => 'Отримайте свій API ключ з панелі керування Mollie',
    'webhook_secret' => 'Webhook Secret (необов\'язково)',
    'webhook_secret_helper' => 'Необов\'язково: Додайте webhook secret для підвищення безпеки. Налаштуйте це в панелі керування Mollie в розділі Developers > Webhooks',
    'register_account' => 'Зареєструйте обліковий запис на :name',
    'after_registration' => 'Після реєстрації на :name у вас буде API ключ',
    'enter_api_key' => 'Введіть API ключ в поле справа',
    'webhook_configuration' => 'Конфігурація Webhook:',
    'webhook_url_instruction' => 'В панелі керування Mollie налаштуйте URL webhook як:',
    'webhook_note' => 'Примітка: Замініть {token} на фактичний токен платежу. Webhook буде автоматично викликано Mollie для оновлення статусу платежу.',
    'security_optional' => 'Безпека (необов\'язково):',
    'security_instruction' => 'Для підвищення безпеки ви можете налаштувати webhook secret в панелі керування Mollie в розділі Developers > Webhooks, потім ввести його в поле Webhook Secret.',
];
