<?php

return [
    'name' => 'Cronjob',
    'description' => 'Nustatykite automatizuotas fonines užduotis, kad jūsų svetainė veiktų sklandžiai.',
    'is_not_ready' => 'Cronjob dar nesukonfigūruotas',
    'is_not_ready_description' => 'Prašome sekti žemiau pateiktas instrukcijas, kad nustatytumėte cronjob. Tai reikalinga funkcijoms, tokioms kaip palikto krepšelio priminimai, el. pašto planavimas ir kitos automatizuotos užduotys.',
    'is_working' => 'Cronjob veikia teisingai!',
    'is_not_working' => 'Cronjob nustojo veikti',
    'is_not_working_description' => 'Cronjob nebuvo paleistas per pastarąsias 10 minučių. Patikrinkite serverio konfigūraciją arba susisiekite su savo prieglobos paslaugų teikėju.',
    'last_checked' => 'Paskutinė veikla: :time',
    'copy_button' => 'Kopijuoti komandą',
    'what_is' => [
        'title' => 'Kas yra Cronjob?',
        'description' => 'Cronjob yra automatizuota užduotis, kuri vykdoma fone jūsų serveryje. Tai leidžia jūsų svetainei automatiškai atlikti svarbias užduotis be jūsų rankinio įsikišimo.',
        'examples' => 'Pavyzdžiai',
        'features' => 'Palikto krepšelio priminimų siuntimas, suplanuotų el. laiškų apdorojimas, senų duomenų valymas, ataskaitų generavimas ir daugiau.',
    ],
    'command' => [
        'title' => 'Jūsų Cronjob komanda',
        'description' => 'Nukopijuokite šią komandą ir pridėkite ją prie savo prieglobos valdymo skydelio. Ši komanda turi būti vykdoma kas minutę, kad jūsų automatizuotos užduotys veiktų.',
    ],
    'setup' => [
        'name' => 'Kaip nustatyti',
        'copied' => 'Nukopijuota į iškarpinę!',
        'choose_hosting' => 'Pasirinkite savo prieglobos valdymo skydelį žemiau ir sekite žingsnis po žingsnio instrukcijas:',
    ],
    'cpanel' => [
        'step1' => 'Prisijunkite prie savo <strong>cPanel</strong> paskyros',
        'step2' => 'Raskite ir spustelėkite <strong>"Cron Jobs"</strong> skiltyje "Advanced"',
        'step3' => 'Skiltyje "Add New Cron Job" pasirinkite <strong>"Once Per Minute (* * * * *)"</strong> iš išskleidžiamojo meniu',
        'step4' => '<strong>Įklijuokite komandą</strong>, kurią nukopijuote aukščiau, į lauką "Command"',
        'step5' => 'Spustelėkite <strong>"Add New Cron Job"</strong>, kad išsaugotumėte',
    ],
    'plesk' => [
        'step1' => 'Prisijunkite prie savo <strong>Plesk</strong> valdymo skydelio',
        'step2' => 'Eikite į <strong>"Scheduled Tasks"</strong> (arba "Cron Jobs")',
        'step3' => 'Spustelėkite <strong>"Add Task"</strong> arba <strong>"Schedule a Task"</strong>',
        'step4' => 'Nustatykite grafiką, kad būtų vykdoma <strong>kas minutę</strong> ir įklijuokite komandą',
        'step5' => 'Spustelėkite <strong>"OK"</strong> arba <strong>"Apply"</strong>, kad išsaugotumėte',
    ],
    'directadmin' => [
        'step1' => 'Prisijunkite prie savo <strong>DirectAdmin</strong> skydelio',
        'step2' => 'Eikite į <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Spustelėkite <strong>"Add Cron Job"</strong>',
        'step4' => 'Nustatykite visus laiko laukus į <strong>*</strong> (kas minutę) ir įklijuokite komandą',
        'step5' => 'Spustelėkite <strong>"Add"</strong>, kad išsaugotumėte cronjob',
    ],
    'ssh' => [
        'step1' => 'Prisijunkite prie savo serverio per <strong>SSH</strong> naudodami Terminal arba PuTTY',
        'step2' => 'Įveskite <code>crontab -e</code> ir paspauskite Enter, kad redaguotumėte crontab failą',
        'step3' => 'Pridėkite naują eilutę apačioje ir <strong>įklijuokite komandą</strong>',
        'step4' => 'Paspauskite <strong>Ctrl+X</strong>, tada <strong>Y</strong>, tada <strong>Enter</strong>, kad išsaugotumėte (nano redaktoriui)',
        'step5' => 'Cronjob dabar aktyvus ir bus vykdomas kas minutę',
    ],
    'need_help' => 'Reikia pagalbos? Susisiekite su savo prieglobos paslaugų teikėju ir paprašykite nustatyti cronjob, kuris vykdomas kas minutę su aukščiau parodyta komanda.',
];
