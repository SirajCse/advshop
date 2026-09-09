<?php

return [
    'title' => 'Instalare',
    'next' => 'Pasul următor',
    'forms' => [
        'errorTitle' => 'Au apărut următoarele erori:',
    ],
    'welcome' => [
        'title' => 'Bun venit',
        'message' => 'Înainte de a începe, avem nevoie de câteva informații despre baza de date. Va trebui să cunoașteți următoarele elemente înainte de a continua.',
        'language' => 'Limbă',
        'next' => 'Să începem',
    ],
    'requirements' => [
        'title' => 'Cerințe server',
    ],
    'permissions' => [
        'next' => 'Configurează mediul',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Setări mediu',
            'form' => [
                'name_required' => 'Este necesar un nume de mediu.',
                'app_name_label' => 'Titlul site-ului',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Conexiune bază de date',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Gazdă bază de date',
                'db_port_label' => 'Port bază de date',
                'db_name_label' => 'Nume bază de date',
                'db_name_placeholder' => 'Nume bază de date',
                'db_username_label' => 'Nume utilizator bază de date',
                'db_username_placeholder' => 'Nume utilizator bază de date',
                'db_password_label' => 'Parolă bază de date',
                'db_password_placeholder' => 'Parolă bază de date',
                'buttons' => [
                    'install' => 'Instalează',
                ],
                'db_host_helper' => 'Dacă folosiți Laravel Sail, schimbați pur și simplu DB_HOST în DB_HOST=mysql. La unele gazde DB_HOST poate fi localhost în loc de 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Setările fișierului .env au fost salvate.',
        'errors' => 'Nu se poate salva fișierul .env, vă rugăm să îl creați manual.',
    ],
    'theme' => [
        'title' => 'Alegeți tema',
        'message' => 'Alegeți o temă pentru a personaliza aspectul site-ului dvs. web. Această selecție va importa și date eșantion adaptate temei alese.',
    ],
    'theme_preset' => [
        'title' => 'Alegeți presetarea temei',
        'message' => 'Alegeți o presetare a temei pentru a personaliza aspectul site-ului dvs. web. Această selecție va importa și date eșantion adaptate temei alese.',
    ],
    'createAccount' => [
        'title' => 'Creează cont',
        'form' => [
            'first_name' => 'Prenume',
            'last_name' => 'Nume',
            'username' => 'Nume utilizator',
            'email' => 'Email',
            'password' => 'Parolă',
            'password_confirmation' => 'Confirmare parolă',
            'create' => 'Creează',
        ],
    ],
    'license' => [
        'title' => 'Activează licența',
        'skip' => 'Omite deocamdată',
    ],
    'final' => [
        'pageTitle' => 'Instalare finalizată',
        'title' => 'Terminat',
        'message' => 'Aplicația a fost instalată cu succes.',
        'exit' => 'Mergi la panoul de administrare',
    ],
    'install_step_title' => 'Instalare - Pasul :step: :title',
];
