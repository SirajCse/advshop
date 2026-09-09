<?php

return [
    'webhook_secret' => 'Segredo do webhook',
    'webhook_secret_helper' => 'Encontre-o no seu Stripe Dashboard em Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Encontre-o no seu Stripe Dashboard em Developers > API keys.',
    'secret_key_helper' => 'Encontre-o no seu Stripe Dashboard em Developers > API keys. Mantenha esta chave confidencial.',
    'webhook_setup_guide' => [
        'title' => 'Guia de configuração do Stripe Webhook',
        'description' => 'Siga estas etapas para configurar um webhook Stripe',
        'step_1_label' => 'Faça login no painel Stripe',
        'step_1_description' => 'Aceda a :link e clique no botão "Add Endpoint" na secção "Webhooks" do separador "Developers".',
        'step_2_label' => 'Selecione Evento e Configure Endpoint',
        'step_2_description' => 'Selecione o evento "payment_intent.succeeded" e introduza o URL seguinte no campo "Endpoint URL": :url',
        'step_3_label' => 'Adicionar ponto final',
        'step_3_description' => 'Clique no botão "Adicionar endpoint" para salvar o webhook.',
        'step_4_label' => 'Copiar segredo de assinatura',
        'step_4_description' => 'Copie o valor "Signing Secret" da seção "Detalhes do Webhook" e cole-o no campo "Stripe Webhook Secret" na seção "Stripe" da guia "Pagamento" na página "Configurações".',
    ],
    'no_payment_charge' => 'Sem cobrança de pagamento. Por favor, tente novamente!',
    'payment_failed' => 'Pagamento falhou!',
    'payment_type' => 'Tipo de Pagamento',
];
