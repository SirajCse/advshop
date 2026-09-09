<?php

return [
    'webhook_secret' => 'Секретний ключ Webhook',
    'webhook_secret_helper' => 'Знайдіть це в Stripe Dashboard у розділі Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Знайдіть це в Stripe Dashboard у розділі Developers > API keys.',
    'secret_key_helper' => 'Знайдіть це в Stripe Dashboard у розділі Developers > API keys. Зберігайте цей ключ у таємниці.',
    'webhook_setup_guide' => [
        'title' => 'Посібник з налаштування Webhook для Stripe',
        'description' => 'Дотримуйтесь цих кроків, щоб налаштувати Webhook для Stripe',
        'step_1_label' => 'Увійдіть до панелі керування Stripe',
        'step_1_description' => 'Перейдіть за посиланням :link і натисніть кнопку "Додати кінцеву точку" в розділі "Webhooks" вкладки "Розробники".',
        'step_2_label' => 'Виберіть подію та налаштуйте кінцеву точку',
        'step_2_description' => 'Виберіть подію "payment_intent.succeeded" і введіть наступну URL-адресу в поле "Endpoint URL": :url',
        'step_3_label' => 'Додати кінцеву точку',
        'step_3_description' => 'Натисніть кнопку "Додати кінцеву точку", щоб зберегти webhook.',
        'step_4_label' => 'Скопіюйте секретний ключ підпису',
        'step_4_description' => 'Скопіюйте значення "Signing Secret" з розділу "Webhook Details" і вставте його в поле "Stripe Webhook Secret" у розділі "Stripe" вкладки "Оплата" на сторінці "Налаштування".',
    ],
    'no_payment_charge' => 'Немає плати за платіж. Будь ласка, спробуйте ще раз!',
    'payment_failed' => 'Платіж не вдався!',
    'payment_type' => 'Тип Платежу',
];
