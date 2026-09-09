<?php

return [
    'title' => 'Installation',
    'next' => 'Next Step',
    'forms' => [
        'errorTitle' => 'The following errors occurred:',
    ],
    'welcome' => [
        'title' => 'Welcome',
        'message' => 'Before getting started, we need some information on the database. You will need to know the following items before proceeding.',
        'language' => 'Language',
        'next' => 'Let\'s go',
    ],
    'requirements' => [
        'title' => 'Server Requirements',
        'php_version_required' => 'Version PHP :version requise',
    ],
    'permissions' => [
        'next' => 'Configure Environment',
    ],
    'environment' => [
        'wizard' => [
            'form' => [
                'app_url_label' => 'URL',
                'db_connection_label_mysql' => 'MySQL',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
                'name_required' => 'An environment name is required.',
                'app_name_label' => 'Site title',
                'db_connection_label' => 'Database Connection',
                'db_host_label' => 'Database host',
                'db_port_label' => 'Database port',
                'db_name_label' => 'Database name',
                'db_name_placeholder' => 'Database name',
                'db_username_label' => 'Database username',
                'db_username_placeholder' => 'Database username',
                'db_password_label' => 'Database password',
                'db_password_placeholder' => 'Database password',
                'buttons' => [
                    'install' => 'Install',
                ],
                'db_host_helper' => 'If you use Laravel Sail, just change DB_HOST to DB_HOST=mysql. On some hosting DB_HOST can be localhost instead of 127.0.0.1',
            ],
            'title' => 'Environment Settings',
        ],
        'success' => 'Your .env file settings have been saved.',
        'errors' => 'Unable to save the .env file, Please create it manually.',
    ],
    'theme' => [
        'title' => 'Choisir un thème',
        'message' => 'Choisissez un thème pour personnaliser l\'apparence de votre site Web. Cette sélection importera également des exemples de données adaptées au thème choisi.',
    ],
    'createAccount' => [
        'title' => 'Create account',
        'form' => [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Password confirmation',
            'create' => 'Create',
        ],
    ],
    'license' => [
        'title' => 'Activate License',
        'skip' => 'Skip for now',
    ],
    'final' => [
        'pageTitle' => 'Installation Finished',
        'title' => 'Done',
        'message' => 'Application has been successfully installed.',
        'exit' => 'Go to admin dashboard',
    ],
    'install_step_title' => 'Installation - Step :step: :title',
    'theme_preset' => [
        'title' => 'Choose theme preset',
        'message' => 'Choose a theme preset to personalize the appearance of your website. This selection will also import sample data tailored to the chosen theme.',
    ],
];
