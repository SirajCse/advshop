<?php

return [
    'payment_description' => 'Il cliente può acquistare prodotti e pagare direttamente usando Visa, carta di credito tramite :name',
    'api_key' => 'Chiave API',
    'api_key_helper' => 'Ottieni la tua chiave API dalla tua Dashboard Mollie',
    'webhook_secret' => 'Webhook Secret (opzionale)',
    'webhook_secret_helper' => 'Opzionale: Aggiungi un webhook secret per una maggiore sicurezza. Configura questo nella tua Dashboard Mollie sotto Developers > Webhooks',
    'register_account' => 'Registra un account su :name',
    'after_registration' => 'Dopo la registrazione su :name, avrai una chiave API',
    'enter_api_key' => 'Inserisci la chiave API nella casella a destra',
    'webhook_configuration' => 'Configurazione Webhook:',
    'webhook_url_instruction' => 'Nella tua Dashboard Mollie, configura l\'URL del webhook come:',
    'webhook_note' => 'Nota: Sostituisci {token} con il token di pagamento effettivo. Il webhook verrà chiamato automaticamente da Mollie per aggiornare lo stato del pagamento.',
    'security_optional' => 'Sicurezza (opzionale):',
    'security_instruction' => 'Per una maggiore sicurezza, puoi configurare un webhook secret nella tua Dashboard Mollie sotto Developers > Webhooks, quindi inseriscilo nel campo Webhook Secret.',
];
