<?php

return [
    'name' => 'Cronjob',
    'description' => 'Nastavte automatizované úlohy na pozadí, aby váš web fungoval hladce.',
    'is_not_ready' => 'Cronjob ještě není nakonfigurován',
    'is_not_ready_description' => 'Postupujte podle pokynů níže pro nastavení cronjobu. Toto je vyžadováno pro funkce jako upomínky na opuštěné košíky, plánování e-mailů a další automatizované úlohy.',
    'is_working' => 'Cronjob funguje správně!',
    'is_not_working' => 'Cronjob přestal fungovat',
    'is_not_working_description' => 'Cronjob nebyl spuštěn posledních 10 minut. Zkontrolujte konfiguraci serveru nebo kontaktujte poskytovatele hostingu.',
    'last_checked' => 'Poslední aktivita: :time',
    'copy_button' => 'Kopírovat příkaz',
    'what_is' => [
        'title' => 'Co je Cronjob?',
        'description' => 'Cronjob je automatizovaná úloha, která běží na pozadí vašeho serveru. Umožňuje vašemu webu automaticky provádět důležité úlohy, aniž byste museli cokoli dělat ručně.',
        'examples' => 'Příklady',
        'features' => 'Odesílání upomínek na opuštěné košíky, zpracování naplánovaných e-mailů, čištění starých dat, generování reportů a další.',
    ],
    'command' => [
        'title' => 'Váš Cronjob příkaz',
        'description' => 'Zkopírujte tento příkaz a přidejte ho do ovládacího panelu hostingu. Tento příkaz musí běžet každou minutu, aby vaše automatizované úlohy fungovaly.',
    ],
    'setup' => [
        'name' => 'Jak nastavit',
        'copied' => 'Zkopírováno do schránky!',
        'choose_hosting' => 'Vyberte níže svůj ovládací panel hostingu a postupujte podle pokynů krok za krokem:',
    ],
    'cpanel' => [
        'step1' => 'Přihlaste se ke svému účtu <strong>cPanel</strong>',
        'step2' => 'Najděte a klikněte na <strong>"Cron Jobs"</strong> v sekci "Advanced"',
        'step3' => 'Pod "Add New Cron Job" vyberte <strong>"Once Per Minute (* * * * *)"</strong> z rozbalovací nabídky',
        'step4' => '<strong>Vložte příkaz</strong>, který jste zkopírovali výše, do pole "Command"',
        'step5' => 'Klikněte na <strong>"Add New Cron Job"</strong> pro uložení',
    ],
    'plesk' => [
        'step1' => 'Přihlaste se do ovládacího panelu <strong>Plesk</strong>',
        'step2' => 'Přejděte na <strong>"Scheduled Tasks"</strong> (nebo "Cron Jobs")',
        'step3' => 'Klikněte na <strong>"Add Task"</strong> nebo <strong>"Schedule a Task"</strong>',
        'step4' => 'Nastavte plán na spuštění <strong>každou minutu</strong> a vložte příkaz',
        'step5' => 'Klikněte na <strong>"OK"</strong> nebo <strong>"Apply"</strong> pro uložení',
    ],
    'directadmin' => [
        'step1' => 'Přihlaste se do panelu <strong>DirectAdmin</strong>',
        'step2' => 'Přejděte na <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klikněte na <strong>"Add Cron Job"</strong>',
        'step4' => 'Nastavte všechna časová pole na <strong>*</strong> (každou minutu) a vložte příkaz',
        'step5' => 'Klikněte na <strong>"Add"</strong> pro uložení cronjobu',
    ],
    'ssh' => [
        'step1' => 'Připojte se k serveru přes <strong>SSH</strong> pomocí Terminálu nebo PuTTY',
        'step2' => 'Napište <code>crontab -e</code> a stiskněte Enter pro úpravu souboru crontab',
        'step3' => 'Přidejte nový řádek dole a <strong>vložte příkaz</strong>',
        'step4' => 'Stiskněte <strong>Ctrl+X</strong>, poté <strong>Y</strong>, poté <strong>Enter</strong> pro uložení (pro nano editor)',
        'step5' => 'Cronjob je nyní aktivní a bude se spouštět každou minutu',
    ],
    'need_help' => 'Potřebujete pomoc? Kontaktujte poskytovatele hostingu a požádejte ho o nastavení cronjobu, který se spouští každou minutu s příkazem zobrazeným výše.',
];
