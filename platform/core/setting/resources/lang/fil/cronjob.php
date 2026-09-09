<?php

return [
    'name' => 'Cronjob',
    'description' => 'Mag-set up ng mga automated na background task para patuloy na gumagana nang maayos ang iyong website.',
    'is_not_ready' => 'Hindi pa na-configure ang cronjob',
    'is_not_ready_description' => 'Pakisunod ang mga tagubilin sa ibaba para i-set up ang cronjob. Ito ay kinakailangan para sa mga feature tulad ng abandoned cart reminders, email scheduling, at iba pang automated na gawain.',
    'is_working' => 'Gumagana nang maayos ang cronjob!',
    'is_not_working' => 'Huminto sa paggana ang cronjob',
    'is_not_working_description' => 'Hindi tumakbo ang cronjob sa nakaraang 10 minuto. Pakitingnan ang configuration ng iyong server o makipag-ugnayan sa iyong hosting provider.',
    'last_checked' => 'Huling aktibidad: :time',
    'copy_button' => 'Kopyahin ang command',
    'what_is' => [
        'title' => 'Ano ang Cronjob?',
        'description' => 'Ang cronjob ay isang automated na gawain na tumatakbo sa background ng iyong server. Pinapayagan nito ang iyong website na awtomatikong magsagawa ng mahahalagang gawain nang hindi mo kailangang gumawa ng kahit ano nang mano-mano.',
        'examples' => 'Mga Halimbawa',
        'features' => 'Magpadala ng abandoned cart reminders, magproseso ng scheduled na email, maglinis ng lumang data, gumawa ng mga ulat, at marami pa.',
    ],
    'command' => [
        'title' => 'Ang Iyong Cronjob Command',
        'description' => 'Kopyahin ang command na ito at idagdag ito sa iyong hosting control panel. Kailangang tumakbo ang command na ito bawat minuto para gumana ang iyong mga automated na gawain.',
    ],
    'setup' => [
        'name' => 'Paano Mag-set Up',
        'copied' => 'Nakopya sa clipboard!',
        'choose_hosting' => 'Piliin ang iyong hosting control panel sa ibaba at sundin ang step-by-step na mga tagubilin:',
    ],
    'cpanel' => [
        'step1' => 'Mag-log in sa iyong <strong>cPanel</strong> account',
        'step2' => 'Hanapin at i-click ang <strong>"Cron Jobs"</strong> sa "Advanced" section',
        'step3' => 'Sa ilalim ng "Add New Cron Job", piliin ang <strong>"Once Per Minute (* * * * *)"</strong> mula sa timing dropdown',
        'step4' => '<strong>I-paste ang command</strong> na kinopya mo sa itaas sa "Command" field',
        'step5' => 'I-click ang <strong>"Add New Cron Job"</strong> para i-save',
    ],
    'plesk' => [
        'step1' => 'Mag-log in sa iyong <strong>Plesk</strong> control panel',
        'step2' => 'Pumunta sa <strong>"Scheduled Tasks"</strong> (o "Cron Jobs")',
        'step3' => 'I-click ang <strong>"Add Task"</strong> o <strong>"Schedule a Task"</strong>',
        'step4' => 'I-set ang schedule para tumakbo <strong>bawat minuto</strong> at i-paste ang command',
        'step5' => 'I-click ang <strong>"OK"</strong> o <strong>"Apply"</strong> para i-save',
    ],
    'directadmin' => [
        'step1' => 'Mag-log in sa iyong <strong>DirectAdmin</strong> panel',
        'step2' => 'Mag-navigate sa <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'I-click ang <strong>"Add Cron Job"</strong>',
        'step4' => 'I-set ang lahat ng time fields sa <strong>*</strong> (bawat minuto) at i-paste ang command',
        'step5' => 'I-click ang <strong>"Add"</strong> para i-save ang cronjob',
    ],
    'ssh' => [
        'step1' => 'Kumonekta sa iyong server sa pamamagitan ng <strong>SSH</strong> gamit ang Terminal o PuTTY',
        'step2' => 'I-type ang <code>crontab -e</code> at pindutin ang Enter para i-edit ang crontab file',
        'step3' => 'Magdagdag ng bagong linya sa ibaba at <strong>i-paste ang command</strong>',
        'step4' => 'Pindutin ang <strong>Ctrl+X</strong>, pagkatapos <strong>Y</strong>, pagkatapos <strong>Enter</strong> para i-save (para sa nano editor)',
        'step5' => 'Ang cronjob ay aktibo na at tatakbo bawat minuto',
    ],
    'need_help' => 'Kailangan ng tulong? Makipag-ugnayan sa iyong hosting provider at hilingin sa kanila na mag-set up ng cronjob na tumatakbo bawat minuto gamit ang command na ipinapakita sa itaas.',
];
