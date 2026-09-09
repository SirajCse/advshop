<?php

return [
    'webhook_secret' => 'Secret Webhook',
    'webhook_secret_helper' => 'Găsiți-l în Stripe Dashboard sub Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Găsiți-l în Stripe Dashboard sub Developers > API keys.',
    'secret_key_helper' => 'Găsiți-l în Stripe Dashboard sub Developers > API keys. Păstrați această cheie confidențială.',
    'webhook_setup_guide' => [
        'title' => 'Ghid de configurare Stripe Webhook',
        'description' => 'Urmați acești pași pentru a configura un webhook Stripe',
        'step_1_label' => 'Conectați-vă la tabloul de bord Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Selectați evenimentul și configurați punctul final',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Adăugați punctul final',
        'step_3_description' => 'Faceți clic pe butonul "Adăugați punct final" pentru a salva webhook-ul.',
        'step_4_label' => 'Copiați secretul de semnare',
        'step_4_description' => 'Copiați valoarea "Signing Secret" din secțiunea "Webhook Details" și lipiți-o în câmpul "Stripe Webhook Secret" din secțiunea "Stripe" de pe fila "Plată" din pagina "Setări".',
    ],
    'no_payment_charge' => 'Fără taxă de plată. Vă rugăm să încercați din nou!',
    'payment_failed' => 'Plata a eșuat!',
    'payment_type' => 'Tip de plată',
];
