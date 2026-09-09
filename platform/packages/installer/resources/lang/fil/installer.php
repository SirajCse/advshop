<?php

return [
    'title' => 'Pag-install',
    'next' => 'Susunod na Hakbang',
    'forms' => [
        'errorTitle' => 'Nangyari ang mga sumusunod na error:',
    ],
    'welcome' => [
        'title' => 'Maligayang pagdating',
        'message' => 'Bago magsimula, kailangan namin ng ilang impormasyon tungkol sa database. Kailangan mong malaman ang mga sumusunod na item bago magpatuloy.',
        'language' => 'Wika',
        'next' => 'Tara na',
    ],
    'requirements' => [
        'title' => 'Mga Kinakailangan ng Server',
    ],
    'permissions' => [
        'next' => 'I-configure ang Kapaligiran',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Mga Setting ng Kapaligiran',
            'form' => [
                'name_required' => 'Kailangan ang pangalan ng kapaligiran.',
                'app_name_label' => 'Pamagat ng site',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Koneksyon sa Database',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Database host',
                'db_port_label' => 'Database port',
                'db_name_label' => 'Pangalan ng database',
                'db_name_placeholder' => 'Pangalan ng database',
                'db_username_label' => 'Username ng database',
                'db_username_placeholder' => 'Username ng database',
                'db_password_label' => 'Password ng database',
                'db_password_placeholder' => 'Password ng database',
                'buttons' => [
                    'install' => 'I-install',
                ],
                'db_host_helper' => 'Kung gumagamit ka ng Laravel Sail, baguhin lang ang DB_HOST sa DB_HOST=mysql. Sa ilang hosting, maaaring localhost ang DB_HOST sa halip na 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Na-save ang iyong mga setting ng .env file.',
        'errors' => 'Hindi ma-save ang .env file, Pakigawa ito nang manu-mano.',
    ],
    'theme' => [
        'title' => 'Pumili ng tema',
        'message' => 'Pumili ng tema upang i-personalize ang hitsura ng iyong website. Ang pagpiling ito ay mag-import din ng sample data na inangkop sa napiling tema.',
    ],
    'theme_preset' => [
        'title' => 'Pumili ng preset ng tema',
        'message' => 'Pumili ng preset ng tema upang i-personalize ang hitsura ng iyong website. Ang pagpiling ito ay mag-import din ng sample data na inangkop sa napiling tema.',
    ],
    'createAccount' => [
        'title' => 'Lumikha ng account',
        'form' => [
            'first_name' => 'Unang pangalan',
            'last_name' => 'Apelyido',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Kumpirmasyon ng password',
            'create' => 'Lumikha',
        ],
    ],
    'license' => [
        'title' => 'I-activate ang Lisensya',
        'skip' => 'Laktawan muna',
    ],
    'final' => [
        'pageTitle' => 'Tapos na ang Pag-install',
        'title' => 'Tapos na',
        'message' => 'Matagumpay na na-install ang application.',
        'exit' => 'Pumunta sa admin dashboard',
    ],
    'install_step_title' => 'Pag-install - Hakbang :step: :title',
];
