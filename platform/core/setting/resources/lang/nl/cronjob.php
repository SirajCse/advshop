<?php

return [
    'name' => 'Cronjob',
    'description' => 'Stel geautomatiseerde achtergrondtaken in om uw website soepel te laten draaien.',
    'is_not_ready' => 'Cronjob is nog niet geconfigureerd',
    'is_not_ready_description' => 'Volg de onderstaande instructies om de cronjob in te stellen. Dit is vereist voor functies zoals herinneringen voor achtergelaten winkelwagens, e-mailplanning en andere geautomatiseerde taken.',
    'is_working' => 'Cronjob werkt correct!',
    'is_not_working' => 'Cronjob is gestopt met werken',
    'is_not_working_description' => 'De cronjob is de afgelopen 10 minuten niet uitgevoerd. Controleer uw serverconfiguratie of neem contact op met uw hostingprovider.',
    'last_checked' => 'Laatste activiteit: :time',
    'copy_button' => 'Opdracht kopiëren',
    'what_is' => [
        'title' => 'Wat is een Cronjob?',
        'description' => 'Een cronjob is een geautomatiseerde taak die op de achtergrond van uw server draait. Het stelt uw website in staat om automatisch belangrijke taken uit te voeren zonder dat u iets handmatig hoeft te doen.',
        'examples' => 'Voorbeelden',
        'features' => 'Herinneringen voor achtergelaten winkelwagens verzenden, geplande e-mails verwerken, oude gegevens opschonen, rapporten genereren en meer.',
    ],
    'command' => [
        'title' => 'Uw Cronjob-opdracht',
        'description' => 'Kopieer deze opdracht en voeg deze toe aan uw hosting configuratiescherm. Deze opdracht moet elke minuut worden uitgevoerd om uw geautomatiseerde taken werkend te houden.',
    ],
    'setup' => [
        'name' => 'Hoe in te stellen',
        'copied' => 'Gekopieerd naar klembord!',
        'choose_hosting' => 'Selecteer hieronder uw hosting configuratiescherm en volg de stapsgewijze instructies:',
    ],
    'cpanel' => [
        'step1' => 'Log in op uw <strong>cPanel</strong>-account',
        'step2' => 'Zoek en klik op <strong>"Cron Jobs"</strong> in het gedeelte "Advanced"',
        'step3' => 'Selecteer onder "Add New Cron Job" <strong>"Once Per Minute (* * * * *)"</strong> uit het timing dropdown menu',
        'step4' => '<strong>Plak de opdracht</strong> die u hierboven hebt gekopieerd in het veld "Command"',
        'step5' => 'Klik op <strong>"Add New Cron Job"</strong> om op te slaan',
    ],
    'plesk' => [
        'step1' => 'Log in op uw <strong>Plesk</strong>-configuratiescherm',
        'step2' => 'Ga naar <strong>"Scheduled Tasks"</strong> (of "Cron Jobs")',
        'step3' => 'Klik op <strong>"Add Task"</strong> of <strong>"Schedule a Task"</strong>',
        'step4' => 'Stel het schema in om <strong>elke minuut</strong> uit te voeren en plak de opdracht',
        'step5' => 'Klik op <strong>"OK"</strong> of <strong>"Apply"</strong> om op te slaan',
    ],
    'directadmin' => [
        'step1' => 'Log in op uw <strong>DirectAdmin</strong>-paneel',
        'step2' => 'Navigeer naar <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klik op <strong>"Add Cron Job"</strong>',
        'step4' => 'Stel alle tijdvelden in op <strong>*</strong> (elke minuut) en plak de opdracht',
        'step5' => 'Klik op <strong>"Add"</strong> om de cronjob op te slaan',
    ],
    'ssh' => [
        'step1' => 'Maak verbinding met uw server via <strong>SSH</strong> met Terminal of PuTTY',
        'step2' => 'Typ <code>crontab -e</code> en druk op Enter om het crontab-bestand te bewerken',
        'step3' => 'Voeg een nieuwe regel onderaan toe en <strong>plak de opdracht</strong>',
        'step4' => 'Druk op <strong>Ctrl+X</strong>, dan <strong>Y</strong>, dan <strong>Enter</strong> om op te slaan (voor nano-editor)',
        'step5' => 'De cronjob is nu actief en zal elke minuut worden uitgevoerd',
    ],
    'need_help' => 'Hulp nodig? Neem contact op met uw hostingprovider en vraag hen om een cronjob in te stellen die elke minuut wordt uitgevoerd met de hierboven getoonde opdracht.',
];
