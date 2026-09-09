<?php

return [
    'name' => 'Cronjob',
    'description' => 'Postavite automatizirane pozadinske zadatke kako bi vaša web stranica radila glatko.',
    'is_not_ready' => 'Cronjob još nije konfiguriran',
    'is_not_ready_description' => 'Molimo slijedite upute u nastavku za postavljanje cronjoba. Ovo je potrebno za značajke poput podsjetnika za napuštene košarice, zakazivanje e-pošte i druge automatizirane zadatke.',
    'is_working' => 'Cronjob radi ispravno!',
    'is_not_working' => 'Cronjob je prestao raditi',
    'is_not_working_description' => 'Cronjob nije pokrenut u posljednjih 10 minuta. Molimo provjerite konfiguraciju poslužitelja ili kontaktirajte svog pružatelja hostinga.',
    'last_checked' => 'Posljednja aktivnost: :time',
    'copy_button' => 'Kopiraj naredbu',
    'what_is' => [
        'title' => 'Što je Cronjob?',
        'description' => 'Cronjob je automatizirani zadatak koji se izvršava u pozadini na vašem poslužitelju. Omogućuje vašoj web stranici automatsko izvršavanje važnih zadataka bez potrebe za ručnim djelovanjem.',
        'examples' => 'Primjeri',
        'features' => 'Slanje podsjetnika za napuštene košarice, obrada zakazane e-pošte, čišćenje starih podataka, generiranje izvještaja i više.',
    ],
    'command' => [
        'title' => 'Vaša Cronjob naredba',
        'description' => 'Kopirajte ovu naredbu i dodajte je u upravljačku ploču hostinga. Ova naredba mora se pokretati svake minute kako bi vaši automatizirani zadaci radili.',
    ],
    'setup' => [
        'name' => 'Kako postaviti',
        'copied' => 'Kopirano u međuspremnik!',
        'choose_hosting' => 'Odaberite svoju upravljačku ploču hostinga ispod i slijedite upute korak po korak:',
    ],
    'cpanel' => [
        'step1' => 'Prijavite se na svoj <strong>cPanel</strong> račun',
        'step2' => 'Pronađite i kliknite na <strong>"Cron Jobs"</strong> u odjeljku "Advanced"',
        'step3' => 'Pod "Add New Cron Job", odaberite <strong>"Once Per Minute (* * * * *)"</strong> iz padajućeg izbornika',
        'step4' => '<strong>Zalijepite naredbu</strong> koju ste kopirali gore u polje "Command"',
        'step5' => 'Kliknite <strong>"Add New Cron Job"</strong> za spremanje',
    ],
    'plesk' => [
        'step1' => 'Prijavite se na svoju <strong>Plesk</strong> upravljačku ploču',
        'step2' => 'Idite na <strong>"Scheduled Tasks"</strong> (ili "Cron Jobs")',
        'step3' => 'Kliknite <strong>"Add Task"</strong> ili <strong>"Schedule a Task"</strong>',
        'step4' => 'Postavite raspored da se pokreće <strong>svake minute</strong> i zalijepite naredbu',
        'step5' => 'Kliknite <strong>"OK"</strong> ili <strong>"Apply"</strong> za spremanje',
    ],
    'directadmin' => [
        'step1' => 'Prijavite se na svoj <strong>DirectAdmin</strong> panel',
        'step2' => 'Navigirajte do <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Kliknite <strong>"Add Cron Job"</strong>',
        'step4' => 'Postavite sva vremenska polja na <strong>*</strong> (svake minute) i zalijepite naredbu',
        'step5' => 'Kliknite <strong>"Add"</strong> za spremanje cronjoba',
    ],
    'ssh' => [
        'step1' => 'Povežite se sa svojim poslužiteljem putem <strong>SSH</strong> koristeći Terminal ili PuTTY',
        'step2' => 'Upišite <code>crontab -e</code> i pritisnite Enter za uređivanje crontab datoteke',
        'step3' => 'Dodajte novi redak na dnu i <strong>zalijepite naredbu</strong>',
        'step4' => 'Pritisnite <strong>Ctrl+X</strong>, zatim <strong>Y</strong>, zatim <strong>Enter</strong> za spremanje (za nano editor)',
        'step5' => 'Cronjob je sada aktivan i pokretat će se svake minute',
    ],
    'need_help' => 'Trebate pomoć? Kontaktirajte svog pružatelja hostinga i zamolite ga da postavi cronjob koji se pokreće svake minute s naredbom prikazanom gore.',
];
