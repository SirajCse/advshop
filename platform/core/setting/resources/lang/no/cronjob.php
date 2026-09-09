<?php

return [
    'name' => 'Cronjob',
    'description' => 'Sett opp automatiserte bakgrunnsoppgaver for å holde nettstedet ditt i gang uten problemer.',
    'is_not_ready' => 'Cronjob er ikke konfigurert ennå',
    'is_not_ready_description' => 'Følg instruksjonene nedenfor for å sette opp cronjob. Dette er nødvendig for funksjoner som påminnelser om forlatte handlekurver, e-postplanlegging og andre automatiserte oppgaver.',
    'is_working' => 'Cronjob kjører riktig!',
    'is_not_working' => 'Cronjob har sluttet å virke',
    'is_not_working_description' => 'Cronjob har ikke kjørt de siste 10 minuttene. Sjekk serverkonfigurasjonen din eller kontakt hostingleverandøren.',
    'last_checked' => 'Siste aktivitet: :time',
    'copy_button' => 'Kopier kommando',
    'what_is' => [
        'title' => 'Hva er en Cronjob?',
        'description' => 'En cronjob er en automatisert oppgave som kjører i bakgrunnen på serveren din. Den lar nettstedet ditt utføre viktige oppgaver automatisk uten at du trenger å gjøre noe manuelt.',
        'examples' => 'Eksempler',
        'features' => 'Sende påminnelser om forlatte handlekurver, behandle planlagte e-poster, rydde opp gamle data, generere rapporter og mer.',
    ],
    'command' => [
        'title' => 'Din Cronjob-kommando',
        'description' => 'Kopier denne kommandoen og legg den til i hostingkontrollpanelet ditt. Denne kommandoen må kjøres hvert minutt for at de automatiserte oppgavene dine skal fungere.',
    ],
    'setup' => [
        'name' => 'Hvordan sette opp',
        'copied' => 'Kopiert til utklippstavlen!',
        'choose_hosting' => 'Velg hostingkontrollpanelet ditt nedenfor og følg trinn-for-trinn-instruksjonene:',
    ],
    'cpanel' => [
        'step1' => 'Logg inn på <strong>cPanel</strong>-kontoen din',
        'step2' => 'Finn og klikk på <strong>"Cron Jobs"</strong> under "Advanced"-seksjonen',
        'step3' => 'Under "Add New Cron Job", velg <strong>"Once Per Minute (* * * * *)"</strong> fra tidsrullegardinmenyen',
        'step4' => '<strong>Lim inn kommandoen</strong> du kopierte ovenfor i "Command"-feltet',
        'step5' => 'Klikk på <strong>"Add New Cron Job"</strong> for å lagre',
    ],
    'plesk' => [
        'step1' => 'Logg inn på <strong>Plesk</strong>-kontrollpanelet ditt',
        'step2' => 'Gå til <strong>"Scheduled Tasks"</strong> (eller "Cron Jobs")',
        'step3' => 'Klikk på <strong>"Add Task"</strong> eller <strong>"Schedule a Task"</strong>',
        'step4' => 'Sett tidsplanen til å kjøre <strong>hvert minutt</strong> og lim inn kommandoen',
        'step5' => 'Klikk på <strong>"OK"</strong> eller <strong>"Apply"</strong> for å lagre',
    ],
    'directadmin' => [
        'step1' => 'Logg inn på <strong>DirectAdmin</strong>-panelet ditt',
        'step2' => 'Naviger til <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klikk på <strong>"Add Cron Job"</strong>',
        'step4' => 'Sett alle tidsfelt til <strong>*</strong> (hvert minutt) og lim inn kommandoen',
        'step5' => 'Klikk på <strong>"Add"</strong> for å lagre cronjob',
    ],
    'ssh' => [
        'step1' => 'Koble til serveren din via <strong>SSH</strong> ved hjelp av Terminal eller PuTTY',
        'step2' => 'Skriv <code>crontab -e</code> og trykk Enter for å redigere crontab-filen',
        'step3' => 'Legg til en ny linje nederst og <strong>lim inn kommandoen</strong>',
        'step4' => 'Trykk <strong>Ctrl+X</strong>, deretter <strong>Y</strong>, deretter <strong>Enter</strong> for å lagre (for nano-editor)',
        'step5' => 'Cronjob er nå aktiv og vil kjøre hvert minutt',
    ],
    'need_help' => 'Trenger du hjelp? Kontakt hostingleverandøren din og be dem sette opp en cronjob som kjører hvert minutt med kommandoen vist ovenfor.',
];
