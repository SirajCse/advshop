<?php

return [
    'payment_description' => 'Clientul poate cumpăra produse și poate plăti direct folosind Visa, card de credit prin :name',
    'api_key' => 'Cheie API',
    'api_key_helper' => 'Obțineți cheia API din panoul dumneavoastră Mollie',
    'webhook_secret' => 'Webhook Secret (opțional)',
    'webhook_secret_helper' => 'Opțional: Adăugați un webhook secret pentru securitate îmbunătățită. Configurați acest lucru în panoul Mollie sub Developers > Webhooks',
    'register_account' => 'Înregistrați un cont pe :name',
    'after_registration' => 'După înregistrarea pe :name, veți avea o cheie API',
    'enter_api_key' => 'Introduceți cheia API în caseta din dreapta',
    'webhook_configuration' => 'Configurare Webhook:',
    'webhook_url_instruction' => 'În panoul dumneavoastră Mollie, configurați URL-ul webhook-ului ca:',
    'webhook_note' => 'Notă: Înlocuiți {token} cu tokenul de plată real. Webhook-ul va fi apelat automat de Mollie pentru a actualiza starea plății.',
    'security_optional' => 'Securitate (opțional):',
    'security_instruction' => 'Pentru securitate îmbunătățită, puteți configura un webhook secret în panoul Mollie sub Developers > Webhooks, apoi introduceți-l în câmpul Webhook Secret.',
];
