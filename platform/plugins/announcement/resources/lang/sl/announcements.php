<?php

return [
    'name' => 'Obvestila',

    'enums' => [
        'announce_placement' => [
            'top' => 'Zgoraj',
            'bottom' => 'Pritrjeno spodaj',
            'popup' => 'Pojavno okno',
            'theme' => 'Vgrajeno v temo',
        ],

        'text_alignment' => [
            'start' => 'Začetek',
            'center' => 'Sredina',
        ],
    ],

    'validation' => [
        'font_size' => 'Velikost pisave mora biti veljavna vrednost velikosti pisave CSS.',
        'text_color' => 'Barva besedila mora biti veljavna šestnajstiška vrednost barve.',
    ],

    'create' => 'Ustvari novo obvestilo',
    'add_new' => 'Dodaj novo',
    'settings' => [
        'name' => 'Obvestilo',
        'description' => 'Upravljanje nastavitev obvestil',
    ],

    'background_color' => 'Barva ozadja',
    'font_size' => 'Velikost pisave',
    'font_size_help' => 'Pustite prazno za uporabo privzete vrednosti. Primer: 1rem, 1em, 12px, ...',
    'text_color' => 'Barva besedila',
    'start_date' => 'Začetni datum',
    'end_date' => 'Končni datum',
    'has_action' => 'Ima dejanje',
    'action_label' => 'Oznaka dejanja',
    'action_url' => 'URL dejanja',
    'action_open_new_tab' => 'Odpri v novem zavihku',
    'dismissible_label' => 'Dovoli uporabniku zapreti obvestilo',
    'placement' => 'Postavitev',
    'text_alignment' => 'Poravnava besedila',
    'is_active' => 'Je aktivno',
    'max_width' => 'Največja širina',
    'max_width_help' => 'Pustite prazno za uporabo privzete vrednosti. Primer: 100%, 500px, ...',
    'max_width_unit' => 'Enota največje širine',
    'font_size_unit' => 'Enota velikosti pisave',
    'autoplay_label' => 'Samodejno predvajanje',
    'autoplay_delay_label' => 'Zakasnitev samodejnega predvajanja',
    'autoplay_delay_help' => 'Zakasnitev med vsakim obvestilom v milisekundah. Pustite prazno za uporabo privzete vrednosti (5000).',
    'lazy_loading' => 'Leno nalaganje',
    'lazy_loading_description' => 'Omogočite to možnost za izboljšanje hitrosti nalaganja strani',
    'hide_on_mobile' => 'Skrij na mobilni napravi',
    'dismiss' => 'Zapri',

    // Placeholders and help texts
    'name_placeholder' => 'Vnesite ime obvestila',
    'name_help' => 'Ime samo za notranjo referenco, ni vidno uporabnikom',
    'content_placeholder' => 'Tukaj vnesite sporočilo obvestila...',
    'content_help' => 'Sporočilo, ki bo prikazano uporabnikom. Podpira oblikovanje HTML.',
    'start_date_placeholder' => 'Izberite začetni datum in čas',
    'start_date_help' => 'Obvestilo bo vidno od tega datuma. Pustite prazno za takojšen začetek.',
    'end_date_placeholder' => 'Izberite končni datum in čas',
    'end_date_help' => 'Obvestilo bo skrito po tem datumu. Pustite prazno brez poteka.',
    'has_action_help' => 'Dodajte gumb za poziv k dejanju v svoje obvestilo',
    'action_label_placeholder' => 'npr., Izvedi več, Kupi zdaj',
    'action_label_help' => 'Besedilo, prikazano na gumbu dejanja',
    'action_url_placeholder' => 'https://primer.si/stran',
    'action_url_help' => 'URL, na katerega bodo uporabniki preusmerjeni, ko kliknejo gumb dejanja',
    'action_open_new_tab_help' => 'Odprite povezavo dejanja v novem zavihku brskalnika',
    'is_active_help' => 'Omogočite ali onemogočite to obvestilo brez njegovega brisanja',
];
