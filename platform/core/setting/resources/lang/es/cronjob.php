<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configure tareas automatizadas en segundo plano para mantener su sitio web funcionando sin problemas.',
    'is_not_ready' => 'El cronjob aún no está configurado',
    'is_not_ready_description' => 'Por favor, siga las instrucciones a continuación para configurar el cronjob. Esto es necesario para funciones como recordatorios de carrito abandonado, programación de correos electrónicos y otras tareas automatizadas.',
    'is_working' => '¡El cronjob está funcionando correctamente!',
    'is_not_working' => 'El cronjob ha dejado de funcionar',
    'is_not_working_description' => 'El cronjob no se ha ejecutado en los últimos 10 minutos. Por favor, verifique la configuración de su servidor o contacte a su proveedor de hosting.',
    'last_checked' => 'Última actividad: :time',
    'copy_button' => 'Copiar comando',
    'what_is' => [
        'title' => '¿Qué es un Cronjob?',
        'description' => 'Un cronjob es una tarea automatizada que se ejecuta en segundo plano en su servidor. Permite que su sitio web realice tareas importantes automáticamente sin que tenga que hacer nada manualmente.',
        'examples' => 'Ejemplos',
        'features' => 'Enviar recordatorios de carrito abandonado, procesar correos programados, limpiar datos antiguos, generar informes y más.',
    ],
    'command' => [
        'title' => 'Su comando de Cronjob',
        'description' => 'Copie este comando y agréguelo a su panel de control de hosting. Este comando debe ejecutarse cada minuto para que sus tareas automatizadas funcionen.',
    ],
    'setup' => [
        'name' => 'Cómo configurar',
        'copied' => '¡Copiado al portapapeles!',
        'choose_hosting' => 'Seleccione su panel de control de hosting a continuación y siga las instrucciones paso a paso:',
    ],
    'cpanel' => [
        'step1' => 'Inicie sesión en su cuenta de <strong>cPanel</strong>',
        'step2' => 'Busque y haga clic en <strong>"Cron Jobs"</strong> en la sección "Advanced"',
        'step3' => 'En "Add New Cron Job", seleccione <strong>"Once Per Minute (* * * * *)"</strong> del menú desplegable',
        'step4' => '<strong>Pegue el comando</strong> que copió arriba en el campo "Command"',
        'step5' => 'Haga clic en <strong>"Add New Cron Job"</strong> para guardar',
    ],
    'plesk' => [
        'step1' => 'Inicie sesión en su panel de control de <strong>Plesk</strong>',
        'step2' => 'Vaya a <strong>"Scheduled Tasks"</strong> (o "Cron Jobs")',
        'step3' => 'Haga clic en <strong>"Add Task"</strong> o <strong>"Schedule a Task"</strong>',
        'step4' => 'Configure el horario para ejecutar <strong>cada minuto</strong> y pegue el comando',
        'step5' => 'Haga clic en <strong>"OK"</strong> o <strong>"Apply"</strong> para guardar',
    ],
    'directadmin' => [
        'step1' => 'Inicie sesión en su panel de <strong>DirectAdmin</strong>',
        'step2' => 'Navegue a <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Haga clic en <strong>"Add Cron Job"</strong>',
        'step4' => 'Configure todos los campos de tiempo en <strong>*</strong> (cada minuto) y pegue el comando',
        'step5' => 'Haga clic en <strong>"Add"</strong> para guardar el cronjob',
    ],
    'ssh' => [
        'step1' => 'Conéctese a su servidor vía <strong>SSH</strong> usando Terminal o PuTTY',
        'step2' => 'Escriba <code>crontab -e</code> y presione Enter para editar el archivo crontab',
        'step3' => 'Agregue una nueva línea al final y <strong>pegue el comando</strong>',
        'step4' => 'Presione <strong>Ctrl+X</strong>, luego <strong>Y</strong>, luego <strong>Enter</strong> para guardar (para el editor nano)',
        'step5' => 'El cronjob ahora está activo y se ejecutará cada minuto',
    ],
    'need_help' => 'Necesita ayuda? Contacte a su proveedor de hosting y pídales que configuren un cronjob que se ejecute cada minuto con el comando mostrado arriba.',
];
