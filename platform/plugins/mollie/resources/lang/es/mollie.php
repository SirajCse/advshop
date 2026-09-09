<?php

return [
    'payment_description' => 'El cliente puede comprar productos y pagar directamente usando Visa, tarjeta de crédito a través de :name',
    'api_key' => 'Clave API',
    'api_key_helper' => 'Obtén tu clave API desde tu Panel de Mollie',
    'webhook_secret' => 'Webhook Secret (opcional)',
    'webhook_secret_helper' => 'Opcional: Agrega un webhook secret para mayor seguridad. Configura esto en tu Panel de Mollie bajo Desarrolladores > Webhooks',
    'register_account' => 'Registra una cuenta en :name',
    'after_registration' => 'Después del registro en :name, tendrás una clave API',
    'enter_api_key' => 'Ingresa la clave API en el cuadro de la derecha',
    'webhook_configuration' => 'Configuración de Webhook:',
    'webhook_url_instruction' => 'En tu Panel de Mollie, configura la URL del webhook como:',
    'webhook_note' => 'Nota: Reemplaza {token} con el token de pago real. El webhook será llamado automáticamente por Mollie para actualizar el estado del pago.',
    'security_optional' => 'Seguridad (opcional):',
    'security_instruction' => 'Para mayor seguridad, puedes configurar un webhook secret en tu Panel de Mollie bajo Desarrolladores > Webhooks, luego ingrésalo en el campo Webhook Secret.',
];
