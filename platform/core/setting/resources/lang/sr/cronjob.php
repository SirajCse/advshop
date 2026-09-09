<?php

return [
    'name' => 'Cronjob',
    'description' => 'Подесите аутоматизоване позадинске задатке како би ваш сајт радио глатко.',
    'is_not_ready' => 'Cronjob још није конфигурисан',
    'is_not_ready_description' => 'Молимо пратите упутства испод за подешавање cronjob-а. Ово је потребно за функције као што су подсетници за напуштене корпе, заказивање е-поште и други аутоматизовани задаци.',
    'is_working' => 'Cronjob ради исправно!',
    'is_not_working' => 'Cronjob је престао да ради',
    'is_not_working_description' => 'Cronjob није покренут у последњих 10 минута. Молимо проверите конфигурацију сервера или контактирајте свог хостинг провајдера.',
    'last_checked' => 'Последња активност: :time',
    'copy_button' => 'Копирај команду',
    'what_is' => [
        'title' => 'Шта је Cronjob?',
        'description' => 'Cronjob је аутоматизовани задатак који се извршава у позадини на вашем серверу. Омогућава вашем сајту да аутоматски извршава важне задатке без потребе да било шта радите ручно.',
        'examples' => 'Примери',
        'features' => 'Слање подсетника за напуштене корпе, обрада заказане е-поште, чишћење старих података, генерисање извештаја и још много тога.',
    ],
    'command' => [
        'title' => 'Ваша Cronjob команда',
        'description' => 'Копирајте ову команду и додајте је у контролну таблу хостинга. Ова команда мора да се покреће сваког минута да би ваши аутоматизовани задаци радили.',
    ],
    'setup' => [
        'name' => 'Како подесити',
        'copied' => 'Копирано у клипборд!',
        'choose_hosting' => 'Изаберите своју контролну таблу хостинга испод и пратите упутства корак по корак:',
    ],
    'cpanel' => [
        'step1' => 'Пријавите се на свој <strong>cPanel</strong> налог',
        'step2' => 'Пронађите и кликните на <strong>"Cron Jobs"</strong> у одељку "Advanced"',
        'step3' => 'Под "Add New Cron Job", изаберите <strong>"Once Per Minute (* * * * *)"</strong> из падајућег менија',
        'step4' => '<strong>Налепите команду</strong> коју сте копирали горе у поље "Command"',
        'step5' => 'Кликните <strong>"Add New Cron Job"</strong> за чување',
    ],
    'plesk' => [
        'step1' => 'Пријавите се на своју <strong>Plesk</strong> контролну таблу',
        'step2' => 'Идите на <strong>"Scheduled Tasks"</strong> (или "Cron Jobs")',
        'step3' => 'Кликните <strong>"Add Task"</strong> или <strong>"Schedule a Task"</strong>',
        'step4' => 'Подесите распоред да се покреће <strong>сваког минута</strong> и налепите команду',
        'step5' => 'Кликните <strong>"OK"</strong> или <strong>"Apply"</strong> за чување',
    ],
    'directadmin' => [
        'step1' => 'Пријавите се на свој <strong>DirectAdmin</strong> панел',
        'step2' => 'Навигирајте до <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Кликните <strong>"Add Cron Job"</strong>',
        'step4' => 'Подесите сва временска поља на <strong>*</strong> (сваког минута) и налепите команду',
        'step5' => 'Кликните <strong>"Add"</strong> за чување cronjob-а',
    ],
    'ssh' => [
        'step1' => 'Повежите се са својим сервером преко <strong>SSH</strong> користећи Terminal или PuTTY',
        'step2' => 'Укуцајте <code>crontab -e</code> и притисните Enter за уређивање crontab фајла',
        'step3' => 'Додајте нови ред на дну и <strong>налепите команду</strong>',
        'step4' => 'Притисните <strong>Ctrl+X</strong>, затим <strong>Y</strong>, затим <strong>Enter</strong> за чување (за nano едитор)',
        'step5' => 'Cronjob је сада активан и покретаће се сваког минута',
    ],
    'need_help' => 'Потребна вам је помоћ? Контактирајте свог хостинг провајдера и замолите их да подесе cronjob који се покреће сваког минута са командом приказаном горе.',
];
