<?php

return [
    'name' => 'Cronjob',
    'description' => 'Nastavite avtomatizirane naloge v ozadju, da bo vaše spletno mesto delovalo gladko.',
    'is_not_ready' => 'Cronjob še ni nastavljen',
    'is_not_ready_description' => 'Prosimo, sledite spodnjim navodilom za nastavitev cronjoba. To je potrebno za funkcije, kot so opomniki za zapuščene košarice, razporejanje e-pošte in druge avtomatizirane naloge.',
    'is_working' => 'Cronjob deluje pravilno!',
    'is_not_working' => 'Cronjob je prenehal delovati',
    'is_not_working_description' => 'Cronjob se v zadnjih 10 minutah ni zagnal. Prosimo, preverite konfiguracijo strežnika ali se obrnite na svojega ponudnika gostovanja.',
    'last_checked' => 'Zadnja aktivnost: :time',
    'copy_button' => 'Kopiraj ukaz',
    'what_is' => [
        'title' => 'Kaj je Cronjob?',
        'description' => 'Cronjob je avtomatizirana naloga, ki se izvaja v ozadju na vašem strežniku. Omogoča vašemu spletnemu mestu, da samodejno izvaja pomembne naloge, ne da bi morali kar koli narediti ročno.',
        'examples' => 'Primeri',
        'features' => 'Pošiljanje opomnikov za zapuščene košarice, obdelava načrtovane e-pošte, čiščenje starih podatkov, ustvarjanje poročil in več.',
    ],
    'command' => [
        'title' => 'Vaš Cronjob ukaz',
        'description' => 'Kopirajte ta ukaz in ga dodajte v nadzorno ploščo gostovanja. Ta ukaz se mora izvajati vsako minuto, da bodo vaše avtomatizirane naloge delovale.',
    ],
    'setup' => [
        'name' => 'Kako nastaviti',
        'copied' => 'Kopirano v odložišče!',
        'choose_hosting' => 'Izberite svojo nadzorno ploščo gostovanja spodaj in sledite navodilom po korakih:',
    ],
    'cpanel' => [
        'step1' => 'Prijavite se v svoj račun <strong>cPanel</strong>',
        'step2' => 'Poiščite in kliknite na <strong>"Cron Jobs"</strong> v razdelku "Advanced"',
        'step3' => 'Pod "Add New Cron Job" izberite <strong>"Once Per Minute (* * * * *)"</strong> iz spustnega menija',
        'step4' => '<strong>Prilepite ukaz</strong>, ki ste ga kopirali zgoraj, v polje "Command"',
        'step5' => 'Kliknite <strong>"Add New Cron Job"</strong> za shranjevanje',
    ],
    'plesk' => [
        'step1' => 'Prijavite se v svojo nadzorno ploščo <strong>Plesk</strong>',
        'step2' => 'Pojdite na <strong>"Scheduled Tasks"</strong> (ali "Cron Jobs")',
        'step3' => 'Kliknite <strong>"Add Task"</strong> ali <strong>"Schedule a Task"</strong>',
        'step4' => 'Nastavite urnik za zagon <strong>vsako minuto</strong> in prilepite ukaz',
        'step5' => 'Kliknite <strong>"OK"</strong> ali <strong>"Apply"</strong> za shranjevanje',
    ],
    'directadmin' => [
        'step1' => 'Prijavite se v svojo ploščo <strong>DirectAdmin</strong>',
        'step2' => 'Pomaknite se na <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Kliknite <strong>"Add Cron Job"</strong>',
        'step4' => 'Nastavite vsa časovna polja na <strong>*</strong> (vsako minuto) in prilepite ukaz',
        'step5' => 'Kliknite <strong>"Add"</strong> za shranjevanje cronjoba',
    ],
    'ssh' => [
        'step1' => 'Povežite se s svojim strežnikom prek <strong>SSH</strong> z uporabo Terminal ali PuTTY',
        'step2' => 'Vnesite <code>crontab -e</code> in pritisnite Enter za urejanje datoteke crontab',
        'step3' => 'Dodajte novo vrstico na dnu in <strong>prilepite ukaz</strong>',
        'step4' => 'Pritisnite <strong>Ctrl+X</strong>, nato <strong>Y</strong>, nato <strong>Enter</strong> za shranjevanje (za nano urejevalnik)',
        'step5' => 'Cronjob je zdaj aktiven in se bo zaganjal vsako minuto',
    ],
    'need_help' => 'Potrebujete pomoč? Obrnite se na svojega ponudnika gostovanja in ga prosite, da nastavi cronjob, ki se izvaja vsako minuto z ukazom, prikazanim zgoraj.',
];
