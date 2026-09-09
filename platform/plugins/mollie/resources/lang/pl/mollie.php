<?php

return [
    'payment_description' => 'Klient może kupić produkt i zapłacić bezpośrednio za pomocą Visa, karty kredytowej przez :name',
    'api_key' => 'Klucz API',
    'api_key_helper' => 'Uzyskaj swój klucz API z panelu Mollie',
    'webhook_secret' => 'Webhook Secret (opcjonalnie)',
    'webhook_secret_helper' => 'Opcjonalnie: Dodaj webhook secret dla zwiększonego bezpieczeństwa. Skonfiguruj to w panelu Mollie w sekcji Developers > Webhooks',
    'register_account' => 'Zarejestruj konto na :name',
    'after_registration' => 'Po rejestracji na :name będziesz mieć klucz API',
    'enter_api_key' => 'Wprowadź klucz API w polu po prawej stronie',
    'webhook_configuration' => 'Konfiguracja Webhooka:',
    'webhook_url_instruction' => 'W panelu Mollie skonfiguruj URL webhooka jako:',
    'webhook_note' => 'Uwaga: Zamień {token} na rzeczywisty token płatności. Webhook zostanie automatycznie wywołany przez Mollie w celu aktualizacji statusu płatności.',
    'security_optional' => 'Bezpieczeństwo (opcjonalnie):',
    'security_instruction' => 'Dla zwiększonego bezpieczeństwa możesz skonfigurować webhook secret w panelu Mollie w sekcji Developers > Webhooks, a następnie wprowadzić go w polu Webhook Secret.',
];
