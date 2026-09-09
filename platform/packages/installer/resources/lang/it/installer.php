<?php

return [
    'title' => 'Installazione',
    'next' => 'Prossimo passo',
    'forms' => [
        'errorTitle' => 'Si sono verificati i seguenti errori:',
    ],
    'welcome' => [
        'title' => 'Benvenuto',
        'message' => 'Prima di iniziare, abbiamo bisogno di alcune informazioni sul database. Dovrai conoscere i seguenti elementi prima di procedere.',
        'language' => 'Lingua',
        'next' => 'Iniziamo',
    ],
    'requirements' => [
        'title' => 'Requisiti del server',
    ],
    'permissions' => [
        'next' => 'Configura ambiente',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Impostazioni ambiente',
            'form' => [
                'name_required' => 'È richiesto un nome per l\'ambiente.',
                'app_name_label' => 'Titolo del sito',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Connessione database',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Host database',
                'db_port_label' => 'Porta database',
                'db_name_label' => 'Nome database',
                'db_name_placeholder' => 'Nome database',
                'db_username_label' => 'Username database',
                'db_username_placeholder' => 'Username database',
                'db_password_label' => 'Password database',
                'db_password_placeholder' => 'Password database',
                'buttons' => [
                    'install' => 'Installa',
                ],
                'db_host_helper' => 'Se usi Laravel Sail, cambia semplicemente DB_HOST in DB_HOST=mysql. Su alcuni hosting DB_HOST può essere localhost invece di 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Le impostazioni del tuo file .env sono state salvate.',
        'errors' => 'Impossibile salvare il file .env, crealo manualmente.',
    ],
    'theme' => [
        'title' => 'Scegli tema',
        'message' => 'Scegli un tema per personalizzare l\'aspetto del tuo sito web. Questa selezione importerà anche dati di esempio adattati al tema scelto.',
    ],
    'theme_preset' => [
        'title' => 'Scegli preset tema',
        'message' => 'Scegli un preset tema per personalizzare l\'aspetto del tuo sito web. Questa selezione importerà anche dati di esempio adattati al tema scelto.',
    ],
    'createAccount' => [
        'title' => 'Crea account',
        'form' => [
            'first_name' => 'Nome',
            'last_name' => 'Cognome',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Conferma password',
            'create' => 'Crea',
        ],
    ],
    'license' => [
        'title' => 'Attiva licenza',
        'skip' => 'Salta per ora',
    ],
    'final' => [
        'pageTitle' => 'Installazione completata',
        'title' => 'Fatto',
        'message' => 'L\'applicazione è stata installata con successo.',
        'exit' => 'Vai alla dashboard amministratore',
    ],
    'install_step_title' => 'Installazione - Passo :step: :title',
];
