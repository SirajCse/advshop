<?php

return [
    'webhook_secret' => 'Webhook тајна',
    'webhook_secret_helper' => 'Пронађите ово у Stripe Dashboard-у под Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Пронађите ово у Stripe Dashboard-у под Developers > API keys.',
    'secret_key_helper' => 'Пронађите ово у Stripe Dashboard-у под Developers > API keys. Чувајте овај кључ поверљивим.',
    'webhook_setup_guide' => [
        'title' => 'Водич за подешавање Stripe Webhook-а',
        'description' => 'Пратите ове кораке да бисте подесили Stripe webhook',
        'step_1_label' => 'Пријавите се на Stripe контролну таблу',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Изаберите догађај и конфигуришите крајњу тачку',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Додај крајњу тачку',
        'step_3_description' => 'Кликните на дугме "Додај крајњу тачку" да бисте сачували webhook.',
        'step_4_label' => 'Копирај тајну потписа',
        'step_4_description' => 'Копирајте вредност "Signing Secret" из одељка "Webhook Details" и налепите је у поље "Stripe Webhook Secret" у одељку "Stripe" на картици "Плаћање" на страници "Подешавања".',
    ],
    'no_payment_charge' => 'Нема накнаде за плаћање. Молимо покушајте поново!',
    'payment_failed' => 'Плаћање није успело!',
    'payment_type' => 'Тип плаћања',
];
