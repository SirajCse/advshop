<?php

return [
    'name' => 'Ogłoszenia',

    'enums' => [
        'announce_placement' => [
            'top' => 'Góra',
            'bottom' => 'Stałe na dole',
            'popup' => 'Wyskakujące okienko',
            'theme' => 'Wbudowane w motyw',
        ],

        'text_alignment' => [
            'start' => 'Początek',
            'center' => 'Środek',
        ],
    ],

    'validation' => [
        'font_size' => 'Rozmiar czcionki musi być prawidłową wartością rozmiaru czcionki CSS.',
        'text_color' => 'Kolor tekstu musi być prawidłową wartością koloru szesnastkowego.',
    ],

    'create' => 'Utwórz nowe ogłoszenie',
    'add_new' => 'Dodaj nowe',
    'settings' => [
        'name' => 'Ogłoszenie',
        'description' => 'Zarządzaj ustawieniami ogłoszeń',
    ],

    'background_color' => 'Kolor tła',
    'font_size' => 'Rozmiar czcionki',
    'font_size_help' => 'Zostaw puste, aby użyć domyślnego. Przykład: 1rem, 1em, 12px, ...',
    'text_color' => 'Kolor tekstu',
    'start_date' => 'Data rozpoczęcia',
    'end_date' => 'Data zakończenia',
    'has_action' => 'Ma akcję',
    'action_label' => 'Etykieta akcji',
    'action_url' => 'URL akcji',
    'action_open_new_tab' => 'Otwórz w nowej karcie',
    'dismissible_label' => 'Zezwól użytkownikowi na zamknięcie ogłoszenia',
    'placement' => 'Umieszczenie',
    'text_alignment' => 'Wyrównanie tekstu',
    'is_active' => 'Jest aktywne',
    'max_width' => 'Maksymalna szerokość',
    'max_width_help' => 'Zostaw puste, aby użyć wartości domyślnej. Przykład: 100%, 500px, ...',
    'max_width_unit' => 'Jednostka maksymalnej szerokości',
    'font_size_unit' => 'Jednostka rozmiaru czcionki',
    'autoplay_label' => 'Automatyczne odtwarzanie',
    'autoplay_delay_label' => 'Opóźnienie automatycznego odtwarzania',
    'autoplay_delay_help' => 'Opóźnienie między każdym ogłoszeniem w milisekundach. Zostaw puste, aby użyć wartości domyślnej (5000).',
    'lazy_loading' => 'Leniwe ładowanie',
    'lazy_loading_description' => 'Włącz tę opcję, aby poprawić szybkość ładowania strony',
    'hide_on_mobile' => 'Ukryj na urządzeniach mobilnych',
    'dismiss' => 'Zamknij',

    // Placeholders and help texts
    'name_placeholder' => 'Wprowadź nazwę ogłoszenia',
    'name_help' => 'Nazwa tylko do celów wewnętrznych, niewidoczna dla użytkowników',
    'content_placeholder' => 'Wprowadź treść ogłoszenia tutaj...',
    'content_help' => 'Wiadomość, która będzie wyświetlana użytkownikom. Obsługuje formatowanie HTML.',
    'start_date_placeholder' => 'Wybierz datę i godzinę rozpoczęcia',
    'start_date_help' => 'Ogłoszenie będzie widoczne od tej daty. Zostaw puste, aby rozpocząć natychmiast.',
    'end_date_placeholder' => 'Wybierz datę i godzinę zakończenia',
    'end_date_help' => 'Ogłoszenie zostanie ukryte po tej dacie. Zostaw puste dla braku wygaśnięcia.',
    'has_action_help' => 'Dodaj przycisk wezwania do działania do swojego ogłoszenia',
    'action_label_placeholder' => 'np. Dowiedz się więcej, Kup teraz',
    'action_label_help' => 'Tekst wyświetlany na przycisku akcji',
    'action_url_placeholder' => 'https://przyklad.pl/strona',
    'action_url_help' => 'URL, do którego użytkownicy zostaną przekierowani po kliknięciu przycisku akcji',
    'action_open_new_tab_help' => 'Otwórz link akcji w nowej karcie przeglądarki',
    'is_active_help' => 'Włącz lub wyłącz to ogłoszenie bez jego usuwania',
];
