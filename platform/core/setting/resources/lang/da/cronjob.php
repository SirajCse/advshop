<?php

return [
    'name' => 'Cronjob',
    'description' => 'Opsæt automatiserede baggrundsopgaver for at holde dit websted kørende problemfrit.',
    'is_not_ready' => 'Cronjob er ikke konfigureret endnu',
    'is_not_ready_description' => 'Følg instruktionerne nedenfor for at opsætte cronjob. Dette er påkrævet for funktioner som påmindelser om forladte indkøbskurve, e-mail-planlægning og andre automatiserede opgaver.',
    'is_working' => 'Cronjob kører korrekt!',
    'is_not_working' => 'Cronjob er stoppet med at virke',
    'is_not_working_description' => 'Cronjob har ikke kørt i de sidste 10 minutter. Tjek din serverkonfiguration eller kontakt din hostingudbyder.',
    'last_checked' => 'Seneste aktivitet: :time',
    'copy_button' => 'Kopier kommando',
    'what_is' => [
        'title' => 'Hvad er en Cronjob?',
        'description' => 'En cronjob er en automatiseret opgave, der kører i baggrunden på din server. Den lader dit websted udføre vigtige opgaver automatisk uden at du skal gøre noget manuelt.',
        'examples' => 'Eksempler',
        'features' => 'Send påmindelser om forladte indkøbskurve, behandl planlagte e-mails, ryd op i gamle data, generer rapporter og mere.',
    ],
    'command' => [
        'title' => 'Din Cronjob-kommando',
        'description' => 'Kopier denne kommando og tilføj den til dit hosting kontrolpanel. Denne kommando skal køres hvert minut for at dine automatiserede opgaver fungerer.',
    ],
    'setup' => [
        'name' => 'Sådan opsættes',
        'copied' => 'Kopieret til udklipsholder!',
        'choose_hosting' => 'Vælg dit hosting kontrolpanel nedenfor og følg trin-for-trin instruktionerne:',
    ],
    'cpanel' => [
        'step1' => 'Log ind på din <strong>cPanel</strong>-konto',
        'step2' => 'Find og klik på <strong>"Cron Jobs"</strong> under "Advanced" sektionen',
        'step3' => 'Under "Add New Cron Job", vælg <strong>"Once Per Minute (* * * * *)"</strong> fra tids-dropdown',
        'step4' => '<strong>Indsæt kommandoen</strong> du kopierede ovenfor i "Command" feltet',
        'step5' => 'Klik på <strong>"Add New Cron Job"</strong> for at gemme',
    ],
    'plesk' => [
        'step1' => 'Log ind på dit <strong>Plesk</strong> kontrolpanel',
        'step2' => 'Gå til <strong>"Scheduled Tasks"</strong> (eller "Cron Jobs")',
        'step3' => 'Klik på <strong>"Add Task"</strong> eller <strong>"Schedule a Task"</strong>',
        'step4' => 'Indstil tidsplanen til at køre <strong>hvert minut</strong> og indsæt kommandoen',
        'step5' => 'Klik på <strong>"OK"</strong> eller <strong>"Apply"</strong> for at gemme',
    ],
    'directadmin' => [
        'step1' => 'Log ind på dit <strong>DirectAdmin</strong> panel',
        'step2' => 'Naviger til <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klik på <strong>"Add Cron Job"</strong>',
        'step4' => 'Indstil alle tidsfelter til <strong>*</strong> (hvert minut) og indsæt kommandoen',
        'step5' => 'Klik på <strong>"Add"</strong> for at gemme cronjob',
    ],
    'ssh' => [
        'step1' => 'Opret forbindelse til din server via <strong>SSH</strong> ved hjælp af Terminal eller PuTTY',
        'step2' => 'Skriv <code>crontab -e</code> og tryk Enter for at redigere crontab-filen',
        'step3' => 'Tilføj en ny linje i bunden og <strong>indsæt kommandoen</strong>',
        'step4' => 'Tryk <strong>Ctrl+X</strong>, derefter <strong>Y</strong>, derefter <strong>Enter</strong> for at gemme (for nano editor)',
        'step5' => 'Cronjob er nu aktiv og vil køre hvert minut',
    ],
    'need_help' => 'Brug for hjælp? Kontakt din hostingudbyder og bed dem opsætte en cronjob, der kører hvert minut med kommandoen vist ovenfor.',
];
