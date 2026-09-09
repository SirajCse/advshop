<?php

return [
    'name' => 'Cronjob',
    'description' => 'Seadistage automatiseeritud taustaülesanded, et teie veebisait töötaks sujuvalt.',
    'is_not_ready' => 'Cronjob pole veel seadistatud',
    'is_not_ready_description' => 'Järgige allolevaid juhiseid cronjobi seadistamiseks. See on vajalik funktsioonide jaoks nagu mahajäetud ostukorvi meeldetuletused, e-posti ajastamine ja muud automatiseeritud ülesanded.',
    'is_working' => 'Cronjob töötab korrektselt!',
    'is_not_working' => 'Cronjob on lakanud töötamast',
    'is_not_working_description' => 'Cronjob pole viimase 10 minuti jooksul käivitunud. Kontrollige oma serveri seadistust või võtke ühendust oma hostingu pakkujaga.',
    'last_checked' => 'Viimane tegevus: :time',
    'copy_button' => 'Kopeeri käsk',
    'what_is' => [
        'title' => 'Mis on Cronjob?',
        'description' => 'Cronjob on automatiseeritud ülesanne, mis töötab teie serveri taustal. See võimaldab teie veebisaidil täita olulisi ülesandeid automaatselt, ilma et peaksite midagi käsitsi tegema.',
        'examples' => 'Näited',
        'features' => 'Mahajäetud ostukorvi meeldetuletuste saatmine, ajastatud e-kirjade töötlemine, vanade andmete puhastamine, aruannete genereerimine ja palju muud.',
    ],
    'command' => [
        'title' => 'Teie Cronjob käsk',
        'description' => 'Kopeerige see käsk ja lisage see oma hostingu juhtpaneelile. See käsk peab käivituma iga minut, et teie automatiseeritud ülesanded töötaksid.',
    ],
    'setup' => [
        'name' => 'Kuidas seadistada',
        'copied' => 'Kopeeritud lõikelauale!',
        'choose_hosting' => 'Valige allpool oma hostingu juhtpaneel ja järgige samm-sammult juhiseid:',
    ],
    'cpanel' => [
        'step1' => 'Logige oma <strong>cPanel</strong> kontole sisse',
        'step2' => 'Leidke ja klõpsake <strong>"Cron Jobs"</strong> jaotises "Advanced"',
        'step3' => 'Jaotises "Add New Cron Job" valige <strong>"Once Per Minute (* * * * *)"</strong> rippmenüüst',
        'step4' => '<strong>Kleepige käsk</strong>, mille ülal kopeerisite, väljale "Command"',
        'step5' => 'Klõpsake <strong>"Add New Cron Job"</strong> salvestamiseks',
    ],
    'plesk' => [
        'step1' => 'Logige oma <strong>Plesk</strong> juhtpaneelile sisse',
        'step2' => 'Minge jaotisesse <strong>"Scheduled Tasks"</strong> (või "Cron Jobs")',
        'step3' => 'Klõpsake <strong>"Add Task"</strong> või <strong>"Schedule a Task"</strong>',
        'step4' => 'Seadistage ajakava käivituma <strong>iga minut</strong> ja kleepige käsk',
        'step5' => 'Klõpsake <strong>"OK"</strong> või <strong>"Apply"</strong> salvestamiseks',
    ],
    'directadmin' => [
        'step1' => 'Logige oma <strong>DirectAdmin</strong> paneelile sisse',
        'step2' => 'Navigeerige jaotisesse <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klõpsake <strong>"Add Cron Job"</strong>',
        'step4' => 'Seadistage kõik ajaväljad väärtusele <strong>*</strong> (iga minut) ja kleepige käsk',
        'step5' => 'Klõpsake <strong>"Add"</strong> cronjobi salvestamiseks',
    ],
    'ssh' => [
        'step1' => 'Ühenduge oma serveriga <strong>SSH</strong> kaudu, kasutades Terminal\'i või PuTTY-d',
        'step2' => 'Sisestage <code>crontab -e</code> ja vajutage Enter crontab faili redigeerimiseks',
        'step3' => 'Lisage alla uus rida ja <strong>kleepige käsk</strong>',
        'step4' => 'Vajutage <strong>Ctrl+X</strong>, seejärel <strong>Y</strong>, seejärel <strong>Enter</strong> salvestamiseks (nano redaktori jaoks)',
        'step5' => 'Cronjob on nüüd aktiivne ja käivitub iga minut',
    ],
    'need_help' => 'Vajate abi? Võtke ühendust oma hostingu pakkujaga ja paluge neil seadistada cronjob, mis käivitub iga minut ülal näidatud käsuga.',
];
