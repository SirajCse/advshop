<?php

return [
    'webhook_secret' => 'Webhook საიდუმლო',
    'webhook_secret_helper' => 'ეს შეგიძლიათ იხილოთ Stripe Dashboard-ში Developers > Webhooks > Signing secret განყოფილებაში.',
    'public_key_helper' => 'ეს შეგიძლიათ იხილოთ Stripe Dashboard-ში Developers > API keys განყოფილებაში.',
    'secret_key_helper' => 'ეს შეგიძლიათ იხილოთ Stripe Dashboard-ში Developers > API keys განყოფილებაში. შეინახეთ ეს გასაღები კონფიდენციალური.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook-ის დაყენების გზამკვლევი',
        'description' => 'მიჰყევით ამ ნაბიჯებს Stripe webhook-ის დასაყენებლად',
        'step_1_label' => 'შედით Stripe-ის დაფაზე',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'აირჩიეთ მოვლენა და კონფიგურაცია endpoint',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'დაამატეთ endpoint',
        'step_3_description' => 'დააწკაპუნეთ "Add Endpoint" ღილაკზე webhook-ის შესანახად.',
        'step_4_label' => 'დააკოპირეთ ხელმოწერის საიდუმლო',
        'step_4_description' => 'დააკოპირეთ "Signing Secret" მნიშვნელობა "Webhook Details" განყოფილებიდან და ჩასვით "Stripe Webhook Secret" ველში "Stripe" განყოფილებაში "გადახდა" ჩანართზე "პარამეტრები" გვერდზე.',
    ],
    'no_payment_charge' => 'გადახდის საკომისიო არ არის. გთხოვთ სცადოთ ხელახლა!',
    'payment_failed' => 'გადახდა ვერ განხორციელდა!',
    'payment_type' => 'გადახდის ტიპი',
];
