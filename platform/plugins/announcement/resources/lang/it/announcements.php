<?php

return [
    'name' => 'Annunci',

    'enums' => [
        'announce_placement' => [
            'top' => 'In alto',
            'bottom' => 'Fisso in basso',
            'popup' => 'Popup',
            'theme' => 'Incorporato nel tema',
        ],

        'text_alignment' => [
            'start' => 'Inizio',
            'center' => 'Centro',
        ],
    ],

    'validation' => [
        'font_size' => 'La dimensione del carattere deve essere un valore valido di dimensione del carattere CSS.',
        'text_color' => 'Il colore del testo deve essere un valore di colore esadecimale valido.',
    ],

    'create' => 'Crea nuovo annuncio',
    'add_new' => 'Aggiungi nuovo',
    'settings' => [
        'name' => 'Annuncio',
        'description' => 'Gestisci le impostazioni degli annunci',
    ],

    'background_color' => 'Colore di sfondo',
    'font_size' => 'Dimensione carattere',
    'font_size_help' => 'Lascia vuoto per utilizzare il valore predefinito. Esempio: 1rem, 1em, 12px, ...',
    'text_color' => 'Colore del testo',
    'start_date' => 'Data di inizio',
    'end_date' => 'Data di fine',
    'has_action' => 'Ha azione',
    'action_label' => 'Etichetta azione',
    'action_url' => 'URL azione',
    'action_open_new_tab' => 'Apri in nuova scheda',
    'dismissible_label' => 'Consenti all\'utente di chiudere l\'annuncio',
    'placement' => 'Posizionamento',
    'text_alignment' => 'Allineamento del testo',
    'is_active' => 'È attivo',
    'max_width' => 'Larghezza massima',
    'max_width_help' => 'Lascia vuoto per utilizzare il valore predefinito. Esempio: 100%, 500px, ...',
    'max_width_unit' => 'Unità larghezza massima',
    'font_size_unit' => 'Unità dimensione carattere',
    'autoplay_label' => 'Riproduzione automatica',
    'autoplay_delay_label' => 'Ritardo riproduzione automatica',
    'autoplay_delay_help' => 'Il ritardo tra ogni annuncio in millisecondi. Lascia vuoto per utilizzare il valore predefinito (5000).',
    'lazy_loading' => 'Caricamento lazy',
    'lazy_loading_description' => 'Abilita questa opzione per migliorare la velocità di caricamento della pagina',
    'hide_on_mobile' => 'Nascondi su mobile',
    'dismiss' => 'Chiudi',

    // Placeholders and help texts
    'name_placeholder' => 'Inserisci il nome dell\'annuncio',
    'name_help' => 'Nome solo per riferimento interno, non visibile agli utenti',
    'content_placeholder' => 'Inserisci qui il messaggio del tuo annuncio...',
    'content_help' => 'Il messaggio che verrà visualizzato agli utenti. Supporta la formattazione HTML.',
    'start_date_placeholder' => 'Seleziona data e ora di inizio',
    'start_date_help' => 'L\'annuncio sarà visibile da questa data. Lascia vuoto per iniziare immediatamente.',
    'end_date_placeholder' => 'Seleziona data e ora di fine',
    'end_date_help' => 'L\'annuncio verrà nascosto dopo questa data. Lascia vuoto per nessuna scadenza.',
    'has_action_help' => 'Aggiungi un pulsante call-to-action al tuo annuncio',
    'action_label_placeholder' => 'ad es., Scopri di più, Acquista ora',
    'action_label_help' => 'Testo visualizzato sul pulsante azione',
    'action_url_placeholder' => 'https://esempio.com/pagina',
    'action_url_help' => 'URL a cui gli utenti verranno reindirizzati cliccando sul pulsante azione',
    'action_open_new_tab_help' => 'Apri il link dell\'azione in una nuova scheda del browser',
    'is_active_help' => 'Attiva o disattiva questo annuncio senza eliminarlo',
];
