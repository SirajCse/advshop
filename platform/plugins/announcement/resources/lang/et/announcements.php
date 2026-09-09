<?php

return [
    'name' => 'Teated',

    'enums' => [
        'announce_placement' => [
            'top' => 'Üleval',
            'bottom' => 'Fikseeritud all',
            'popup' => 'Hüpikaken',
            'theme' => 'Teemasse integreeritud',
        ],

        'text_alignment' => [
            'start' => 'Algus',
            'center' => 'Keskele',
        ],
    ],

    'validation' => [
        'font_size' => 'Fondi suurus peab olema kehtiv CSS fondi suuruse väärtus.',
        'text_color' => 'Teksti värv peab olema kehtiv kuueteistkümnendsüsteemi värvi väärtus.',
    ],

    'create' => 'Loo uus teade',
    'add_new' => 'Lisa uus',
    'settings' => [
        'name' => 'Teade',
        'description' => 'Halda teadete seadeid',
    ],

    'background_color' => 'Tausta värv',
    'font_size' => 'Fondi suurus',
    'font_size_help' => 'Jäta tühjaks vaikeväärtuse kasutamiseks. Näide: 1rem, 1em, 12px, ...',
    'text_color' => 'Teksti värv',
    'start_date' => 'Alguskuupäev',
    'end_date' => 'Lõppkuupäev',
    'has_action' => 'On tegevus',
    'action_label' => 'Tegevuse silt',
    'action_url' => 'Tegevuse URL',
    'action_open_new_tab' => 'Ava uuel vahekaardil',
    'dismissible_label' => 'Luba kasutajal teade sulgeda',
    'placement' => 'Asukoht',
    'text_alignment' => 'Teksti joondus',
    'is_active' => 'On aktiivne',
    'max_width' => 'Maksimaalne laius',
    'max_width_help' => 'Jäta tühjaks vaikeväärtuse kasutamiseks. Näide: 100%, 500px, ...',
    'max_width_unit' => 'Maksimaalse laiuse ühik',
    'font_size_unit' => 'Fondi suuruse ühik',
    'autoplay_label' => 'Automaatne esitus',
    'autoplay_delay_label' => 'Automaatse esituse viivitus',
    'autoplay_delay_help' => 'Viivitus iga teate vahel millisekundites. Jäta tühjaks vaikeväärtuse kasutamiseks (5000).',
    'lazy_loading' => 'Laisk laadimine',
    'lazy_loading_description' => 'Luba see valik lehe laadimise kiiruse parandamiseks',
    'hide_on_mobile' => 'Peida mobiilis',
    'dismiss' => 'Sulge',

    // Placeholders and help texts
    'name_placeholder' => 'Sisesta teate nimi',
    'name_help' => 'Nimi ainult sisemiseks viitamiseks, kasutajatele ei ole nähtav',
    'content_placeholder' => 'Sisesta siia oma teate sõnum...',
    'content_help' => 'Sõnum, mis kuvatakse kasutajatele. Toetab HTML vormindust.',
    'start_date_placeholder' => 'Vali alguskuupäev ja -kellaaeg',
    'start_date_help' => 'Teade on nähtav alates sellest kuupäevast. Jäta tühjaks kohe alustamiseks.',
    'end_date_placeholder' => 'Vali lõppkuupäev ja -kellaaeg',
    'end_date_help' => 'Teade peidetakse pärast seda kuupäeva. Jäta tühjaks aegumise puudumiseks.',
    'has_action_help' => 'Lisa oma teatele tegevusele kutsumise nupp',
    'action_label_placeholder' => 'nt Loe lisaks, Osta kohe',
    'action_label_help' => 'Tegevusnupul kuvatav tekst',
    'action_url_placeholder' => 'https://naidisveebi.ee/lehekülg',
    'action_url_help' => 'URL, kuhu kasutajad suunatakse tegevusnupule klõpsamisel',
    'action_open_new_tab_help' => 'Ava tegevuse link uuel brauseri vahekaardil',
    'is_active_help' => 'Luba või keela see teade seda kustutamata',
];
