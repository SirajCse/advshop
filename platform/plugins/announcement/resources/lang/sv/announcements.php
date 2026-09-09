<?php

return [
    'name' => 'Meddelanden',

    'enums' => [
        'announce_placement' => [
            'top' => 'Toppen',
            'bottom' => 'Fast längst ner',
            'popup' => 'Popup',
            'theme' => 'Inbyggd i tema',
        ],

        'text_alignment' => [
            'start' => 'Start',
            'center' => 'Mitten',
        ],
    ],

    'validation' => [
        'font_size' => 'Teckenstorlek måste vara ett giltigt CSS-teckenstorleksvärde.',
        'text_color' => 'Textfärg måste vara ett giltigt hex-färgvärde.',
    ],

    'create' => 'Skapa nytt meddelande',
    'add_new' => 'Lägg till nytt',
    'settings' => [
        'name' => 'Meddelande',
        'description' => 'Hantera meddelandeinställningar',
    ],

    'background_color' => 'Bakgrundsfärg',
    'font_size' => 'Teckenstorlek',
    'font_size_help' => 'Lämna tomt för att använda standard. Exempel: 1rem, 1em, 12px, ...',
    'text_color' => 'Textfärg',
    'start_date' => 'Startdatum',
    'end_date' => 'Slutdatum',
    'has_action' => 'Har åtgärd',
    'action_label' => 'Åtgärdsetikett',
    'action_url' => 'Åtgärds-URL',
    'action_open_new_tab' => 'Öppna i ny flik',
    'dismissible_label' => 'Tillåt användare att stänga meddelandet',
    'placement' => 'Placering',
    'text_alignment' => 'Textjustering',
    'is_active' => 'Är aktiv',
    'max_width' => 'Maximal bredd',
    'max_width_help' => 'Lämna tomt för att använda standardvärde. Exempel: 100%, 500px, ...',
    'max_width_unit' => 'Maximal bredd-enhet',
    'font_size_unit' => 'Teckenstorlek-enhet',
    'autoplay_label' => 'Automatisk uppspelning',
    'autoplay_delay_label' => 'Fördröjning för automatisk uppspelning',
    'autoplay_delay_help' => 'Fördröjningen mellan varje meddelande i millisekunder. Lämna tomt för att använda standardvärde (5000).',
    'lazy_loading' => 'Lat laddning',
    'lazy_loading_description' => 'Aktivera detta alternativ för att förbättra sidladdningshastigheten',
    'hide_on_mobile' => 'Dölj på mobil',
    'dismiss' => 'Stäng',

    // Placeholders and help texts
    'name_placeholder' => 'Ange meddelandenamn',
    'name_help' => 'Namn endast för intern referens, inte synligt för användare',
    'content_placeholder' => 'Ange ditt meddelande här...',
    'content_help' => 'Meddelandet som kommer att visas för användare. Stöder HTML-formatering.',
    'start_date_placeholder' => 'Välj startdatum och tid',
    'start_date_help' => 'Meddelandet kommer att vara synligt från detta datum. Lämna tomt för att starta omedelbart.',
    'end_date_placeholder' => 'Välj slutdatum och tid',
    'end_date_help' => 'Meddelandet kommer att döljas efter detta datum. Lämna tomt för ingen utgång.',
    'has_action_help' => 'Lägg till en uppmaning till handling-knapp till ditt meddelande',
    'action_label_placeholder' => 't.ex., Lär dig mer, Handla nu',
    'action_label_help' => 'Text som visas på åtgärdsknappen',
    'action_url_placeholder' => 'https://exempel.se/sida',
    'action_url_help' => 'URL dit användare kommer att omdirigeras när de klickar på åtgärdsknappen',
    'action_open_new_tab_help' => 'Öppna åtgärdslänken i en ny webbläsarflik',
    'is_active_help' => 'Aktivera eller inaktivera detta meddelande utan att ta bort det',
];
