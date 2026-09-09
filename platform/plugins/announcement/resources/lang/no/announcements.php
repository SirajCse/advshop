<?php

return [
    'name' => 'Kunngjøringer',

    'enums' => [
        'announce_placement' => [
            'top' => 'Topp',
            'bottom' => 'Fast nederst',
            'popup' => 'Popup',
            'theme' => 'Innebygd i tema',
        ],

        'text_alignment' => [
            'start' => 'Start',
            'center' => 'Senter',
        ],
    ],

    'validation' => [
        'font_size' => 'Skriftstørrelse må være en gyldig CSS skriftstørrelse verdi.',
        'text_color' => 'Tekstfarge må være en gyldig hex fargeverdi.',
    ],

    'create' => 'Opprett ny kunngjøring',
    'add_new' => 'Legg til ny',
    'settings' => [
        'name' => 'Kunngjøring',
        'description' => 'Administrer kunngjøringsinnstillinger',
    ],

    'background_color' => 'Bakgrunnsfarge',
    'font_size' => 'Skriftstørrelse',
    'font_size_help' => 'La stå tom for å bruke standard. Eksempel: 1rem, 1em, 12px, ...',
    'text_color' => 'Tekstfarge',
    'start_date' => 'Startdato',
    'end_date' => 'Sluttdato',
    'has_action' => 'Har handling',
    'action_label' => 'Handlingsetikett',
    'action_url' => 'Handlings-URL',
    'action_open_new_tab' => 'Åpne i ny fane',
    'dismissible_label' => 'Tillat bruker å lukke kunngjøringen',
    'placement' => 'Plassering',
    'text_alignment' => 'Tekstjustering',
    'is_active' => 'Er aktiv',
    'max_width' => 'Maks bredde',
    'max_width_help' => 'La stå tom for å bruke standardverdi. Eksempel: 100%, 500px, ...',
    'max_width_unit' => 'Maks bredde-enhet',
    'font_size_unit' => 'Skriftstørrelse-enhet',
    'autoplay_label' => 'Automatisk avspilling',
    'autoplay_delay_label' => 'Forsinkelse for automatisk avspilling',
    'autoplay_delay_help' => 'Forsinkelsen mellom hver kunngjøring i millisekunder. La stå tom for å bruke standardverdi (5000).',
    'lazy_loading' => 'Lat lasting',
    'lazy_loading_description' => 'Aktiver dette alternativet for å forbedre sidelastingshastighet',
    'hide_on_mobile' => 'Skjul på mobil',
    'dismiss' => 'Lukk',

    // Placeholders and help texts
    'name_placeholder' => 'Skriv inn kunngjøringsnavn',
    'name_help' => 'Navn kun for intern referanse, ikke synlig for brukere',
    'content_placeholder' => 'Skriv inn kunngjøringsmeldingen din her...',
    'content_help' => 'Meldingen som vil bli vist til brukere. Støtter HTML-formatering.',
    'start_date_placeholder' => 'Velg startdato og -tid',
    'start_date_help' => 'Kunngjøringen vil være synlig fra denne datoen. La stå tom for å starte umiddelbart.',
    'end_date_placeholder' => 'Velg sluttdato og -tid',
    'end_date_help' => 'Kunngjøringen vil bli skjult etter denne datoen. La stå tom for ingen utløp.',
    'has_action_help' => 'Legg til en call-to-action-knapp til kunngjøringen din',
    'action_label_placeholder' => 'f.eks., Lær mer, Handle nå',
    'action_label_help' => 'Tekst vist på handlingsknappen',
    'action_url_placeholder' => 'https://eksempel.no/side',
    'action_url_help' => 'URL hvor brukere vil bli omdirigert når de klikker på handlingsknappen',
    'action_open_new_tab_help' => 'Åpne handlingslenken i en ny nettleserfane',
    'is_active_help' => 'Aktiver eller deaktiver denne kunngjøringen uten å slette den',
];
