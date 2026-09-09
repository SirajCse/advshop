<?php

return [
    'title' => 'Instalacija',
    'next' => 'Sljedeći korak',
    'forms' => [
        'errorTitle' => 'Dogodile su se sljedeće greške:',
    ],
    'welcome' => [
        'title' => 'Dobrodošli',
        'message' => 'Prije početka, trebamo neke informacije o bazi podataka. Morat ćete znati sljedeće stavke prije nastavka.',
        'language' => 'Jezik',
        'next' => 'Počnimo',
    ],
    'requirements' => [
        'title' => 'Zahtjevi poslužitelja',
    ],
    'permissions' => [
        'next' => 'Konfiguriraj okruženje',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Postavke okruženja',
            'form' => [
                'name_required' => 'Naziv okruženja je obavezan.',
                'app_name_label' => 'Naslov stranice',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Veza s bazom podataka',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Host baze podataka',
                'db_port_label' => 'Port baze podataka',
                'db_name_label' => 'Naziv baze podataka',
                'db_name_placeholder' => 'Naziv baze podataka',
                'db_username_label' => 'Korisničko ime baze podataka',
                'db_username_placeholder' => 'Korisničko ime baze podataka',
                'db_password_label' => 'Lozinka baze podataka',
                'db_password_placeholder' => 'Lozinka baze podataka',
                'buttons' => [
                    'install' => 'Instaliraj',
                ],
                'db_host_helper' => 'Ako koristite Laravel Sail, jednostavno promijenite DB_HOST u DB_HOST=mysql. Na nekim hostinzima DB_HOST može biti localhost umjesto 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Postavke vaše .env datoteke su spremljene.',
        'errors' => 'Nije moguće spremiti .env datoteku, molimo stvorite je ručno.',
    ],
    'theme' => [
        'title' => 'Odaberite temu',
        'message' => 'Odaberite temu za personalizaciju izgleda vaše web stranice. Ovaj odabir će također uvesti primjere podataka prilagođenih odabranoj temi.',
    ],
    'theme_preset' => [
        'title' => 'Odaberite predložak teme',
        'message' => 'Odaberite predložak teme za personalizaciju izgleda vaše web stranice. Ovaj odabir će također uvesti primjere podataka prilagođenih odabranoj temi.',
    ],
    'createAccount' => [
        'title' => 'Stvori račun',
        'form' => [
            'first_name' => 'Ime',
            'last_name' => 'Prezime',
            'username' => 'Korisničko ime',
            'email' => 'Email',
            'password' => 'Lozinka',
            'password_confirmation' => 'Potvrda lozinke',
            'create' => 'Stvori',
        ],
    ],
    'license' => [
        'title' => 'Aktiviraj licencu',
        'skip' => 'Preskoči za sada',
    ],
    'final' => [
        'pageTitle' => 'Instalacija završena',
        'title' => 'Gotovo',
        'message' => 'Aplikacija je uspješno instalirana.',
        'exit' => 'Idi na admin nadzornu ploču',
    ],
    'install_step_title' => 'Instalacija - Korak :step: :title',
];
