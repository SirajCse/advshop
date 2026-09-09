<?php

return [
    'title' => 'Встановлення',
    'next' => 'Наступний крок',
    'forms' => [
        'errorTitle' => 'Виникли наступні помилки:',
    ],
    'welcome' => [
        'title' => 'Ласкаво просимо',
        'message' => 'Перед початком нам потрібна інформація про базу даних. Вам потрібно знати наступні пункти перед продовженням.',
        'language' => 'Мова',
        'next' => 'Почнемо',
    ],
    'requirements' => [
        'title' => 'Вимоги до сервера',
    ],
    'permissions' => [
        'next' => 'Налаштувати оточення',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Налаштування оточення',
            'form' => [
                'name_required' => 'Потрібна назва оточення.',
                'app_name_label' => 'Заголовок сайту',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Підключення до бази даних',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Хост бази даних',
                'db_port_label' => 'Порт бази даних',
                'db_name_label' => 'Назва бази даних',
                'db_name_placeholder' => 'Назва бази даних',
                'db_username_label' => 'Ім\'я користувача бази даних',
                'db_username_placeholder' => 'Ім\'я користувача бази даних',
                'db_password_label' => 'Пароль бази даних',
                'db_password_placeholder' => 'Пароль бази даних',
                'buttons' => [
                    'install' => 'Встановити',
                ],
                'db_host_helper' => 'Якщо ви використовуєте Laravel Sail, просто змініть DB_HOST на DB_HOST=mysql. На деяких хостингах DB_HOST може бути localhost замість 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Налаштування файлу .env було збережено.',
        'errors' => 'Неможливо зберегти файл .env, будь ласка, створіть його вручну.',
    ],
    'theme' => [
        'title' => 'Вибір теми',
        'message' => 'Виберіть тему для персоналізації зовнішнього вигляду вашого веб-сайту. Цей вибір також імпортує зразкові дані, пристосовані до вибраної теми.',
    ],
    'createAccount' => [
        'title' => 'Створити обліковий запис',
        'form' => [
            'first_name' => 'Ім\'я',
            'last_name' => 'Прізвище',
            'username' => 'Ім\'я користувача',
            'email' => 'Електронна пошта',
            'password' => 'Пароль',
            'password_confirmation' => 'Підтвердження паролю',
            'create' => 'Створити',
        ],
    ],
    'license' => [
        'title' => 'Активувати ліцензію',
        'skip' => 'Пропустити зараз',
    ],
    'final' => [
        'pageTitle' => 'Встановлення завершено',
        'title' => 'Готово',
        'message' => 'Застосунок успішно встановлено.',
        'exit' => 'Перейти до панелі адміністратора',
    ],
    'install_step_title' => 'Встановлення - Крок :step: :title',
];
