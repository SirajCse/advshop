<?php

return [
    'name' => 'Cronjob',
    'description' => 'Configurați sarcini automate în fundal pentru a menține site-ul dvs. funcționând fără probleme.',
    'is_not_ready' => 'Cronjob nu este încă configurat',
    'is_not_ready_description' => 'Vă rugăm să urmați instrucțiunile de mai jos pentru a configura cronjob-ul. Acesta este necesar pentru funcții precum memento-uri pentru coșuri abandonate, programarea e-mailurilor și alte sarcini automate.',
    'is_working' => 'Cronjob funcționează corect!',
    'is_not_working' => 'Cronjob a încetat să funcționeze',
    'is_not_working_description' => 'Cronjob nu a rulat în ultimele 10 minute. Vă rugăm să verificați configurația serverului sau să contactați furnizorul de găzduire.',
    'last_checked' => 'Ultima activitate: :time',
    'copy_button' => 'Copiază comanda',
    'what_is' => [
        'title' => 'Ce este un Cronjob?',
        'description' => 'Un cronjob este o sarcină automată care rulează în fundal pe serverul dvs. Permite site-ului dvs. să efectueze automat sarcini importante fără a fi nevoie să faceți nimic manual.',
        'examples' => 'Exemple',
        'features' => 'Trimiterea memento-urilor pentru coșuri abandonate, procesarea e-mailurilor programate, curățarea datelor vechi, generarea rapoartelor și altele.',
    ],
    'command' => [
        'title' => 'Comanda dvs. Cronjob',
        'description' => 'Copiați această comandă și adăugați-o la panoul de control al găzduirii. Această comandă trebuie să ruleze în fiecare minut pentru ca sarcinile automate să funcționeze.',
    ],
    'setup' => [
        'name' => 'Cum se configurează',
        'copied' => 'Copiat în clipboard!',
        'choose_hosting' => 'Selectați panoul de control al găzduirii de mai jos și urmați instrucțiunile pas cu pas:',
    ],
    'cpanel' => [
        'step1' => 'Conectați-vă la contul dvs. <strong>cPanel</strong>',
        'step2' => 'Găsiți și faceți clic pe <strong>"Cron Jobs"</strong> în secțiunea "Advanced"',
        'step3' => 'Sub "Add New Cron Job", selectați <strong>"Once Per Minute (* * * * *)"</strong> din meniul derulant',
        'step4' => '<strong>Lipiți comanda</strong> pe care ați copiat-o mai sus în câmpul "Command"',
        'step5' => 'Faceți clic pe <strong>"Add New Cron Job"</strong> pentru a salva',
    ],
    'plesk' => [
        'step1' => 'Conectați-vă la panoul de control <strong>Plesk</strong>',
        'step2' => 'Mergeți la <strong>"Scheduled Tasks"</strong> (sau "Cron Jobs")',
        'step3' => 'Faceți clic pe <strong>"Add Task"</strong> sau <strong>"Schedule a Task"</strong>',
        'step4' => 'Setați programarea să ruleze <strong>în fiecare minut</strong> și lipiți comanda',
        'step5' => 'Faceți clic pe <strong>"OK"</strong> sau <strong>"Apply"</strong> pentru a salva',
    ],
    'directadmin' => [
        'step1' => 'Conectați-vă la panoul <strong>DirectAdmin</strong>',
        'step2' => 'Navigați la <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Faceți clic pe <strong>"Add Cron Job"</strong>',
        'step4' => 'Setați toate câmpurile de timp la <strong>*</strong> (în fiecare minut) și lipiți comanda',
        'step5' => 'Faceți clic pe <strong>"Add"</strong> pentru a salva cronjob-ul',
    ],
    'ssh' => [
        'step1' => 'Conectați-vă la server prin <strong>SSH</strong> folosind Terminal sau PuTTY',
        'step2' => 'Tastați <code>crontab -e</code> și apăsați Enter pentru a edita fișierul crontab',
        'step3' => 'Adăugați o linie nouă în partea de jos și <strong>lipiți comanda</strong>',
        'step4' => 'Apăsați <strong>Ctrl+X</strong>, apoi <strong>Y</strong>, apoi <strong>Enter</strong> pentru a salva (pentru editorul nano)',
        'step5' => 'Cronjob-ul este acum activ și va rula în fiecare minut',
    ],
    'need_help' => 'Aveți nevoie de ajutor? Contactați furnizorul de găzduire și cereți-le să configureze un cronjob care să ruleze în fiecare minut cu comanda afișată mai sus.',
];
