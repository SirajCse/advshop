<?php

return [
    'name' => 'Anúncios',

    'enums' => [
        'announce_placement' => [
            'top' => 'Topo',
            'bottom' => 'Fixo na parte inferior',
            'popup' => 'Popup',
            'theme' => 'Integrado ao tema',
        ],

        'text_alignment' => [
            'start' => 'Início',
            'center' => 'Centro',
        ],
    ],

    'validation' => [
        'font_size' => 'O tamanho da fonte deve ser um valor válido de tamanho de fonte CSS.',
        'text_color' => 'A cor do texto deve ser um valor de cor hexadecimal válido.',
    ],

    'create' => 'Criar novo anúncio',
    'add_new' => 'Adicionar novo',
    'settings' => [
        'name' => 'Anúncio',
        'description' => 'Gerenciar configurações de anúncios',
    ],

    'background_color' => 'Cor de fundo',
    'font_size' => 'Tamanho da fonte',
    'font_size_help' => 'Deixe vazio para usar o padrão. Exemplo: 1rem, 1em, 12px, ...',
    'text_color' => 'Cor do texto',
    'start_date' => 'Data de início',
    'end_date' => 'Data de término',
    'has_action' => 'Tem ação',
    'action_label' => 'Rótulo da ação',
    'action_url' => 'URL da ação',
    'action_open_new_tab' => 'Abrir em nova aba',
    'dismissible_label' => 'Permitir que o usuário feche o anúncio',
    'placement' => 'Posicionamento',
    'text_alignment' => 'Alinhamento do texto',
    'is_active' => 'Está ativo',
    'max_width' => 'Largura máxima',
    'max_width_help' => 'Deixe vazio para usar o valor padrão. Exemplo: 100%, 500px, ...',
    'max_width_unit' => 'Unidade de largura máxima',
    'font_size_unit' => 'Unidade de tamanho da fonte',
    'autoplay_label' => 'Reprodução automática',
    'autoplay_delay_label' => 'Atraso da reprodução automática',
    'autoplay_delay_help' => 'O atraso entre cada anúncio em milissegundos. Deixe vazio para usar o valor padrão (5000).',
    'lazy_loading' => 'Carregamento preguiçoso',
    'lazy_loading_description' => 'Ative esta opção para melhorar a velocidade de carregamento da página',
    'hide_on_mobile' => 'Ocultar no celular',
    'dismiss' => 'Fechar',

    // Placeholders and help texts
    'name_placeholder' => 'Digite o nome do anúncio',
    'name_help' => 'Nome apenas para referência interna, não visível para os usuários',
    'content_placeholder' => 'Digite sua mensagem de anúncio aqui...',
    'content_help' => 'A mensagem que será exibida aos usuários. Suporta formatação HTML.',
    'start_date_placeholder' => 'Selecione a data e hora de início',
    'start_date_help' => 'O anúncio estará visível a partir desta data. Deixe vazio para começar imediatamente.',
    'end_date_placeholder' => 'Selecione a data e hora de término',
    'end_date_help' => 'O anúncio será ocultado após esta data. Deixe vazio para sem expiração.',
    'has_action_help' => 'Adicionar um botão de chamada para ação ao seu anúncio',
    'action_label_placeholder' => 'ex.: Saiba Mais, Compre Agora',
    'action_label_help' => 'Texto exibido no botão de ação',
    'action_url_placeholder' => 'https://exemplo.com/pagina',
    'action_url_help' => 'URL para onde os usuários serão redirecionados ao clicar no botão de ação',
    'action_open_new_tab_help' => 'Abrir o link de ação em uma nova aba do navegador',
    'is_active_help' => 'Ativar ou desativar este anúncio sem excluí-lo',
];
