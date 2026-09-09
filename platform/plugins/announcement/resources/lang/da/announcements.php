<?php

return [
    'name' => 'Meddelelser',

    'enums' => [
        'announce_placement' => [
            'top' => 'Top',
            'bottom' => 'Fast i bunden',
            'popup' => 'Pop-up',
            'theme' => 'Indbygget i tema',
        ],

        'text_alignment' => [
            'start' => 'Start',
            'center' => 'Center',
        ],
    ],

    'validation' => [
        'font_size' => 'Skriftstørrelse skal være en gyldig CSS skriftstørrelse værdi.',
        'text_color' => 'Tekstfarve skal være en gyldig hex farveværdi.',
    ],

    'create' => 'Opret ny meddelelse',
    'add_new' => 'Tilføj ny',
    'settings' => [
        'name' => 'Meddelelse',
        'description' => 'Administrer meddelelsesindstillinger',
    ],

    'background_color' => 'Baggrundsfarve',
    'font_size' => 'Skriftstørrelse',
    'font_size_help' => 'Lad være tom for at bruge standard. Eksempel: 1rem, 1em, 12px, ...',
    'text_color' => 'Tekstfarve',
    'start_date' => 'Startdato',
    'end_date' => 'Slutdato',
    'has_action' => 'Har handling',
    'action_label' => 'Handlingsetiket',
    'action_url' => 'Handlings-URL',
    'action_open_new_tab' => 'Åbn i ny fane',
    'dismissible_label' => 'Tillad bruger at afvise meddelelsen',
    'placement' => 'Placering',
    'text_alignment' => 'Tekstjustering',
    'is_active' => 'Er aktiv',
    'max_width' => 'Maksimal bredde',
    'max_width_help' => 'Lad være tom for at bruge standardværdi. Eksempel: 100%, 500px, ...',
    'max_width_unit' => 'Maksimal breddeenhed',
    'font_size_unit' => 'Skriftstørrelsesenhed',
    'autoplay_label' => 'Automatisk afspilning',
    'autoplay_delay_label' => 'Forsinkelse ved automatisk afspilning',
    'autoplay_delay_help' => 'Forsinkelsen mellem hver meddelelse i millisekunder. Lad være tom for at bruge standardværdi (5000).',
    'lazy_loading' => 'Lazy loading',
    'lazy_loading_description' => 'Aktiver denne mulighed for at forbedre sideindlæsningshastigheden',
    'hide_on_mobile' => 'Skjul på mobil',
    'dismiss' => 'Afvis',

    // Placeholders and help texts
    'name_placeholder' => 'Indtast meddelelsesnavn',
    'name_help' => 'Navn kun til intern reference, ikke synligt for brugere',
    'content_placeholder' => 'Indtast din meddelelsesbesked her...',
    'content_help' => 'Beskeden, der vises til brugerne. Understøtter HTML-formatering.',
    'start_date_placeholder' => 'Vælg startdato og -tid',
    'start_date_help' => 'Meddelelsen vil være synlig fra denne dato. Lad være tom for at starte med det samme.',
    'end_date_placeholder' => 'Vælg slutdato og -tid',
    'end_date_help' => 'Meddelelsen vil blive skjult efter denne dato. Lad være tom for ingen udløb.',
    'has_action_help' => 'Tilføj en call-to-action-knap til din meddelelse',
    'action_label_placeholder' => 'f.eks., Lær mere, Køb nu',
    'action_label_help' => 'Tekst vist på handlingsknappen',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'URL, hvor brugere vil blive omdirigeret til, når de klikker på handlingsknappen',
    'action_open_new_tab_help' => 'Åbn handlingslinket i en ny browserfane',
    'is_active_help' => 'Aktiver eller deaktiver denne meddelelse uden at slette den',
];
