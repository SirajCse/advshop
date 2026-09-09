<?php

return [
    'name' => 'Cronjob',
    'description' => 'Állítson be automatizált háttérfeladatokat, hogy webhelye zökkenőmentesen működjön.',
    'is_not_ready' => 'A cronjob még nincs beállítva',
    'is_not_ready_description' => 'Kövesse az alábbi utasításokat a cronjob beállításához. Ez szükséges olyan funkciókhoz, mint az elhagyott kosár emlékeztetők, e-mail ütemezés és egyéb automatizált feladatok.',
    'is_working' => 'A cronjob megfelelően működik!',
    'is_not_working' => 'A cronjob leállt',
    'is_not_working_description' => 'A cronjob nem futott az elmúlt 10 percben. Ellenőrizze a szerver konfigurációját vagy lépjen kapcsolatba a tárhelyszolgáltatójával.',
    'last_checked' => 'Utolsó tevékenység: :time',
    'copy_button' => 'Parancs másolása',
    'what_is' => [
        'title' => 'Mi az a Cronjob?',
        'description' => 'A cronjob egy automatizált feladat, amely a szerver hátterében fut. Lehetővé teszi, hogy webhelye automatikusan végezzen fontos feladatokat anélkül, hogy bármit is manuálisan kellene tennie.',
        'examples' => 'Példák',
        'features' => 'Elhagyott kosár emlékeztetők küldése, ütemezett e-mailek feldolgozása, régi adatok törlése, jelentések generálása és még sok más.',
    ],
    'command' => [
        'title' => 'Az Ön Cronjob parancsa',
        'description' => 'Másolja ezt a parancsot és adja hozzá a tárhely vezérlőpultjához. Ennek a parancsnak percenként kell futnia, hogy az automatizált feladatok működjenek.',
    ],
    'setup' => [
        'name' => 'Hogyan állítsa be',
        'copied' => 'Vágólapra másolva!',
        'choose_hosting' => 'Válassza ki az alábbi tárhely vezérlőpultot és kövesse a lépésről lépésre szóló utasításokat:',
    ],
    'cpanel' => [
        'step1' => 'Jelentkezzen be a <strong>cPanel</strong> fiókjába',
        'step2' => 'Keresse meg és kattintson a <strong>"Cron Jobs"</strong> elemre az "Advanced" részben',
        'step3' => 'Az "Add New Cron Job" alatt válassza a <strong>"Once Per Minute (* * * * *)"</strong> opciót a legördülő menüből',
        'step4' => '<strong>Illessze be a parancsot</strong>, amit fent másolt, a "Command" mezőbe',
        'step5' => 'Kattintson az <strong>"Add New Cron Job"</strong> gombra a mentéshez',
    ],
    'plesk' => [
        'step1' => 'Jelentkezzen be a <strong>Plesk</strong> vezérlőpultba',
        'step2' => 'Lépjen a <strong>"Scheduled Tasks"</strong> (vagy "Cron Jobs") menüpontra',
        'step3' => 'Kattintson az <strong>"Add Task"</strong> vagy <strong>"Schedule a Task"</strong> gombra',
        'step4' => 'Állítsa be az ütemezést <strong>percenkénti</strong> futásra és illessze be a parancsot',
        'step5' => 'Kattintson az <strong>"OK"</strong> vagy <strong>"Apply"</strong> gombra a mentéshez',
    ],
    'directadmin' => [
        'step1' => 'Jelentkezzen be a <strong>DirectAdmin</strong> panelbe',
        'step2' => 'Navigáljon az <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong> menüpontra',
        'step3' => 'Kattintson az <strong>"Add Cron Job"</strong> gombra',
        'step4' => 'Állítsa az összes időmezőt <strong>*</strong>-ra (percenként) és illessze be a parancsot',
        'step5' => 'Kattintson az <strong>"Add"</strong> gombra a cronjob mentéséhez',
    ],
    'ssh' => [
        'step1' => 'Csatlakozzon a szerverhez <strong>SSH</strong>-n keresztül Terminal vagy PuTTY használatával',
        'step2' => 'Írja be a <code>crontab -e</code> parancsot és nyomjon Entert a crontab fájl szerkesztéséhez',
        'step3' => 'Adjon hozzá egy új sort alul és <strong>illessze be a parancsot</strong>',
        'step4' => 'Nyomja meg a <strong>Ctrl+X</strong>, majd <strong>Y</strong>, majd <strong>Enter</strong> billentyűket a mentéshez (nano szerkesztőhöz)',
        'step5' => 'A cronjob most aktív és percenként fog futni',
    ],
    'need_help' => 'Segítségre van szüksége? Lépjen kapcsolatba a tárhelyszolgáltatójával és kérje meg, hogy állítson be egy cronjobot, amely percenként fut a fent látható paranccsal.',
];
