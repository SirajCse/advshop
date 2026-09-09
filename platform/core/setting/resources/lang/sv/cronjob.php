<?php

return [
    'name' => 'Cronjob',
    'description' => 'Konfigurera automatiserade bakgrundsuppgifter för att hålla din webbplats igång smidigt.',
    'is_not_ready' => 'Cronjob är inte konfigurerat ännu',
    'is_not_ready_description' => 'Följ instruktionerna nedan för att konfigurera cronjob. Detta krävs för funktioner som påminnelser om övergivna varukorgar, e-postschemaläggning och andra automatiserade uppgifter.',
    'is_working' => 'Cronjob fungerar korrekt!',
    'is_not_working' => 'Cronjob har slutat fungera',
    'is_not_working_description' => 'Cronjob har inte körts de senaste 10 minuterna. Kontrollera din serverkonfiguration eller kontakta din hostingleverantör.',
    'last_checked' => 'Senaste aktivitet: :time',
    'copy_button' => 'Kopiera kommando',
    'what_is' => [
        'title' => 'Vad är en Cronjob?',
        'description' => 'En cronjob är en automatiserad uppgift som körs i bakgrunden på din server. Den låter din webbplats utföra viktiga uppgifter automatiskt utan att du behöver göra något manuellt.',
        'examples' => 'Exempel',
        'features' => 'Skicka påminnelser om övergivna varukorgar, bearbeta schemalagda e-postmeddelanden, rensa gamla data, generera rapporter och mer.',
    ],
    'command' => [
        'title' => 'Ditt Cronjob-kommando',
        'description' => 'Kopiera detta kommando och lägg till det i din hostingkontrollpanel. Detta kommando måste köras varje minut för att dina automatiserade uppgifter ska fungera.',
    ],
    'setup' => [
        'name' => 'Hur man konfigurerar',
        'copied' => 'Kopierat till urklipp!',
        'choose_hosting' => 'Välj din hostingkontrollpanel nedan och följ steg-för-steg-instruktionerna:',
    ],
    'cpanel' => [
        'step1' => 'Logga in på ditt <strong>cPanel</strong>-konto',
        'step2' => 'Hitta och klicka på <strong>"Cron Jobs"</strong> under avsnittet "Advanced"',
        'step3' => 'Under "Add New Cron Job", välj <strong>"Once Per Minute (* * * * *)"</strong> från tidsrullgardinsmenyn',
        'step4' => '<strong>Klistra in kommandot</strong> du kopierade ovan i fältet "Command"',
        'step5' => 'Klicka på <strong>"Add New Cron Job"</strong> för att spara',
    ],
    'plesk' => [
        'step1' => 'Logga in på din <strong>Plesk</strong>-kontrollpanel',
        'step2' => 'Gå till <strong>"Scheduled Tasks"</strong> (eller "Cron Jobs")',
        'step3' => 'Klicka på <strong>"Add Task"</strong> eller <strong>"Schedule a Task"</strong>',
        'step4' => 'Ställ in schemat att köras <strong>varje minut</strong> och klistra in kommandot',
        'step5' => 'Klicka på <strong>"OK"</strong> eller <strong>"Apply"</strong> för att spara',
    ],
    'directadmin' => [
        'step1' => 'Logga in på din <strong>DirectAdmin</strong>-panel',
        'step2' => 'Navigera till <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klicka på <strong>"Add Cron Job"</strong>',
        'step4' => 'Ställ in alla tidsfält till <strong>*</strong> (varje minut) och klistra in kommandot',
        'step5' => 'Klicka på <strong>"Add"</strong> för att spara cronjob',
    ],
    'ssh' => [
        'step1' => 'Anslut till din server via <strong>SSH</strong> med Terminal eller PuTTY',
        'step2' => 'Skriv <code>crontab -e</code> och tryck Enter för att redigera crontab-filen',
        'step3' => 'Lägg till en ny rad längst ner och <strong>klistra in kommandot</strong>',
        'step4' => 'Tryck <strong>Ctrl+X</strong>, sedan <strong>Y</strong>, sedan <strong>Enter</strong> för att spara (för nano-redigeraren)',
        'step5' => 'Cronjob är nu aktiv och kommer att köras varje minut',
    ],
    'need_help' => 'Behöver du hjälp? Kontakta din hostingleverantör och be dem konfigurera en cronjob som körs varje minut med kommandot som visas ovan.',
];
