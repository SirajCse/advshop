<?php

return [
    'title' => 'Installation',
    'next' => 'Nächster Schritt',
    'forms' => [
        'errorTitle' => 'Folgende Fehler sind aufgetreten:',
    ],
    'welcome' => [
        'title' => 'Willkommen',
        'message' => 'Bevor Sie beginnen, benötigen wir einige Informationen zur Datenbank. Sie müssen die folgenden Punkte kennen, bevor Sie fortfahren.',
        'language' => 'Sprache',
        'next' => 'Los geht\'s',
    ],
    'requirements' => [
        'title' => 'Serveranforderungen',
        'php_version_required' => 'PHP-Version :version erforderlich',
    ],
    'permissions' => [
        'next' => 'Umgebung konfigurieren',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Umgebungseinstellungen',
            'form' => [
                'name_required' => 'Ein Umgebungsname ist erforderlich.',
                'app_name_label' => 'Seitentitel',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Datenbankverbindung',
                'db_connection_label_mysql' => 'Mysql',
                'db_host_label' => 'Datenbankhost',
                'db_port_label' => 'Datenbankport',
                'db_name_label' => 'Datenbankname',
                'db_name_placeholder' => 'Datenbankname',
                'db_username_label' => 'Datenbankbenutzername',
                'db_username_placeholder' => 'Datenbankbenutzername',
                'db_password_label' => 'Datenbankpasswort',
                'db_password_placeholder' => 'Datenbankpasswort',
                'buttons' => [
                    'install' => 'Installieren',
                ],
                'db_host_helper' => 'Wenn Sie Laravel Sail verwenden, ändern Sie einfach DB_HOST zu DB_HOST=mysql. Bei einigen Hosting-Diensten kann DB_HOST auch localhost anstelle von 127.0.0.1 sein.',
                'db_connections' => [
                    'mysql' => 'Mysql',
                    'sqlite' => 'Sqlite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Ihre .env-Dateieinstellungen wurden gespeichert.',
        'errors' => 'Die .env-Datei konnte nicht gespeichert werden. Bitte erstellen Sie sie manuell.',
    ],
    'theme' => [
        'title' => 'Wählen Sie Thema',
        'message' => 'Wählen Sie ein Thema, um das Erscheinungsbild Ihrer Website zu personalisieren. Diese Auswahl importiert auch Beispieldaten, die auf das ausgewählte Thema zugeschnitten sind.',
    ],
    'createAccount' => [
        'title' => 'Konto erstellen',
        'form' => [
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'username' => 'Benutzername',
            'email' => 'E-Mail',
            'password' => 'Passwort',
            'password_confirmation' => 'Passwort bestätigen',
            'create' => 'Erstellen',
        ],
    ],
    'license' => [
        'title' => 'Lizenz aktivieren',
        'skip' => 'Jetzt überspringen',
    ],
    'final' => [
        'pageTitle' => 'Installation abgeschlossen',
        'title' => 'Fertig',
        'message' => 'Die Anwendung wurde erfolgreich installiert.',
        'exit' => 'Zum Administrations-Dashboard gehen',
    ],
    'install_step_title' => 'Installation - Step :step: :title',
];
