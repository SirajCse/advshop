<?php

return [
    'name' => 'Ilmoitukset',

    'enums' => [
        'announce_placement' => [
            'top' => 'Yläosa',
            'bottom' => 'Kiinteä alaosassa',
            'popup' => 'Ponnahdusikkuna',
            'theme' => 'Teemaan sisäänrakennettu',
        ],

        'text_alignment' => [
            'start' => 'Alku',
            'center' => 'Keskellä',
        ],
    ],

    'validation' => [
        'font_size' => 'Fontin koon on oltava kelvollinen CSS-fonttikoon arvo.',
        'text_color' => 'Tekstin värin on oltava kelvollinen heksaväri.',
    ],

    'create' => 'Luo uusi ilmoitus',
    'add_new' => 'Lisää uusi',
    'settings' => [
        'name' => 'Ilmoitus',
        'description' => 'Hallitse ilmoitusasetuksia',
    ],

    'background_color' => 'Taustaväri',
    'font_size' => 'Fonttikoko',
    'font_size_help' => 'Jätä tyhjäksi käyttääksesi oletusarvoa. Esimerkki: 1rem, 1em, 12px, ...',
    'text_color' => 'Tekstin väri',
    'start_date' => 'Aloituspäivä',
    'end_date' => 'Päättymispäivä',
    'has_action' => 'On toiminto',
    'action_label' => 'Toiminnon otsikko',
    'action_url' => 'Toiminnon URL',
    'action_open_new_tab' => 'Avaa uudessa välilehdessä',
    'dismissible_label' => 'Salli käyttäjän sulkea ilmoitus',
    'placement' => 'Sijainti',
    'text_alignment' => 'Tekstin tasaus',
    'is_active' => 'On aktiivinen',
    'max_width' => 'Maksimileveys',
    'max_width_help' => 'Jätä tyhjäksi käyttääksesi oletusarvoa. Esimerkki: 100%, 500px, ...',
    'max_width_unit' => 'Maksimileveyden yksikkö',
    'font_size_unit' => 'Fonttikoon yksikkö',
    'autoplay_label' => 'Automaattinen toisto',
    'autoplay_delay_label' => 'Automaattisen toiston viive',
    'autoplay_delay_help' => 'Viive kunkin ilmoituksen välillä millisekunteina. Jätä tyhjäksi käyttääksesi oletusarvoa (5000).',
    'lazy_loading' => 'Laiskalataus',
    'lazy_loading_description' => 'Ota tämä vaihtoehto käyttöön parantaaksesi sivun latausnopeutta',
    'hide_on_mobile' => 'Piilota mobiilissa',
    'dismiss' => 'Sulje',

    // Placeholders and help texts
    'name_placeholder' => 'Syötä ilmoituksen nimi',
    'name_help' => 'Nimi vain sisäiseen viittaukseen, ei näkyvissä käyttäjille',
    'content_placeholder' => 'Syötä ilmoitusviestisi tähän...',
    'content_help' => 'Viesti, joka näytetään käyttäjille. Tukee HTML-muotoilua.',
    'start_date_placeholder' => 'Valitse aloituspäivä ja -aika',
    'start_date_help' => 'Ilmoitus näkyy tästä päivästä lähtien. Jätä tyhjäksi aloittaaksesi välittömästi.',
    'end_date_placeholder' => 'Valitse päättymispäivä ja -aika',
    'end_date_help' => 'Ilmoitus piilotetaan tämän päivän jälkeen. Jätä tyhjäksi, jollei vanhentumista.',
    'has_action_help' => 'Lisää toimintakehotuspainike ilmoitukseesi',
    'action_label_placeholder' => 'esim. Lue lisää, Osta nyt',
    'action_label_help' => 'Toimintopainikkeessa näytettävä teksti',
    'action_url_placeholder' => 'https://esimerkki.fi/sivu',
    'action_url_help' => 'URL-osoite, johon käyttäjät ohjataan toimintopainiketta napsautettaessa',
    'action_open_new_tab_help' => 'Avaa toimintolinkki uudessa selainvälilehdessä',
    'is_active_help' => 'Ota käyttöön tai poista käytöstä tämä ilmoitus poistamatta sitä',
];
