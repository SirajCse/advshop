<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configura attività automatizzate in background per mantenere il tuo sito web funzionante senza problemi.',
    'is_not_ready' => 'Il cronjob non è ancora configurato',
    'is_not_ready_description' => 'Segui le istruzioni qui sotto per configurare il cronjob. Questo è necessario per funzionalità come promemoria carrello abbandonato, pianificazione email e altre attività automatizzate.',
    'is_working' => 'Il cronjob funziona correttamente!',
    'is_not_working' => 'Il cronjob ha smesso di funzionare',
    'is_not_working_description' => 'Il cronjob non è stato eseguito negli ultimi 10 minuti. Controlla la configurazione del server o contatta il tuo provider di hosting.',
    'last_checked' => 'Ultima attività: :time',
    'copy_button' => 'Copia comando',
    'what_is' => [
        'title' => 'Cos\'è un Cronjob?',
        'description' => 'Un cronjob è un\'attività automatizzata che viene eseguita in background sul tuo server. Permette al tuo sito web di eseguire automaticamente attività importanti senza che tu debba fare nulla manualmente.',
        'examples' => 'Esempi',
        'features' => 'Inviare promemoria carrello abbandonato, elaborare email programmate, pulire vecchi dati, generare report e altro.',
    ],
    'command' => [
        'title' => 'Il tuo comando Cronjob',
        'description' => 'Copia questo comando e aggiungilo al pannello di controllo del tuo hosting. Questo comando deve essere eseguito ogni minuto per far funzionare le tue attività automatizzate.',
    ],
    'setup' => [
        'name' => 'Come configurare',
        'copied' => 'Copiato negli appunti!',
        'choose_hosting' => 'Seleziona il tuo pannello di controllo hosting qui sotto e segui le istruzioni passo passo:',
    ],
    'cpanel' => [
        'step1' => 'Accedi al tuo account <strong>cPanel</strong>',
        'step2' => 'Trova e clicca su <strong>"Cron Jobs"</strong> nella sezione "Advanced"',
        'step3' => 'In "Add New Cron Job", seleziona <strong>"Once Per Minute (* * * * *)"</strong> dal menu a tendina',
        'step4' => '<strong>Incolla il comando</strong> che hai copiato sopra nel campo "Command"',
        'step5' => 'Clicca su <strong>"Add New Cron Job"</strong> per salvare',
    ],
    'plesk' => [
        'step1' => 'Accedi al tuo pannello di controllo <strong>Plesk</strong>',
        'step2' => 'Vai su <strong>"Scheduled Tasks"</strong> (o "Cron Jobs")',
        'step3' => 'Clicca su <strong>"Add Task"</strong> o <strong>"Schedule a Task"</strong>',
        'step4' => 'Imposta la pianificazione per eseguire <strong>ogni minuto</strong> e incolla il comando',
        'step5' => 'Clicca su <strong>"OK"</strong> o <strong>"Apply"</strong> per salvare',
    ],
    'directadmin' => [
        'step1' => 'Accedi al tuo pannello <strong>DirectAdmin</strong>',
        'step2' => 'Naviga su <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Clicca su <strong>"Add Cron Job"</strong>',
        'step4' => 'Imposta tutti i campi temporali su <strong>*</strong> (ogni minuto) e incolla il comando',
        'step5' => 'Clicca su <strong>"Add"</strong> per salvare il cronjob',
    ],
    'ssh' => [
        'step1' => 'Connettiti al tuo server via <strong>SSH</strong> usando Terminal o PuTTY',
        'step2' => 'Digita <code>crontab -e</code> e premi Invio per modificare il file crontab',
        'step3' => 'Aggiungi una nuova riga in fondo e <strong>incolla il comando</strong>',
        'step4' => 'Premi <strong>Ctrl+X</strong>, poi <strong>Y</strong>, poi <strong>Invio</strong> per salvare (per l\'editor nano)',
        'step5' => 'Il cronjob è ora attivo e verrà eseguito ogni minuto',
    ],
    'need_help' => 'Hai bisogno di aiuto? Contatta il tuo provider di hosting e chiedi di configurare un cronjob che venga eseguito ogni minuto con il comando mostrato sopra.',
];
