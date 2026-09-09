<?php

return [
    'name' => 'Közlemények',

    'enums' => [
        'announce_placement' => [
            'top' => 'Felül',
            'bottom' => 'Rögzítve alul',
            'popup' => 'Felugró ablak',
            'theme' => 'Téma beépített',
        ],

        'text_alignment' => [
            'start' => 'Kezdet',
            'center' => 'Közép',
        ],
    ],

    'validation' => [
        'font_size' => 'A betűméretnek érvényes CSS betűméret értéknek kell lennie.',
        'text_color' => 'A szöveg színének érvényes hexadecimális színértéknek kell lennie.',
    ],

    'create' => 'Új közlemény létrehozása',
    'add_new' => 'Új hozzáadása',
    'settings' => [
        'name' => 'Közlemény',
        'description' => 'Közlemény beállítások kezelése',
    ],

    'background_color' => 'Háttérszín',
    'font_size' => 'Betűméret',
    'font_size_help' => 'Hagyja üresen az alapértelmezett használatához. Példa: 1rem, 1em, 12px, ...',
    'text_color' => 'Szöveg színe',
    'start_date' => 'Kezdő dátum',
    'end_date' => 'Befejező dátum',
    'has_action' => 'Van művelete',
    'action_label' => 'Művelet címkéje',
    'action_url' => 'Művelet URL',
    'action_open_new_tab' => 'Megnyitás új lapon',
    'dismissible_label' => 'Engedélyezze a felhasználónak a közlemény bezárását',
    'placement' => 'Elhelyezés',
    'text_alignment' => 'Szöveg igazítása',
    'is_active' => 'Aktív',
    'max_width' => 'Maximális szélesség',
    'max_width_help' => 'Hagyja üresen az alapértelmezett érték használatához. Példa: 100%, 500px, ...',
    'max_width_unit' => 'Maximális szélesség egység',
    'font_size_unit' => 'Betűméret egység',
    'autoplay_label' => 'Automatikus lejátszás',
    'autoplay_delay_label' => 'Automatikus lejátszás késleltetése',
    'autoplay_delay_help' => 'A késleltetés az egyes közlemények között milliszekundumban. Hagyja üresen az alapértelmezett érték használatához (5000).',
    'lazy_loading' => 'Lusta betöltés',
    'lazy_loading_description' => 'Engedélyezze ezt az opciót az oldalbetöltési sebesség javítása érdekében',
    'hide_on_mobile' => 'Elrejtés mobilon',
    'dismiss' => 'Bezárás',

    // Placeholders and help texts
    'name_placeholder' => 'Adja meg a közlemény nevét',
    'name_help' => 'Név csak belső hivatkozáshoz, nem látható a felhasználók számára',
    'content_placeholder' => 'Írja be a közlemény üzenetét ide...',
    'content_help' => 'Az üzenet, amely megjelenik a felhasználók számára. Támogatja a HTML formázást.',
    'start_date_placeholder' => 'Válassza ki a kezdő dátumot és időt',
    'start_date_help' => 'A közlemény ettől a dátumtól lesz látható. Hagyja üresen az azonnali kezdéshez.',
    'end_date_placeholder' => 'Válassza ki a befejező dátumot és időt',
    'end_date_help' => 'A közlemény ezen dátum után el lesz rejtve. Hagyja üresen a lejárat nélkül.',
    'has_action_help' => 'Adjon hozzá cselekvésre ösztönző gombot a közleményéhez',
    'action_label_placeholder' => 'pl. Tudjon meg többet, Vásároljon most',
    'action_label_help' => 'A művelet gombon megjelenő szöveg',
    'action_url_placeholder' => 'https://pelda.hu/oldal',
    'action_url_help' => 'URL, ahová a felhasználók átirányításra kerülnek a művelet gombra kattintva',
    'action_open_new_tab_help' => 'Nyissa meg a művelet hivatkozást új böngésző lapon',
    'is_active_help' => 'Engedélyezze vagy tiltsa le ezt a közleményt anélkül, hogy törölné',
];
