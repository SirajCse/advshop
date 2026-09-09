<?php

return [
    'name' => 'Cronjob',
    'description' => 'Nastavte automatizované úlohy na pozadí, aby vaša webová stránka fungovala hladko.',
    'is_not_ready' => 'Cronjob ešte nie je nakonfigurovaný',
    'is_not_ready_description' => 'Postupujte podľa pokynov nižšie na nastavenie cronjobu. Toto je potrebné pre funkcie ako upomienky na opustené košíky, plánovanie e-mailov a ďalšie automatizované úlohy.',
    'is_working' => 'Cronjob funguje správne!',
    'is_not_working' => 'Cronjob prestal fungovať',
    'is_not_working_description' => 'Cronjob nebol spustený posledných 10 minút. Skontrolujte konfiguráciu servera alebo kontaktujte poskytovateľa hostingu.',
    'last_checked' => 'Posledná aktivita: :time',
    'copy_button' => 'Kopírovať príkaz',
    'what_is' => [
        'title' => 'Čo je Cronjob?',
        'description' => 'Cronjob je automatizovaná úloha, ktorá beží na pozadí vášho servera. Umožňuje vašej webovej stránke automaticky vykonávať dôležité úlohy bez toho, aby ste museli čokoľvek robiť ručne.',
        'examples' => 'Príklady',
        'features' => 'Odosielanie upomienok na opustené košíky, spracovanie naplánovaných e-mailov, čistenie starých dát, generovanie reportov a ďalšie.',
    ],
    'command' => [
        'title' => 'Váš Cronjob príkaz',
        'description' => 'Skopírujte tento príkaz a pridajte ho do ovládacieho panela hostingu. Tento príkaz musí bežať každú minútu, aby vaše automatizované úlohy fungovali.',
    ],
    'setup' => [
        'name' => 'Ako nastaviť',
        'copied' => 'Skopírované do schránky!',
        'choose_hosting' => 'Vyberte nižšie svoj ovládací panel hostingu a postupujte podľa pokynov krok za krokom:',
    ],
    'cpanel' => [
        'step1' => 'Prihláste sa do svojho účtu <strong>cPanel</strong>',
        'step2' => 'Nájdite a kliknite na <strong>"Cron Jobs"</strong> v sekcii "Advanced"',
        'step3' => 'Pod "Add New Cron Job" vyberte <strong>"Once Per Minute (* * * * *)"</strong> z rozbaľovacej ponuky',
        'step4' => '<strong>Vložte príkaz</strong>, ktorý ste skopírovali vyššie, do poľa "Command"',
        'step5' => 'Kliknite na <strong>"Add New Cron Job"</strong> pre uloženie',
    ],
    'plesk' => [
        'step1' => 'Prihláste sa do ovládacieho panela <strong>Plesk</strong>',
        'step2' => 'Prejdite na <strong>"Scheduled Tasks"</strong> (alebo "Cron Jobs")',
        'step3' => 'Kliknite na <strong>"Add Task"</strong> alebo <strong>"Schedule a Task"</strong>',
        'step4' => 'Nastavte plán na spustenie <strong>každú minútu</strong> a vložte príkaz',
        'step5' => 'Kliknite na <strong>"OK"</strong> alebo <strong>"Apply"</strong> pre uloženie',
    ],
    'directadmin' => [
        'step1' => 'Prihláste sa do panela <strong>DirectAdmin</strong>',
        'step2' => 'Prejdite na <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Kliknite na <strong>"Add Cron Job"</strong>',
        'step4' => 'Nastavte všetky časové polia na <strong>*</strong> (každú minútu) a vložte príkaz',
        'step5' => 'Kliknite na <strong>"Add"</strong> pre uloženie cronjobu',
    ],
    'ssh' => [
        'step1' => 'Pripojte sa k serveru cez <strong>SSH</strong> pomocou Terminálu alebo PuTTY',
        'step2' => 'Napíšte <code>crontab -e</code> a stlačte Enter pre úpravu súboru crontab',
        'step3' => 'Pridajte nový riadok dole a <strong>vložte príkaz</strong>',
        'step4' => 'Stlačte <strong>Ctrl+X</strong>, potom <strong>Y</strong>, potom <strong>Enter</strong> pre uloženie (pre nano editor)',
        'step5' => 'Cronjob je teraz aktívny a bude sa spúšťať každú minútu',
    ],
    'need_help' => 'Potrebujete pomoc? Kontaktujte poskytovateľa hostingu a požiadajte ho o nastavenie cronjobu, ktorý sa spúšťa každú minútu s príkazom zobrazeným vyššie.',
];
