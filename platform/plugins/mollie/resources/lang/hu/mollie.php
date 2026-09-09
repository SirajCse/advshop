<?php

return [
    'payment_description' => 'Az ügyfél terméket vásárolhat és közvetlenül fizethet Visa, hitelkártyával a :name-n keresztül',
    'api_key' => 'API kulcs',
    'api_key_helper' => 'Szerezze be API kulcsát a Mollie Dashboardról',
    'webhook_secret' => 'Webhook Secret (opcionális)',
    'webhook_secret_helper' => 'Opcionális: Adjon hozzá webhook secretet a fokozott biztonság érdekében. Konfigurálja ezt a Mollie Dashboardon a Developers > Webhooks alatt',
    'register_account' => 'Regisztráljon fiókot a :name oldalon',
    'after_registration' => 'A :name regisztráció után API kulcsa lesz',
    'enter_api_key' => 'Írja be az API kulcsot a jobb oldali mezőbe',
    'webhook_configuration' => 'Webhook konfiguráció:',
    'webhook_url_instruction' => 'A Mollie Dashboardon konfigurálja a webhook URL-t így:',
    'webhook_note' => 'Megjegyzés: Cserélje le a {token}-t a tényleges fizetési tokenre. A webhookot automatikusan meghívja a Mollie a fizetési állapot frissítéséhez.',
    'security_optional' => 'Biztonság (opcionális):',
    'security_instruction' => 'A fokozott biztonság érdekében konfigurálhat webhook secretet a Mollie Dashboardon a Developers > Webhooks alatt, majd írja be azt a Webhook Secret mezőbe.',
];
