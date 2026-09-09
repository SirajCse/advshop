<?php

return [
    'title' => 'Installatie',
    'next' => 'Volgende stap',
    'forms' => [
        'errorTitle' => 'De volgende fouten zijn opgetreden:',
    ],
    'welcome' => [
        'title' => 'Welkom',
        'message' => 'Voordat we beginnen, hebben we wat informatie over de database nodig. U moet de volgende items weten voordat u verdergaat.',
        'language' => 'Taal',
        'next' => 'Laten we beginnen',
    ],
    'requirements' => [
        'title' => 'Serververeisten',
    ],
    'permissions' => [
        'next' => 'Omgeving configureren',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Omgevingsinstellingen',
            'form' => [
                'name_required' => 'Een omgevingsnaam is vereist.',
                'app_name_label' => 'Sitetitel',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Databaseverbinding',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Database host',
                'db_port_label' => 'Database poort',
                'db_name_label' => 'Databasenaam',
                'db_name_placeholder' => 'Databasenaam',
                'db_username_label' => 'Database gebruikersnaam',
                'db_username_placeholder' => 'Database gebruikersnaam',
                'db_password_label' => 'Database wachtwoord',
                'db_password_placeholder' => 'Database wachtwoord',
                'buttons' => [
                    'install' => 'Installeren',
                ],
                'db_host_helper' => 'Als u Laravel Sail gebruikt, wijzig dan gewoon DB_HOST naar DB_HOST=mysql. Bij sommige hostingproviders kan DB_HOST localhost zijn in plaats van 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Uw .env bestandsinstellingen zijn opgeslagen.',
        'errors' => 'Kan het .env bestand niet opslaan, maak het handmatig aan.',
    ],
    'theme' => [
        'title' => 'Kies thema',
        'message' => 'Kies een thema om het uiterlijk van uw website te personaliseren. Deze selectie importeert ook voorbeeldgegevens die zijn afgestemd op het gekozen thema.',
    ],
    'theme_preset' => [
        'title' => 'Kies thema voorinstelling',
        'message' => 'Kies een thema voorinstelling om het uiterlijk van uw website te personaliseren. Deze selectie importeert ook voorbeeldgegevens die zijn afgestemd op het gekozen thema.',
    ],
    'createAccount' => [
        'title' => 'Account aanmaken',
        'form' => [
            'first_name' => 'Voornaam',
            'last_name' => 'Achternaam',
            'username' => 'Gebruikersnaam',
            'email' => 'E-mail',
            'password' => 'Wachtwoord',
            'password_confirmation' => 'Wachtwoordbevestiging',
            'create' => 'Aanmaken',
        ],
    ],
    'license' => [
        'title' => 'Licentie activeren',
        'skip' => 'Voorlopig overslaan',
    ],
    'final' => [
        'pageTitle' => 'Installatie voltooid',
        'title' => 'Klaar',
        'message' => 'Applicatie is succesvol geïnstalleerd.',
        'exit' => 'Ga naar admin dashboard',
    ],
    'install_step_title' => 'Installatie - Stap :step: :title',
];
