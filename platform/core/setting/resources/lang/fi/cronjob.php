<?php

return [
    'name' => 'Cronjob',
    'description' => 'Määritä automaattiset taustatehtävät pitääksesi verkkosivustosi toiminnassa sujuvasti.',
    'is_not_ready' => 'Cronjobia ei ole vielä määritetty',
    'is_not_ready_description' => 'Noudata alla olevia ohjeita cronjobin määrittämiseksi. Tämä vaaditaan ominaisuuksille kuten hylättyjen ostoskorien muistutukset, sähköpostin ajoitus ja muut automaattiset tehtävät.',
    'is_working' => 'Cronjob toimii oikein!',
    'is_not_working' => 'Cronjob on lakannut toimimasta',
    'is_not_working_description' => 'Cronjob ei ole suoritettu viimeisen 10 minuutin aikana. Tarkista palvelimen asetukset tai ota yhteyttä webhotellisi tarjoajaan.',
    'last_checked' => 'Viimeinen toiminta: :time',
    'copy_button' => 'Kopioi komento',
    'what_is' => [
        'title' => 'Mikä on Cronjob?',
        'description' => 'Cronjob on automaattinen tehtävä, joka suoritetaan taustalla palvelimellasi. Se mahdollistaa verkkosivustosi suorittaa tärkeitä tehtäviä automaattisesti ilman, että sinun tarvitsee tehdä mitään manuaalisesti.',
        'examples' => 'Esimerkkejä',
        'features' => 'Lähetä hylättyjen ostoskorien muistutuksia, käsittele ajoitettuja sähköposteja, puhdista vanhoja tietoja, luo raportteja ja paljon muuta.',
    ],
    'command' => [
        'title' => 'Cronjob-komentosi',
        'description' => 'Kopioi tämä komento ja lisää se webhotellin hallintapaneeliin. Tämä komento täytyy suorittaa joka minuutti, jotta automaattiset tehtäväsi toimivat.',
    ],
    'setup' => [
        'name' => 'Kuinka määrittää',
        'copied' => 'Kopioitu leikepöydälle!',
        'choose_hosting' => 'Valitse webhotellin hallintapaneeli alta ja seuraa vaiheittaisia ohjeita:',
    ],
    'cpanel' => [
        'step1' => 'Kirjaudu <strong>cPanel</strong>-tilillesi',
        'step2' => 'Etsi ja klikkaa <strong>"Cron Jobs"</strong> "Advanced"-osiossa',
        'step3' => '"Add New Cron Job" -kohdassa valitse <strong>"Once Per Minute (* * * * *)"</strong> aika-pudotusvalikosta',
        'step4' => '<strong>Liitä komento</strong> jonka kopioit yllä "Command"-kenttään',
        'step5' => 'Klikkaa <strong>"Add New Cron Job"</strong> tallentaaksesi',
    ],
    'plesk' => [
        'step1' => 'Kirjaudu <strong>Plesk</strong>-hallintapaneeliin',
        'step2' => 'Mene kohtaan <strong>"Scheduled Tasks"</strong> (tai "Cron Jobs")',
        'step3' => 'Klikkaa <strong>"Add Task"</strong> tai <strong>"Schedule a Task"</strong>',
        'step4' => 'Aseta aikataulu suoritettavaksi <strong>joka minuutti</strong> ja liitä komento',
        'step5' => 'Klikkaa <strong>"OK"</strong> tai <strong>"Apply"</strong> tallentaaksesi',
    ],
    'directadmin' => [
        'step1' => 'Kirjaudu <strong>DirectAdmin</strong>-paneeliin',
        'step2' => 'Siirry kohtaan <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klikkaa <strong>"Add Cron Job"</strong>',
        'step4' => 'Aseta kaikki aikakentät arvoon <strong>*</strong> (joka minuutti) ja liitä komento',
        'step5' => 'Klikkaa <strong>"Add"</strong> tallentaaksesi cronjob',
    ],
    'ssh' => [
        'step1' => 'Yhdistä palvelimeesi <strong>SSH</strong>:n kautta käyttäen Terminalia tai PuTTYa',
        'step2' => 'Kirjoita <code>crontab -e</code> ja paina Enter muokataksesi crontab-tiedostoa',
        'step3' => 'Lisää uusi rivi alareunaan ja <strong>liitä komento</strong>',
        'step4' => 'Paina <strong>Ctrl+X</strong>, sitten <strong>Y</strong>, sitten <strong>Enter</strong> tallentaaksesi (nano-editorille)',
        'step5' => 'Cronjob on nyt aktiivinen ja suoritetaan joka minuutti',
    ],
    'need_help' => 'Tarvitsetko apua? Ota yhteyttä webhotellisi tarjoajaan ja pyydä heitä määrittämään cronjob, joka suoritetaan joka minuutti yllä näytetyllä komennolla.',
];
