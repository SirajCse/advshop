<?php

return [
    'name' => 'Cronjob',
    'description' => 'Iestatiet automatizētus fona uzdevumus, lai jūsu vietne darbotos vienmērīgi.',
    'is_not_ready' => 'Cronjob vēl nav konfigurēts',
    'is_not_ready_description' => 'Lūdzu, sekojiet zemāk esošajām instrukcijām, lai iestatītu cronjob. Tas ir nepieciešams funkcijām, piemēram, pamesta groza atgādinājumiem, e-pasta plānošanai un citiem automatizētiem uzdevumiem.',
    'is_working' => 'Cronjob darbojas pareizi!',
    'is_not_working' => 'Cronjob ir pārstājis darboties',
    'is_not_working_description' => 'Cronjob nav darbojies pēdējo 10 minūšu laikā. Lūdzu, pārbaudiet servera konfigurāciju vai sazinieties ar savu hostinga pakalpojumu sniedzēju.',
    'last_checked' => 'Pēdējā aktivitāte: :time',
    'copy_button' => 'Kopēt komandu',
    'what_is' => [
        'title' => 'Kas ir Cronjob?',
        'description' => 'Cronjob ir automatizēts uzdevums, kas darbojas fonā uz jūsu servera. Tas ļauj jūsu vietnei automātiski veikt svarīgus uzdevumus bez nepieciešamības kaut ko darīt manuāli.',
        'examples' => 'Piemēri',
        'features' => 'Pamesta groza atgādinājumu sūtīšana, ieplānotu e-pastu apstrāde, vecu datu tīrīšana, atskaišu ģenerēšana un vēl.',
    ],
    'command' => [
        'title' => 'Jūsu Cronjob komanda',
        'description' => 'Nokopējiet šo komandu un pievienojiet to savam hostinga vadības panelim. Šai komandai jādarbojas katru minūti, lai jūsu automatizētie uzdevumi strādātu.',
    ],
    'setup' => [
        'name' => 'Kā iestatīt',
        'copied' => 'Nokopēts starpliktuvē!',
        'choose_hosting' => 'Izvēlieties savu hostinga vadības paneli zemāk un sekojiet soli pa solim instrukcijām:',
    ],
    'cpanel' => [
        'step1' => 'Piesakieties savā <strong>cPanel</strong> kontā',
        'step2' => 'Atrodiet un noklikšķiniet uz <strong>"Cron Jobs"</strong> sadaļā "Advanced"',
        'step3' => 'Zem "Add New Cron Job" izvēlieties <strong>"Once Per Minute (* * * * *)"</strong> no nolaižamās izvēlnes',
        'step4' => '<strong>Ielīmējiet komandu</strong>, ko nokopējāt augstāk, laukā "Command"',
        'step5' => 'Noklikšķiniet uz <strong>"Add New Cron Job"</strong>, lai saglabātu',
    ],
    'plesk' => [
        'step1' => 'Piesakieties savā <strong>Plesk</strong> vadības panelī',
        'step2' => 'Dodieties uz <strong>"Scheduled Tasks"</strong> (vai "Cron Jobs")',
        'step3' => 'Noklikšķiniet uz <strong>"Add Task"</strong> vai <strong>"Schedule a Task"</strong>',
        'step4' => 'Iestatiet grafiku izpildei <strong>katru minūti</strong> un ielīmējiet komandu',
        'step5' => 'Noklikšķiniet uz <strong>"OK"</strong> vai <strong>"Apply"</strong>, lai saglabātu',
    ],
    'directadmin' => [
        'step1' => 'Piesakieties savā <strong>DirectAdmin</strong> panelī',
        'step2' => 'Dodieties uz <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Noklikšķiniet uz <strong>"Add Cron Job"</strong>',
        'step4' => 'Iestatiet visus laika laukus uz <strong>*</strong> (katru minūti) un ielīmējiet komandu',
        'step5' => 'Noklikšķiniet uz <strong>"Add"</strong>, lai saglabātu cronjob',
    ],
    'ssh' => [
        'step1' => 'Pieslēdzieties savam serverim caur <strong>SSH</strong>, izmantojot Terminal vai PuTTY',
        'step2' => 'Ierakstiet <code>crontab -e</code> un nospiediet Enter, lai rediģētu crontab failu',
        'step3' => 'Pievienojiet jaunu rindu apakšā un <strong>ielīmējiet komandu</strong>',
        'step4' => 'Nospiediet <strong>Ctrl+X</strong>, tad <strong>Y</strong>, tad <strong>Enter</strong>, lai saglabātu (nano redaktoram)',
        'step5' => 'Cronjob tagad ir aktīvs un darbosies katru minūti',
    ],
    'need_help' => 'Nepieciešama palīdzība? Sazinieties ar savu hostinga pakalpojumu sniedzēju un lūdziet viņus iestatīt cronjob, kas darbojas katru minūti ar augstāk parādīto komandu.',
];
