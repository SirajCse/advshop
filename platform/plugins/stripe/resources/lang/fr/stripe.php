<?php

return [
    'webhook_secret' => 'Secret du webhook',
    'webhook_secret_helper' => 'Retrouvez-le dans votre Stripe Dashboard sous Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Retrouvez-le dans votre Stripe Dashboard sous Developers > API keys.',
    'secret_key_helper' => 'Retrouvez-le dans votre Stripe Dashboard sous Developers > API keys. Gardez cette clé confidentielle.',
    'webhook_setup_guide' => [
        'title' => 'Guide de configuration du webhook Stripe',
        'description' => 'Suivez ces étapes pour configurer un webhook Stripe',
        'step_1_label' => 'Connectez-vous au tableau de bord Stripe',
        'step_1_description' => 'Accédez à :link puis cliquez sur le bouton « Add Endpoint » dans la section « Webhooks » de l\'onglet « Developers ».',
        'step_2_label' => 'Sélectionnez un événement et configurez le point de terminaison',
        'step_2_description' => 'Sélectionnez l\'événement « payment_intent.succeeded » puis saisissez l\'URL suivante dans le champ « Endpoint URL » : :url',
        'step_3_label' => 'Ajouter un point de terminaison',
        'step_3_description' => 'Cliquez sur le bouton « Ajouter un point de terminaison » pour enregistrer le webhook.',
        'step_4_label' => 'Copier le secret de signature',
        'step_4_description' => 'Copiez la valeur « Signing Secret » de la section « Détails du Webhook » et collez-la dans le champ « Stripe Webhook Secret » de la section « Stripe » de l\'onglet « Paiement » de la page « Paramètres ».',
    ],
    'no_payment_charge' => 'Aucun frais de paiement. Veuillez réessayer !',
    'payment_failed' => 'Le paiement a échoué !',
    'payment_type' => 'Type de Paiement',
];
