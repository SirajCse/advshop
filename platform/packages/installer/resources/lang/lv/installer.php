<?php

return [
    'title' => 'Instalācija',
    'next' => 'Nākamais solis',
    'forms' => [
        'errorTitle' => 'Radās šādas kļūdas:',
    ],
    'welcome' => [
        'title' => 'Laipni lūdzam',
        'message' => 'Pirms sākšanas mums ir nepieciešama informācija par datu bāzi. Pirms turpināt, jums būs jāzina šādi elementi.',
        'language' => 'Valoda',
        'next' => 'Sāksim',
    ],
    'requirements' => [
        'title' => 'Servera prasības',
    ],
    'permissions' => [
        'next' => 'Konfigurēt vidi',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Vides iestatījumi',
            'form' => [
                'name_required' => 'Vides nosaukums ir obligāts.',
                'app_name_label' => 'Vietnes nosaukums',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Datu bāzes savienojums',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Datu bāzes hosta',
                'db_port_label' => 'Datu bāzes ports',
                'db_name_label' => 'Datu bāzes nosaukums',
                'db_name_placeholder' => 'Datu bāzes nosaukums',
                'db_username_label' => 'Datu bāzes lietotājvārds',
                'db_username_placeholder' => 'Datu bāzes lietotājvārds',
                'db_password_label' => 'Datu bāzes parole',
                'db_password_placeholder' => 'Datu bāzes parole',
                'buttons' => [
                    'install' => 'Instalēt',
                ],
                'db_host_helper' => 'Ja izmantojat Laravel Sail, vienkārši mainiet DB_HOST uz DB_HOST=mysql. Dažos hostingos DB_HOST var būt localhost, nevis 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Jūsu .env faila iestatījumi ir saglabāti.',
        'errors' => 'Nevar saglabāt .env failu, lūdzu, izveidojiet to manuāli.',
    ],
    'theme' => [
        'title' => 'Izvēlieties tēmu',
        'message' => 'Izvēlieties tēmu, lai personalizētu savas vietnes izskatu. Šī izvēle arī importēs parauga datus, kas pielāgoti izvēlētajai tēmai.',
    ],
    'theme_preset' => [
        'title' => 'Izvēlieties tēmas iestatījumu',
        'message' => 'Izvēlieties tēmas iestatījumu, lai personalizētu savas vietnes izskatu. Šī izvēle arī importēs parauga datus, kas pielāgoti izvēlētajai tēmai.',
    ],
    'createAccount' => [
        'title' => 'Izveidot kontu',
        'form' => [
            'first_name' => 'Vārds',
            'last_name' => 'Uzvārds',
            'username' => 'Lietotājvārds',
            'email' => 'E-pasts',
            'password' => 'Parole',
            'password_confirmation' => 'Paroles apstiprinājums',
            'create' => 'Izveidot',
        ],
    ],
    'license' => [
        'title' => 'Aktivizēt licenci',
        'skip' => 'Pagaidām izlaist',
    ],
    'final' => [
        'pageTitle' => 'Instalācija pabeigta',
        'title' => 'Gatavs',
        'message' => 'Lietojumprogramma ir veiksmīgi instalēta.',
        'exit' => 'Doties uz administratora paneļi',
    ],
    'install_step_title' => 'Instalācija - Solis :step: :title',
];
