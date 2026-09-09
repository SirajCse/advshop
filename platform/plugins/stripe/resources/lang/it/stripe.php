<?php

return [
    'webhook_secret' => 'Segreto Webhook',
    'webhook_secret_helper' => 'Trovalo nella tua Stripe Dashboard in Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Trovalo nella tua Stripe Dashboard in Developers > API keys.',
    'secret_key_helper' => 'Trovalo nella tua Stripe Dashboard in Developers > API keys. Mantieni questa chiave riservata.',
    'webhook_setup_guide' => [
        'title' => 'Guida alla configurazione del Webhook Stripe',
        'description' => 'Segui questi passaggi per configurare un webhook Stripe',
        'step_1_label' => 'Accedi alla dashboard di Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Seleziona evento e configura endpoint',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Aggiungi endpoint',
        'step_3_description' => 'Fai clic sul pulsante "Aggiungi endpoint" per salvare il webhook.',
        'step_4_label' => 'Copia segreto di firma',
        'step_4_description' => 'Copia il valore "Signing Secret" dalla sezione "Webhook Details" e incollalo nel campo "Stripe Webhook Secret" nella sezione "Stripe" della scheda "Pagamento" nella pagina "Impostazioni".',
    ],
    'no_payment_charge' => 'Nessun addebito di pagamento. Per favore riprova!',
    'payment_failed' => 'Pagamento fallito!',
    'payment_type' => 'Tipo di Pagamento',
];
