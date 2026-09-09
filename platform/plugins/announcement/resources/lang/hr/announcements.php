<?php

return [
    'name' => 'Obavijesti',

    'enums' => [
        'announce_placement' => [
            'top' => 'Gore',
            'bottom' => 'Fiksno na dnu',
            'popup' => 'Skočni prozor',
            'theme' => 'Ugrađeno u temu',
        ],

        'text_alignment' => [
            'start' => 'Početak',
            'center' => 'Centar',
        ],
    ],

    'validation' => [
        'font_size' => 'Veličina fonta mora biti valjana CSS vrijednost veličine fonta.',
        'text_color' => 'Boja teksta mora biti valjana heksadecimalna vrijednost boje.',
    ],

    'create' => 'Stvori novu obavijest',
    'add_new' => 'Dodaj novo',
    'settings' => [
        'name' => 'Obavijest',
        'description' => 'Upravljaj postavkama obavijesti',
    ],

    'background_color' => 'Boja pozadine',
    'font_size' => 'Veličina fonta',
    'font_size_help' => 'Ostavite prazno za korištenje zadane vrijednosti. Primjer: 1rem, 1em, 12px, ...',
    'text_color' => 'Boja teksta',
    'start_date' => 'Datum početka',
    'end_date' => 'Datum kraja',
    'has_action' => 'Ima radnju',
    'action_label' => 'Oznaka radnje',
    'action_url' => 'URL radnje',
    'action_open_new_tab' => 'Otvori u novoj kartici',
    'dismissible_label' => 'Dopusti korisniku zatvaranje obavijesti',
    'placement' => 'Smještaj',
    'text_alignment' => 'Poravnanje teksta',
    'is_active' => 'Je aktivno',
    'max_width' => 'Maksimalna širina',
    'max_width_help' => 'Ostavite prazno za korištenje zadane vrijednosti. Primjer: 100%, 500px, ...',
    'max_width_unit' => 'Jedinica maksimalne širine',
    'font_size_unit' => 'Jedinica veličine fonta',
    'autoplay_label' => 'Automatska reprodukcija',
    'autoplay_delay_label' => 'Kašnjenje automatske reprodukcije',
    'autoplay_delay_help' => 'Kašnjenje između svake obavijesti u milisekundama. Ostavite prazno za korištenje zadane vrijednosti (5000).',
    'lazy_loading' => 'Lijena učitavanje',
    'lazy_loading_description' => 'Omogućite ovu opciju za poboljšanje brzine učitavanja stranice',
    'hide_on_mobile' => 'Sakrij na mobitelu',
    'dismiss' => 'Zatvori',

    // Placeholders and help texts
    'name_placeholder' => 'Unesite naziv obavijesti',
    'name_help' => 'Naziv samo za internu referencu, nije vidljiv korisnicima',
    'content_placeholder' => 'Unesite poruku obavijesti ovdje...',
    'content_help' => 'Poruka koja će biti prikazana korisnicima. Podržava HTML formatiranje.',
    'start_date_placeholder' => 'Odaberite datum i vrijeme početka',
    'start_date_help' => 'Obavijest će biti vidljiva od ovog datuma. Ostavite prazno za trenutni početak.',
    'end_date_placeholder' => 'Odaberite datum i vrijeme kraja',
    'end_date_help' => 'Obavijest će biti sakrivena nakon ovog datuma. Ostavite prazno bez isteka.',
    'has_action_help' => 'Dodajte gumb poziva na akciju svojoj obavijesti',
    'action_label_placeholder' => 'npr. Saznajte više, Kupite sada',
    'action_label_help' => 'Tekst prikazan na gumbu radnje',
    'action_url_placeholder' => 'https://primjer.com/stranica',
    'action_url_help' => 'URL na koji će korisnici biti preusmjereni kada kliknu gumb radnje',
    'action_open_new_tab_help' => 'Otvori poveznicu radnje u novoj kartici preglednika',
    'is_active_help' => 'Omogućite ili onemogućite ovu obavijest bez brisanja',
];
