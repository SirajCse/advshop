<?php

return [
    'payment_description' => 'Asiakas voi ostaa tuotteen ja maksaa suoraan käyttämällä Visa, luottokorttia :name kautta',
    'api_key' => 'API-avain',
    'api_key_helper' => 'Hanki API-avaimesi Mollie-hallintapaneelista',
    'webhook_secret' => 'Webhook Secret (valinnainen)',
    'webhook_secret_helper' => 'Valinnainen: Lisää webhook secret parannetun tietoturvan vuoksi. Määritä tämä Mollie-hallintapaneelissa kohdassa Developers > Webhooks',
    'register_account' => 'Rekisteröi tili :name',
    'after_registration' => 'Rekisteröitymisen jälkeen :name sinulla on API-avain',
    'enter_api_key' => 'Syötä API-avain oikeanpuoleiseen kenttään',
    'webhook_configuration' => 'Webhook-määritys:',
    'webhook_url_instruction' => 'Määritä Mollie-hallintapaneelissa webhook-URL seuraavasti:',
    'webhook_note' => 'Huomautus: Korvaa {token} todellisella maksutokenilla. Mollie kutsuu webhook\'ia automaattisesti maksun tilan päivittämiseksi.',
    'security_optional' => 'Tietoturva (valinnainen):',
    'security_instruction' => 'Parannetun tietoturvan vuoksi voit määrittää webhook secretin Mollie-hallintapaneelissa kohdassa Developers > Webhooks ja syöttää sen sitten Webhook Secret -kenttään.',
];
