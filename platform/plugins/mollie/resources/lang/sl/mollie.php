<?php

return [
    'payment_description' => 'Stranka lahko kupi izdelek in plača neposredno z uporabo Visa, kreditne kartice prek :name',
    'api_key' => 'API ključ',
    'api_key_helper' => 'Pridobite svoj API ključ iz nadzorne plošče Mollie',
    'webhook_secret' => 'Webhook Secret (izbirno)',
    'webhook_secret_helper' => 'Izbirno: Dodajte webhook secret za izboljšano varnost. Konfigurirajte to v nadzorni plošči Mollie pod Developers > Webhooks',
    'register_account' => 'Registrirajte račun na :name',
    'after_registration' => 'Po registraciji na :name boste imeli API ključ',
    'enter_api_key' => 'Vnesite API ključ v polje na desni strani',
    'webhook_configuration' => 'Konfiguracija Webhooka:',
    'webhook_url_instruction' => 'V nadzorni plošči Mollie konfigurirajte webhook URL kot:',
    'webhook_note' => 'Opomba: Zamenjajte {token} z dejanskim žetonom plačila. Webhook bo samodejno klican s strani Mollie za posodobitev stanja plačila.',
    'security_optional' => 'Varnost (izbirno):',
    'security_instruction' => 'Za izboljšano varnost lahko konfigurirate webhook secret v nadzorni plošči Mollie pod Developers > Webhooks in ga nato vnesete v polje Webhook Secret.',
];
