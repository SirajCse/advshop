<?php

return [
    'name' => 'Oznámenia',

    'enums' => [
        'announce_placement' => [
            'top' => 'Hore',
            'bottom' => 'Fixné dole',
            'popup' => 'Vyskakovacie okno',
            'theme' => 'Zabudované v téme',
        ],

        'text_alignment' => [
            'start' => 'Začiatok',
            'center' => 'Stred',
        ],
    ],

    'validation' => [
        'font_size' => 'Veľkosť písma musí byť platná hodnota veľkosti písma CSS.',
        'text_color' => 'Farba textu musí byť platná hexadecimálna hodnota farby.',
    ],

    'create' => 'Vytvoriť nové oznámenie',
    'add_new' => 'Pridať nové',
    'settings' => [
        'name' => 'Oznámenie',
        'description' => 'Spravovať nastavenia oznámení',
    ],

    'background_color' => 'Farba pozadia',
    'font_size' => 'Veľkosť písma',
    'font_size_help' => 'Nechajte prázdne pre použitie predvolenej hodnoty. Príklad: 1rem, 1em, 12px, ...',
    'text_color' => 'Farba textu',
    'start_date' => 'Dátum začiatku',
    'end_date' => 'Dátum ukončenia',
    'has_action' => 'Má akciu',
    'action_label' => 'Označenie akcie',
    'action_url' => 'URL akcie',
    'action_open_new_tab' => 'Otvoriť na novej karte',
    'dismissible_label' => 'Povoliť používateľovi zavrieť oznámenie',
    'placement' => 'Umiestnenie',
    'text_alignment' => 'Zarovnanie textu',
    'is_active' => 'Je aktívne',
    'max_width' => 'Maximálna šírka',
    'max_width_help' => 'Nechajte prázdne pre použitie predvolenej hodnoty. Príklad: 100%, 500px, ...',
    'max_width_unit' => 'Jednotka maximálnej šírky',
    'font_size_unit' => 'Jednotka veľkosti písma',
    'autoplay_label' => 'Automatické prehrávanie',
    'autoplay_delay_label' => 'Oneskorenie automatického prehrávania',
    'autoplay_delay_help' => 'Oneskorenie medzi každým oznámením v milisekundách. Nechajte prázdne pre použitie predvolenej hodnoty (5000).',
    'lazy_loading' => 'Lenivé načítanie',
    'lazy_loading_description' => 'Povoľte túto možnosť na zlepšenie rýchlosti načítania stránky',
    'hide_on_mobile' => 'Skryť na mobilnom zariadení',
    'dismiss' => 'Zavrieť',

    // Placeholders and help texts
    'name_placeholder' => 'Zadajte názov oznámenia',
    'name_help' => 'Názov len na internú referenciu, nie je viditeľný pre používateľov',
    'content_placeholder' => 'Zadajte správu oznámenia tu...',
    'content_help' => 'Správa, ktorá bude zobrazená používateľom. Podporuje HTML formátovanie.',
    'start_date_placeholder' => 'Vyberte dátum a čas začiatku',
    'start_date_help' => 'Oznámenie bude viditeľné od tohto dátumu. Nechajte prázdne pre okamžité spustenie.',
    'end_date_placeholder' => 'Vyberte dátum a čas ukončenia',
    'end_date_help' => 'Oznámenie bude skryté po tomto dátume. Nechajte prázdne pre žiadne vypršanie.',
    'has_action_help' => 'Pridajte tlačidlo výzvy na akciu do svojho oznámenia',
    'action_label_placeholder' => 'napr., Zistiť viac, Nakúpiť teraz',
    'action_label_help' => 'Text zobrazený na tlačidle akcie',
    'action_url_placeholder' => 'https://priklad.sk/stranka',
    'action_url_help' => 'URL, na ktorú budú používatelia presmerovaní po kliknutí na tlačidlo akcie',
    'action_open_new_tab_help' => 'Otvorte odkaz akcie na novej karte prehliadača',
    'is_active_help' => 'Povoliť alebo zakázať toto oznámenie bez jeho vymazania',
];
