<?php

return [
    'name' => 'Cronjob',
    'description' => 'Richten Sie automatisierte Hintergrundaufgaben ein, damit Ihre Website reibungslos läuft.',
    'is_not_ready' => 'Cronjob ist noch nicht konfiguriert',
    'is_not_ready_description' => 'Bitte folgen Sie den Anweisungen unten, um den Cronjob einzurichten. Dies ist erforderlich für Funktionen wie Erinnerungen an abgebrochene Warenkörbe, E-Mail-Planung und andere automatisierte Aufgaben.',
    'is_working' => 'Cronjob läuft ordnungsgemäß!',
    'is_not_working' => 'Cronjob funktioniert nicht mehr',
    'is_not_working_description' => 'Der Cronjob wurde in den letzten 10 Minuten nicht ausgeführt. Bitte überprüfen Sie Ihre Serverkonfiguration oder wenden Sie sich an Ihren Hosting-Anbieter.',
    'last_checked' => 'Letzte Aktivität: :time',
    'copy_button' => 'Befehl kopieren',
    'what_is' => [
        'title' => 'Was ist ein Cronjob?',
        'description' => 'Ein Cronjob ist eine automatisierte Aufgabe, die im Hintergrund auf Ihrem Server ausgeführt wird. Er ermöglicht es Ihrer Website, wichtige Aufgaben automatisch auszuführen, ohne dass Sie etwas manuell tun müssen.',
        'examples' => 'Beispiele',
        'features' => 'Erinnerungen an abgebrochene Warenkörbe senden, geplante E-Mails verarbeiten, alte Daten bereinigen, Berichte erstellen und mehr.',
    ],
    'command' => [
        'title' => 'Ihr Cronjob-Befehl',
        'description' => 'Kopieren Sie diesen Befehl und fügen Sie ihn in Ihr Hosting-Kontrollpanel ein. Dieser Befehl muss jede Minute ausgeführt werden, damit Ihre automatisierten Aufgaben funktionieren.',
    ],
    'setup' => [
        'name' => 'Einrichtung',
        'copied' => 'In die Zwischenablage kopiert!',
        'choose_hosting' => 'Wählen Sie unten Ihr Hosting-Kontrollpanel aus und folgen Sie den Schritt-für-Schritt-Anweisungen:',
    ],
    'cpanel' => [
        'step1' => 'Melden Sie sich bei Ihrem <strong>cPanel</strong>-Konto an',
        'step2' => 'Suchen und klicken Sie auf <strong>"Cron Jobs"</strong> im Abschnitt "Advanced"',
        'step3' => 'Wählen Sie unter "Add New Cron Job" <strong>"Once Per Minute (* * * * *)"</strong> aus dem Timing-Dropdown',
        'step4' => '<strong>Fügen Sie den Befehl</strong> ein, den Sie oben kopiert haben, in das Feld "Command"',
        'step5' => 'Klicken Sie auf <strong>"Add New Cron Job"</strong> zum Speichern',
    ],
    'plesk' => [
        'step1' => 'Melden Sie sich bei Ihrem <strong>Plesk</strong>-Kontrollpanel an',
        'step2' => 'Gehen Sie zu <strong>"Scheduled Tasks"</strong> (oder "Cron Jobs")',
        'step3' => 'Klicken Sie auf <strong>"Add Task"</strong> oder <strong>"Schedule a Task"</strong>',
        'step4' => 'Stellen Sie den Zeitplan auf <strong>jede Minute</strong> ein und fügen Sie den Befehl ein',
        'step5' => 'Klicken Sie auf <strong>"OK"</strong> oder <strong>"Apply"</strong> zum Speichern',
    ],
    'directadmin' => [
        'step1' => 'Melden Sie sich bei Ihrem <strong>DirectAdmin</strong>-Panel an',
        'step2' => 'Navigieren Sie zu <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klicken Sie auf <strong>"Add Cron Job"</strong>',
        'step4' => 'Setzen Sie alle Zeitfelder auf <strong>*</strong> (jede Minute) und fügen Sie den Befehl ein',
        'step5' => 'Klicken Sie auf <strong>"Add"</strong> um den Cronjob zu speichern',
    ],
    'ssh' => [
        'step1' => 'Verbinden Sie sich mit Ihrem Server über <strong>SSH</strong> mit Terminal oder PuTTY',
        'step2' => 'Geben Sie <code>crontab -e</code> ein und drücken Sie Enter, um die Crontab-Datei zu bearbeiten',
        'step3' => 'Fügen Sie eine neue Zeile am Ende hinzu und <strong>fügen Sie den Befehl ein</strong>',
        'step4' => 'Drücken Sie <strong>Strg+X</strong>, dann <strong>Y</strong>, dann <strong>Enter</strong> zum Speichern (für nano-Editor)',
        'step5' => 'Der Cronjob ist jetzt aktiv und wird jede Minute ausgeführt',
    ],
    'need_help' => 'Brauchen Sie Hilfe? Kontaktieren Sie Ihren Hosting-Anbieter und bitten Sie ihn, einen Cronjob einzurichten, der jede Minute mit dem oben gezeigten Befehl ausgeführt wird.',
];
