<?php

return [
    'name' => 'Ankündigungen',

    'enums' => [
        'announce_placement' => [
            'top' => 'Oben',
            'bottom' => 'Fest am unteren Rand',
            'popup' => 'Popup',
            'theme' => 'Im Theme integriert',
        ],

        'text_alignment' => [
            'start' => 'Anfang',
            'center' => 'Mitte',
        ],
    ],

    'validation' => [
        'font_size' => 'Die Schriftgröße muss ein gültiger CSS-Schriftgrößenwert sein.',
        'text_color' => 'Die Textfarbe muss ein gültiger Hex-Farbwert sein.',
    ],

    'create' => 'Neue Ankündigung erstellen',
    'add_new' => 'Neu hinzufügen',
    'settings' => [
        'name' => 'Ankündigung',
        'description' => 'Ankündigungseinstellungen verwalten',
    ],

    'background_color' => 'Hintergrundfarbe',
    'font_size' => 'Schriftgröße',
    'font_size_help' => 'Leer lassen, um Standard zu verwenden. Beispiel: 1rem, 1em, 12px, ...',
    'text_color' => 'Textfarbe',
    'start_date' => 'Startdatum',
    'end_date' => 'Enddatum',
    'has_action' => 'Hat Aktion',
    'action_label' => 'Aktionsbeschriftung',
    'action_url' => 'Aktions-URL',
    'action_open_new_tab' => 'In neuem Tab öffnen',
    'dismissible_label' => 'Benutzer erlauben, Ankündigung zu schließen',
    'placement' => 'Platzierung',
    'text_alignment' => 'Textausrichtung',
    'is_active' => 'Ist aktiv',
    'max_width' => 'Maximale Breite',
    'max_width_help' => 'Leer lassen, um Standardwert zu verwenden. Beispiel: 100%, 500px, ...',
    'max_width_unit' => 'Einheit für maximale Breite',
    'font_size_unit' => 'Einheit für Schriftgröße',
    'autoplay_label' => 'Automatische Wiedergabe',
    'autoplay_delay_label' => 'Verzögerung der automatischen Wiedergabe',
    'autoplay_delay_help' => 'Die Verzögerung zwischen jeder Ankündigung in Millisekunden. Leer lassen, um Standardwert zu verwenden (5000).',
    'lazy_loading' => 'Lazy Loading',
    'lazy_loading_description' => 'Diese Option aktivieren, um die Seitenladegeschwindigkeit zu verbessern',
    'hide_on_mobile' => 'Auf Mobilgeräten ausblenden',
    'dismiss' => 'Schließen',

    // Placeholders and help texts
    'name_placeholder' => 'Ankündigungsname eingeben',
    'name_help' => 'Name nur zur internen Referenz, für Benutzer nicht sichtbar',
    'content_placeholder' => 'Geben Sie hier Ihre Ankündigungsnachricht ein...',
    'content_help' => 'Die Nachricht, die Benutzern angezeigt wird. Unterstützt HTML-Formatierung.',
    'start_date_placeholder' => 'Startdatum und -zeit auswählen',
    'start_date_help' => 'Die Ankündigung wird ab diesem Datum sichtbar sein. Leer lassen, um sofort zu starten.',
    'end_date_placeholder' => 'Enddatum und -zeit auswählen',
    'end_date_help' => 'Die Ankündigung wird nach diesem Datum ausgeblendet. Leer lassen für kein Ablaufdatum.',
    'has_action_help' => 'Fügen Sie Ihrer Ankündigung eine Call-to-Action-Schaltfläche hinzu',
    'action_label_placeholder' => 'z.B. Mehr erfahren, Jetzt einkaufen',
    'action_label_help' => 'Text, der auf der Aktionsschaltfläche angezeigt wird',
    'action_url_placeholder' => 'https://example.com/page',
    'action_url_help' => 'URL, zu der Benutzer weitergeleitet werden, wenn sie auf die Aktionsschaltfläche klicken',
    'action_open_new_tab_help' => 'Aktionslink in neuem Browser-Tab öffnen',
    'is_active_help' => 'Diese Ankündigung aktivieren oder deaktivieren, ohne sie zu löschen',
];
