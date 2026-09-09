<?php

return [
    'name' => 'Aankondigingen',

    'enums' => [
        'announce_placement' => [
            'top' => 'Boven',
            'bottom' => 'Vast onderaan',
            'popup' => 'Pop-up',
            'theme' => 'Ingebouwd in thema',
        ],

        'text_alignment' => [
            'start' => 'Begin',
            'center' => 'Midden',
        ],
    ],

    'validation' => [
        'font_size' => 'Lettergrootte moet een geldige CSS-lettergrootte waarde zijn.',
        'text_color' => 'Tekstkleur moet een geldige hex kleurwaarde zijn.',
    ],

    'create' => 'Nieuwe aankondiging maken',
    'add_new' => 'Nieuwe toevoegen',
    'settings' => [
        'name' => 'Aankondiging',
        'description' => 'Beheer aankondigingsinstellingen',
    ],

    'background_color' => 'Achtergrondkleur',
    'font_size' => 'Lettergrootte',
    'font_size_help' => 'Laat leeg om standaard te gebruiken. Voorbeeld: 1rem, 1em, 12px, ...',
    'text_color' => 'Tekstkleur',
    'start_date' => 'Startdatum',
    'end_date' => 'Einddatum',
    'has_action' => 'Heeft actie',
    'action_label' => 'Actielabel',
    'action_url' => 'Actie-URL',
    'action_open_new_tab' => 'Openen in nieuw tabblad',
    'dismissible_label' => 'Sta gebruiker toe aankondiging te sluiten',
    'placement' => 'Plaatsing',
    'text_alignment' => 'Tekstuitlijning',
    'is_active' => 'Is actief',
    'max_width' => 'Maximale breedte',
    'max_width_help' => 'Laat leeg om standaardwaarde te gebruiken. Voorbeeld: 100%, 500px, ...',
    'max_width_unit' => 'Maximale breedte-eenheid',
    'font_size_unit' => 'Lettergrootte-eenheid',
    'autoplay_label' => 'Automatisch afspelen',
    'autoplay_delay_label' => 'Vertraging automatisch afspelen',
    'autoplay_delay_help' => 'De vertraging tussen elke aankondiging in milliseconden. Laat leeg om standaardwaarde te gebruiken (5000).',
    'lazy_loading' => 'Lui laden',
    'lazy_loading_description' => 'Schakel deze optie in om de laadsnelheid van de pagina te verbeteren',
    'hide_on_mobile' => 'Verbergen op mobiel',
    'dismiss' => 'Sluiten',

    // Placeholders and help texts
    'name_placeholder' => 'Voer aankondigingsnaam in',
    'name_help' => 'Naam alleen voor interne referentie, niet zichtbaar voor gebruikers',
    'content_placeholder' => 'Voer hier uw aankondigingsbericht in...',
    'content_help' => 'Het bericht dat aan gebruikers wordt getoond. Ondersteunt HTML-opmaak.',
    'start_date_placeholder' => 'Selecteer startdatum en -tijd',
    'start_date_help' => 'Aankondiging zal zichtbaar zijn vanaf deze datum. Laat leeg om direct te starten.',
    'end_date_placeholder' => 'Selecteer einddatum en -tijd',
    'end_date_help' => 'Aankondiging wordt verborgen na deze datum. Laat leeg voor geen vervaldatum.',
    'has_action_help' => 'Voeg een call-to-action knop toe aan uw aankondiging',
    'action_label_placeholder' => 'bijv., Meer informatie, Nu kopen',
    'action_label_help' => 'Tekst weergegeven op de actieknop',
    'action_url_placeholder' => 'https://voorbeeld.nl/pagina',
    'action_url_help' => 'URL waarnaar gebruikers worden doorgestuurd bij klikken op de actieknop',
    'action_open_new_tab_help' => 'Open de actielink in een nieuw browsertabblad',
    'is_active_help' => 'Schakel deze aankondiging in of uit zonder deze te verwijderen',
];
