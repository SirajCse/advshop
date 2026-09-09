<?php

return [
    'payment_description' => 'O cliente pode comprar produtos e pagar diretamente usando Visa, cartão de crédito através de :name',
    'api_key' => 'Chave API',
    'api_key_helper' => 'Obtenha a sua chave API no seu painel Mollie',
    'webhook_secret' => 'Webhook Secret (opcional)',
    'webhook_secret_helper' => 'Opcional: Adicione um webhook secret para segurança aprimorada. Configure isto no seu painel Mollie em Developers > Webhooks',
    'register_account' => 'Registar uma conta em :name',
    'after_registration' => 'Após o registo em :name, terá uma chave API',
    'enter_api_key' => 'Introduza a chave API na caixa à direita',
    'webhook_configuration' => 'Configuração do Webhook:',
    'webhook_url_instruction' => 'No seu painel Mollie, configure o URL do webhook como:',
    'webhook_note' => 'Nota: Substitua {token} pelo token de pagamento real. O webhook será automaticamente chamado pela Mollie para atualizar o estado do pagamento.',
    'security_optional' => 'Segurança (opcional):',
    'security_instruction' => 'Para segurança aprimorada, pode configurar um webhook secret no seu painel Mollie em Developers > Webhooks e depois introduzi-lo no campo Webhook Secret.',
];
