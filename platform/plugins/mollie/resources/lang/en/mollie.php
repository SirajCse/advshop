<?php

return [
    'payment_description' => 'Customer can buy product and pay directly using Visa, Credit card via :name',
    'api_key' => 'API Key',
    'api_key_helper' => 'Get your API key from your Mollie Dashboard',
    'webhook_secret' => 'Webhook Secret (Optional)',
    'webhook_secret_helper' => 'Optional: Add a webhook secret for enhanced security. Configure this in your Mollie Dashboard under Developers > Webhooks',
    'register_account' => 'Register an account on :name',
    'after_registration' => 'After registration at :name, you will have API key',
    'enter_api_key' => 'Enter API key into the box in right hand',
    'webhook_configuration' => 'Webhook Configuration:',
    'webhook_url_instruction' => 'In your Mollie Dashboard, configure the webhook URL as:',
    'webhook_note' => 'Note: Replace {token} with the actual payment token. The webhook will be automatically called by Mollie to update payment status.',
    'security_optional' => 'Security (Optional):',
    'security_instruction' => 'For enhanced security, you can configure a webhook secret in your Mollie Dashboard under Developers > Webhooks, then enter it in the Webhook Secret field.',
];
