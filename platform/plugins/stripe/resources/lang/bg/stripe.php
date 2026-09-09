<?php

return [
    'webhook_secret' => 'Webhook тайна',
    'webhook_secret_helper' => 'Намерете го в Stripe Dashboard под Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Намерете го в Stripe Dashboard под Developers > API keys.',
    'secret_key_helper' => 'Намерете го в Stripe Dashboard под Developers > API keys. Пазете този ключ поверителен.',
    'webhook_setup_guide' => [
        'title' => 'Ръководство за настройка на Stripe Webhook',
        'description' => 'Следвайте тези стъпки, за да настроите Stripe webhook',
        'step_1_label' => 'Влезте в таблото за управление на Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Изберете събитие и конфигурирайте крайна точка',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Добавете крайна точка',
        'step_3_description' => 'Щракнете върху бутона "Добавяне на крайна точка", за да запазите webhook.',
        'step_4_label' => 'Копирайте подписващата тайна',
        'step_4_description' => 'Копирайте стойността "Signing Secret" от раздела "Детайли на Webhook" и я поставете в полето "Stripe Webhook Secret" в раздела "Stripe" на раздела "Плащане" на страницата "Настройки".',
    ],
    'no_payment_charge' => 'Няма такса за плащане. Моля, опитайте отново!',
    'payment_failed' => 'Плащането не успя!',
    'payment_type' => 'Тип на плащане',
];
