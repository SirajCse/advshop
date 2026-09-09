<?php

return [
    'payment_description' => 'Le client peut acheter un produit et payer directement en utilisant Visa, carte de crédit via :name',
    'api_key' => 'Clé API',
    'api_key_helper' => 'Obtenez votre clé API depuis votre tableau de bord Mollie',
    'webhook_secret' => 'Webhook Secret (optionnel)',
    'webhook_secret_helper' => 'Optionnel : Ajoutez un webhook secret pour une sécurité renforcée. Configurez ceci dans votre tableau de bord Mollie sous Développeurs > Webhooks',
    'register_account' => 'Créer un compte sur :name',
    'after_registration' => 'Après inscription sur :name, vous aurez une clé API',
    'enter_api_key' => 'Entrez la clé API dans la case à droite',
    'webhook_configuration' => 'Configuration du Webhook :',
    'webhook_url_instruction' => 'Dans votre tableau de bord Mollie, configurez l\'URL du webhook comme :',
    'webhook_note' => 'Remarque : Remplacez {token} par le jeton de paiement réel. Le webhook sera automatiquement appelé par Mollie pour mettre à jour le statut du paiement.',
    'security_optional' => 'Sécurité (optionnel) :',
    'security_instruction' => 'Pour une sécurité renforcée, vous pouvez configurer un webhook secret dans votre tableau de bord Mollie sous Développeurs > Webhooks, puis le saisir dans le champ Webhook Secret.',
];
