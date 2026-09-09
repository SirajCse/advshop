<?php

return [
    'webhook_secret' => 'Секрет Webhook',
    'webhook_secret_helper' => 'Найдите это в Stripe Dashboard в разделе Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Найдите это в Stripe Dashboard в разделе Developers > API keys.',
    'secret_key_helper' => 'Найдите это в Stripe Dashboard в разделе Developers > API keys. Храните этот ключ в тайне.',
    'webhook_setup_guide' => [
        'title' => 'Руководство по настройке Stripe Webhook',
        'description' => 'Следуйте этим шагам для настройки webhook Stripe',
        'step_1_label' => 'Войдите в панель управления Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Выберите событие и настройте конечную точку',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Добавить конечную точку',
        'step_3_description' => 'Нажмите кнопку "Добавить конечную точку", чтобы сохранить webhook.',
        'step_4_label' => 'Скопируйте секрет подписи',
        'step_4_description' => 'Скопируйте значение "Signing Secret" из раздела "Webhook Details" и вставьте его в поле "Stripe Webhook Secret" в разделе "Stripe" на вкладке "Платеж" на странице "Настройки".',
    ],
    'no_payment_charge' => 'Нет платы за платеж. Пожалуйста, попробуйте еще раз!',
    'payment_failed' => 'Платеж не удался!',
    'payment_type' => 'Тип платежа',
];
