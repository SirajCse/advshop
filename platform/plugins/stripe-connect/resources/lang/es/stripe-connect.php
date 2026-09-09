<?php

return [
    'stripe_account_id' => 'ID de cuenta de Stripe',
    'go_to_dashboard' => 'Ir al panel Express',
    'connect' => [
        'label' => 'Conectar con Stripe',
        'description' => 'Conecta tu cuenta de Stripe para recibir pagos.',
    ],
    'disconnect' => [
        'label' => 'Desconectar Stripe',
        'confirm' => '¿Estás seguro de que quieres desconectar tu cuenta de Stripe?',
    ],
    'notifications' => [
        'connected' => 'Tu cuenta de Stripe ha sido conectada.',
        'disconnected' => 'Tu cuenta de Stripe ha sido desconectada.',
        'now_active' => 'Tu cuenta de Stripe está ahora activa.',
    ],
    'withdrawal' => [
        'payout_info' => 'Tu pago será transferido automáticamente a tu cuenta de Stripe con ID: :stripe_account_id.',
    ],
];
