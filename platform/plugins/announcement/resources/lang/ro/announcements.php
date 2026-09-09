<?php

return [
    'name' => 'Anunțuri',

    'enums' => [
        'announce_placement' => [
            'top' => 'Sus',
            'bottom' => 'Fix jos',
            'popup' => 'Popup',
            'theme' => 'Integrat în temă',
        ],

        'text_alignment' => [
            'start' => 'Început',
            'center' => 'Centru',
        ],
    ],

    'validation' => [
        'font_size' => 'Dimensiunea fontului trebuie să fie o valoare validă de dimensiune font CSS.',
        'text_color' => 'Culoarea textului trebuie să fie o valoare de culoare hexadecimală validă.',
    ],

    'create' => 'Creează anunț nou',
    'add_new' => 'Adaugă nou',
    'settings' => [
        'name' => 'Anunț',
        'description' => 'Gestionează setările anunțurilor',
    ],

    'background_color' => 'Culoarea fundalului',
    'font_size' => 'Dimensiunea fontului',
    'font_size_help' => 'Lasă gol pentru a folosi valoarea implicită. Exemplu: 1rem, 1em, 12px, ...',
    'text_color' => 'Culoarea textului',
    'start_date' => 'Data de început',
    'end_date' => 'Data de sfârșit',
    'has_action' => 'Are acțiune',
    'action_label' => 'Eticheta acțiunii',
    'action_url' => 'URL-ul acțiunii',
    'action_open_new_tab' => 'Deschide în filă nouă',
    'dismissible_label' => 'Permite utilizatorului să închidă anunțul',
    'placement' => 'Plasament',
    'text_alignment' => 'Alinierea textului',
    'is_active' => 'Este activ',
    'max_width' => 'Lățimea maximă',
    'max_width_help' => 'Lasă gol pentru a folosi valoarea implicită. Exemplu: 100%, 500px, ...',
    'max_width_unit' => 'Unitatea lățimii maxime',
    'font_size_unit' => 'Unitatea dimensiunii fontului',
    'autoplay_label' => 'Redare automată',
    'autoplay_delay_label' => 'Întârzierea redării automate',
    'autoplay_delay_help' => 'Întârzierea între fiecare anunț în milisecunde. Lasă gol pentru a folosi valoarea implicită (5000).',
    'lazy_loading' => 'Încărcare leneșă',
    'lazy_loading_description' => 'Activează această opțiune pentru a îmbunătăți viteza de încărcare a paginii',
    'hide_on_mobile' => 'Ascunde pe mobil',
    'dismiss' => 'Închide',

    // Placeholders and help texts
    'name_placeholder' => 'Introduceți numele anunțului',
    'name_help' => 'Nume doar pentru referință internă, invizibil pentru utilizatori',
    'content_placeholder' => 'Introduceți mesajul anunțului aici...',
    'content_help' => 'Mesajul care va fi afișat utilizatorilor. Suportă formatare HTML.',
    'start_date_placeholder' => 'Selectați data și ora de început',
    'start_date_help' => 'Anunțul va fi vizibil de la această dată. Lasă gol pentru a începe imediat.',
    'end_date_placeholder' => 'Selectați data și ora de sfârșit',
    'end_date_help' => 'Anunțul va fi ascuns după această dată. Lasă gol pentru fără expirare.',
    'has_action_help' => 'Adaugă un buton de îndemn la acțiune la anunțul tău',
    'action_label_placeholder' => 'de ex., Află mai multe, Cumpără acum',
    'action_label_help' => 'Text afișat pe butonul de acțiune',
    'action_url_placeholder' => 'https://exemplu.ro/pagina',
    'action_url_help' => 'URL unde utilizatorii vor fi redirecționați când dau clic pe butonul de acțiune',
    'action_open_new_tab_help' => 'Deschide linkul acțiunii într-o filă nouă de browser',
    'is_active_help' => 'Activează sau dezactivează acest anunț fără a-l șterge',
];
