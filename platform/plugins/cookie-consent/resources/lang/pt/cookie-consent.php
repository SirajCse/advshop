<?php

return [
    'theme_options' => [
        'name' => 'Consentimento de cookies',
        'description' => 'Configurações de consentimento de cookies',
        'enable' => 'Ativar consentimento de cookies',
        'message' => 'Mensagem',
        'button_text' => 'Texto do botão',
        'max_width' => 'Largura máxima (px)',
        'background_color' => 'Cor de fundo',
        'text_color' => 'Cor do texto',
        'learn_more_url' => 'Saiba mais URL',
        'learn_more_text' => 'Saiba mais texto',
        'style' => 'Estilo',
        'full_width' => 'Largura total',
        'minimal' => 'Mínimo',
        'floating' => 'Cartão flutuante',
        'modal' => 'Janela modal',
        'top_banner' => 'Banner superior',
        'show_reject_button' => 'Mostrar botão de rejeição',
        'show_reject_button_helper' => 'Quando ativado, os utilizadores verão um botão para rejeitar todos os cookies.',
        'show_customize_button' => 'Mostrar botão para personalizar preferências',
        'show_customize_button_helper' => 'Quando ativado, os utilizadores poderão personalizar as suas preferências de cookies, cumprindo o RGPD.',
    ],
    'message' => 'A sua experiência neste site será melhorada ao permitir cookies.',
    'button_text' => 'Permitir cookies',
    'reject_text' => 'Rejeitar',
    'customize_text' => 'Personalizar preferências',
    'save_text' => 'Guardar preferências',
    'cookie_categories' => [
        'essential' => [
            'name' => 'Essenciais',
            'description' => 'Estes cookies são essenciais para que o site funcione corretamente.',
        ],
        'analytics' => [
            'name' => 'Analíticos',
            'description' => 'Estes cookies ajudam-nos a compreender como os visitantes interagem com o site.',
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Estes cookies são usados para apresentar anúncios personalizados.',
        ],
    ],
];
