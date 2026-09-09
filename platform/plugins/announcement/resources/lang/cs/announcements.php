<?php

return [
    'name' => 'Oznámení',

    'enums' => [
        'announce_placement' => [
            'top' => 'Nahoře',
            'bottom' => 'Pevně dole',
            'popup' => 'Vyskakovací okno',
            'theme' => 'Zabudováno v motivu',
        ],

        'text_alignment' => [
            'start' => 'Začátek',
            'center' => 'Střed',
        ],
    ],

    'validation' => [
        'font_size' => 'Velikost písma musí být platná hodnota velikosti písma CSS.',
        'text_color' => 'Barva textu musí být platná hodnota hexadecimální barvy.',
    ],

    'create' => 'Vytvořit nové oznámení',
    'add_new' => 'Přidat nové',
    'settings' => [
        'name' => 'Oznámení',
        'description' => 'Spravovat nastavení oznámení',
    ],

    'background_color' => 'Barva pozadí',
    'font_size' => 'Velikost písma',
    'font_size_help' => 'Nechte prázdné pro použití výchozí hodnoty. Příklad: 1rem, 1em, 12px, ...',
    'text_color' => 'Barva textu',
    'start_date' => 'Datum zahájení',
    'end_date' => 'Datum ukončení',
    'has_action' => 'Má akci',
    'action_label' => 'Štítek akce',
    'action_url' => 'URL akce',
    'action_open_new_tab' => 'Otevřít v nové kartě',
    'dismissible_label' => 'Povolit uživateli zavřít oznámení',
    'placement' => 'Umístění',
    'text_alignment' => 'Zarovnání textu',
    'is_active' => 'Je aktivní',
    'max_width' => 'Maximální šířka',
    'max_width_help' => 'Nechte prázdné pro použití výchozí hodnoty. Příklad: 100%, 500px, ...',
    'max_width_unit' => 'Jednotka maximální šířky',
    'font_size_unit' => 'Jednotka velikosti písma',
    'autoplay_label' => 'Automatické přehrávání',
    'autoplay_delay_label' => 'Zpoždění automatického přehrávání',
    'autoplay_delay_help' => 'Zpoždění mezi jednotlivými oznámeními v milisekundách. Nechte prázdné pro použití výchozí hodnoty (5000).',
    'lazy_loading' => 'Líné načítání',
    'lazy_loading_description' => 'Povolte tuto možnost pro zlepšení rychlosti načítání stránky',
    'hide_on_mobile' => 'Skrýt na mobilních zařízeních',
    'dismiss' => 'Zavřít',

    // Placeholders and help texts
    'name_placeholder' => 'Zadejte název oznámení',
    'name_help' => 'Název pouze pro interní referenci, není viditelný pro uživatele',
    'content_placeholder' => 'Zde zadejte zprávu oznámení...',
    'content_help' => 'Zpráva, která bude zobrazena uživatelům. Podporuje HTML formátování.',
    'start_date_placeholder' => 'Vyberte datum a čas zahájení',
    'start_date_help' => 'Oznámení bude viditelné od tohoto data. Nechte prázdné pro okamžité zahájení.',
    'end_date_placeholder' => 'Vyberte datum a čas ukončení',
    'end_date_help' => 'Oznámení bude skryto po tomto datu. Nechte prázdné pro žádné vypršení.',
    'has_action_help' => 'Přidejte tlačítko výzvy k akci do svého oznámení',
    'action_label_placeholder' => 'např., Zjistit více, Nakupovat nyní',
    'action_label_help' => 'Text zobrazený na tlačítku akce',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'URL, kam budou uživatelé přesměrováni po kliknutí na tlačítko akce',
    'action_open_new_tab_help' => 'Otevřít odkaz akce v nové kartě prohlížeče',
    'is_active_help' => 'Povolit nebo zakázat toto oznámení bez jeho smazání',
];
