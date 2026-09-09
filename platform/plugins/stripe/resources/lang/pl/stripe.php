<?php

return [
    'webhook_secret' => 'Sekret Webhook',
    'webhook_secret_helper' => 'Znajdziesz to w swoim Stripe Dashboard w sekcji Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Znajdziesz to w swoim Stripe Dashboard w sekcji Developers > API keys.',
    'secret_key_helper' => 'Znajdziesz to w swoim Stripe Dashboard w sekcji Developers > API keys. Zachowaj ten klucz w poufności.',
    'webhook_setup_guide' => [
        'title' => 'Przewodnik konfiguracji Stripe Webhook',
        'description' => 'Wykonaj te kroki, aby skonfigurować webhook Stripe',
        'step_1_label' => 'Zaloguj się do panelu Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Wybierz zdarzenie i skonfiguruj punkt końcowy',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Dodaj punkt końcowy',
        'step_3_description' => 'Kliknij przycisk "Dodaj punkt końcowy", aby zapisać webhook.',
        'step_4_label' => 'Skopiuj sekret podpisywania',
        'step_4_description' => 'Skopiuj wartość "Signing Secret" z sekcji "Webhook Details" i wklej ją do pola "Stripe Webhook Secret" w sekcji "Stripe" na karcie "Płatność" na stronie "Ustawienia".',
    ],
    'no_payment_charge' => 'Brak opłaty za płatność. Proszę spróbować ponownie!',
    'payment_failed' => 'Płatność nie powiodła się!',
    'payment_type' => 'Typ płatności',
];
