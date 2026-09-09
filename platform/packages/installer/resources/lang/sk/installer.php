<?php

return [
    'title' => 'Inštalácia',
    'next' => 'Ďalší krok',
    'forms' => [
        'errorTitle' => 'Vyskytli sa nasledujúce chyby:',
    ],
    'welcome' => [
        'title' => 'Vitajte',
        'message' => 'Pred začatím potrebujeme niektoré informácie o databáze. Pred pokračovaním musíte poznať nasledujúce položky.',
        'language' => 'Jazyk',
        'next' => 'Začnime',
    ],
    'requirements' => [
        'title' => 'Požiadavky na server',
    ],
    'permissions' => [
        'next' => 'Konfigurovať prostredie',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Nastavenia prostredia',
            'form' => [
                'name_required' => 'Vyžaduje sa názov prostredia.',
                'app_name_label' => 'Názov stránky',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Pripojenie k databáze',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Hostiteľ databázy',
                'db_port_label' => 'Port databázy',
                'db_name_label' => 'Názov databázy',
                'db_name_placeholder' => 'Názov databázy',
                'db_username_label' => 'Používateľské meno databázy',
                'db_username_placeholder' => 'Používateľské meno databázy',
                'db_password_label' => 'Heslo databázy',
                'db_password_placeholder' => 'Heslo databázy',
                'buttons' => [
                    'install' => 'Inštalovať',
                ],
                'db_host_helper' => 'Ak používate Laravel Sail, jednoducho zmeňte DB_HOST na DB_HOST=mysql. Na niektorých hostingoch môže byť DB_HOST localhost namiesto 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Nastavenia vášho súboru .env boli uložené.',
        'errors' => 'Nie je možné uložiť súbor .env, vytvorte ho prosím manuálne.',
    ],
    'theme' => [
        'title' => 'Vyberte tému',
        'message' => 'Vyberte tému na prispôsobenie vzhľadu vašej webovej stránky. Tento výber tiež importuje vzorové údaje prispôsobené vybranej téme.',
    ],
    'theme_preset' => [
        'title' => 'Vyberte predvoľbu témy',
        'message' => 'Vyberte predvoľbu témy na prispôsobenie vzhľadu vašej webovej stránky. Tento výber tiež importuje vzorové údaje prispôsobené vybranej téme.',
    ],
    'createAccount' => [
        'title' => 'Vytvoriť účet',
        'form' => [
            'first_name' => 'Krstné meno',
            'last_name' => 'Priezvisko',
            'username' => 'Používateľské meno',
            'email' => 'Email',
            'password' => 'Heslo',
            'password_confirmation' => 'Potvrdenie hesla',
            'create' => 'Vytvoriť',
        ],
    ],
    'license' => [
        'title' => 'Aktivovať licenciu',
        'skip' => 'Preskočiť zatiaľ',
    ],
    'final' => [
        'pageTitle' => 'Inštalácia dokončená',
        'title' => 'Hotovo',
        'message' => 'Aplikácia bola úspešne nainštalovaná.',
        'exit' => 'Prejsť na admin panel',
    ],
    'install_step_title' => 'Inštalácia - Krok :step: :title',
];
