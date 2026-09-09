<?php

return [
    'webhook_secret' => 'Webhook Secret',
    'webhook_secret_helper' => 'Find this in your Stripe Dashboard under Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Find this in your Stripe Dashboard under Developers > API keys.',
    'secret_key_helper' => 'Find this in your Stripe Dashboard under Developers > API keys. Keep this key confidential.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook Setup Guide',
        'description' => 'Follow these steps to set up a Stripe webhook',
        'step_1_label' => 'Login to the Stripe Dashboard',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Select Events and Configure Endpoint',
        'step_2_description' => 'Enter the following URL in the "Endpoint URL" field: :url. Then select these events: checkout.session.completed, payment_intent.succeeded, payment_intent.payment_failed, charge.refunded.',
        'step_3_label' => 'Add Endpoint',
        'step_3_description' => 'Click the "Add Endpoint" button to save the webhook.',
        'step_4_label' => 'Copy Signing Secret',
        'step_4_description' => 'Copy the "Signing Secret" value from the "Webhook Details" section and paste it into the "Stripe Webhook Secret" field in the "Stripe" section of the "Payment" tab in the "Settings" page.',
    ],
    'no_payment_charge' => 'No payment charge. Please try again!',
    'payment_failed' => 'Payment failed!',
    'payment_type' => 'Payment Type',
];
