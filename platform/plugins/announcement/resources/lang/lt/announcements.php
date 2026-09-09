<?php

return [
    'name' => 'Pranešimai',

    'enums' => [
        'announce_placement' => [
            'top' => 'Viršuje',
            'bottom' => 'Pritvirtinta apačioje',
            'popup' => 'Iššokantis langas',
            'theme' => 'Integruota į temą',
        ],

        'text_alignment' => [
            'start' => 'Pradžia',
            'center' => 'Centras',
        ],
    ],

    'validation' => [
        'font_size' => 'Šrifto dydis turi būti galiojanti CSS šrifto dydžio reikšmė.',
        'text_color' => 'Teksto spalva turi būti galiojanti šešioliktainė spalvos reikšmė.',
    ],

    'create' => 'Sukurti naują pranešimą',
    'add_new' => 'Pridėti naują',
    'settings' => [
        'name' => 'Pranešimas',
        'description' => 'Tvarkyti pranešimų nustatymus',
    ],

    'background_color' => 'Fono spalva',
    'font_size' => 'Šrifto dydis',
    'font_size_help' => 'Palikite tuščią, kad būtų naudojamas numatytasis. Pavyzdys: 1rem, 1em, 12px, ...',
    'text_color' => 'Teksto spalva',
    'start_date' => 'Pradžios data',
    'end_date' => 'Pabaigos data',
    'has_action' => 'Turi veiksmą',
    'action_label' => 'Veiksmo etiketė',
    'action_url' => 'Veiksmo URL',
    'action_open_new_tab' => 'Atidaryti naujame skirtuke',
    'dismissible_label' => 'Leisti vartotojui uždaryti pranešimą',
    'placement' => 'Vieta',
    'text_alignment' => 'Teksto lygiavimas',
    'is_active' => 'Aktyvus',
    'max_width' => 'Maksimalus plotis',
    'max_width_help' => 'Palikite tuščią, kad būtų naudojama numatytoji reikšmė. Pavyzdys: 100%, 500px, ...',
    'max_width_unit' => 'Maksimalaus pločio vienetas',
    'font_size_unit' => 'Šrifto dydžio vienetas',
    'autoplay_label' => 'Automatinis grojimas',
    'autoplay_delay_label' => 'Automatinio grojimo uždelsa',
    'autoplay_delay_help' => 'Uždelsa tarp kiekvieno pranešimo milisekundėmis. Palikite tuščią, kad būtų naudojama numatytoji reikšmė (5000).',
    'lazy_loading' => 'Atidėtas įkėlimas',
    'lazy_loading_description' => 'Įjunkite šią parinktį, kad pagerintumėte puslapio įkėlimo greitį',
    'hide_on_mobile' => 'Slėpti mobiliajame',
    'dismiss' => 'Uždaryti',

    // Placeholders and help texts
    'name_placeholder' => 'Įveskite pranešimo pavadinimą',
    'name_help' => 'Pavadinimas tik vidinei nuorodai, nematomas vartotojams',
    'content_placeholder' => 'Čia įveskite pranešimo žinutę...',
    'content_help' => 'Žinutė, kuri bus rodoma vartotojams. Palaiko HTML formatavimą.',
    'start_date_placeholder' => 'Pasirinkite pradžios datą ir laiką',
    'start_date_help' => 'Pranešimas bus matomas nuo šios datos. Palikite tuščią, kad pradėtumėte iš karto.',
    'end_date_placeholder' => 'Pasirinkite pabaigos datą ir laiką',
    'end_date_help' => 'Pranešimas bus paslėptas po šios datos. Palikite tuščią, kad nebūtų galiojimo pabaigos.',
    'has_action_help' => 'Pridėkite kvietimo veikti mygtuką prie savo pranešimo',
    'action_label_placeholder' => 'pvz., Sužinokite daugiau, Pirkite dabar',
    'action_label_help' => 'Tekstas, rodomas veiksmo mygtuke',
    'action_url_placeholder' => 'https://pavyzdys.com/puslapis',
    'action_url_help' => 'URL, į kurį vartotojai bus nukreipti spustelėję veiksmo mygtuką',
    'action_open_new_tab_help' => 'Atidaryti veiksmo nuorodą naujame naršyklės skirtuke',
    'is_active_help' => 'Įjungti arba išjungti šį pranešimą jo neištrynant',
];
