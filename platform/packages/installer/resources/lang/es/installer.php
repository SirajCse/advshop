<?php

return [
    'title' => 'Instalación',
    'next' => 'Siguiente Paso',
    'forms' => [
        'errorTitle' => 'Se produjeron los siguientes errores:',
    ],
    'welcome' => [
        'title' => 'Bienvenido',
        'message' => 'Antes de comenzar, necesitamos información sobre la base de datos. Deberá conocer los siguientes elementos antes de continuar.',
        'language' => 'Idioma',
        'next' => 'Vamos',
    ],
    'requirements' => [
        'title' => 'Requisitos del servidor',
        'php_version_required' => 'Se requiere la versión :version de PHP',
    ],
    'permissions' => [
        'next' => 'Configurar Entorno',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Configuración del Entorno',
            'form' => [
                'name_required' => 'Se requiere un nombre de entorno.',
                'app_name_label' => 'Título del sitio',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Conexión a la base de datos',
                'db_connection_label_mysql' => 'mysql',
                'db_host_label' => 'Host de la base de datos',
                'db_port_label' => 'Puerto de la base de datos',
                'db_name_label' => 'Nombre de la base de datos',
                'db_name_placeholder' => 'Nombre de la base de datos',
                'db_username_label' => 'Nombre de usuario de la base de datos',
                'db_username_placeholder' => 'Nombre de usuario de la base de datos',
                'db_password_label' => 'Contraseña de la base de datos',
                'db_password_placeholder' => 'Contraseña de la base de datos',
                'buttons' => [
                    'install' => 'Instalar',
                ],
                'db_host_helper' => 'Si usa Laravel Sail, simplemente cambie DB_HOST a DB_HOST=mysql. En algunos alojamientos, DB_HOST puede ser localhost en lugar de 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'mysql',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'La configuración de su archivo .env se ha guardado.',
        'errors' => 'No se pudo guardar el archivo .env. Por favor, créelo manualmente.',
    ],
    'theme' => [
        'title' => 'Escoge un tema',
        'message' => 'Elija un tema para personalizar la apariencia de su sitio web. Esta selección también importará datos de muestra adaptados al tema elegido.',
    ],
    'createAccount' => [
        'title' => 'Crear cuenta',
        'form' => [
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'username' => 'Nombre de usuario',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'password_confirmation' => 'Confirmación de contraseña',
            'create' => 'Crear',
        ],
    ],
    'license' => [
        'title' => 'Activar licencia',
        'skip' => 'Saltar por ahora',
    ],
    'final' => [
        'pageTitle' => 'Instalación finalizada',
        'title' => 'Hecho',
        'message' => 'La aplicación se ha instalado correctamente.',
        'exit' => 'Ir al panel de administración',
    ],
    'install_step_title' => 'Installation - Step :step: :title',
];
