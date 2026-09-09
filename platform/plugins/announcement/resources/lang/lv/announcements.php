<?php

return [
    'name' => 'Paziņojumi',

    'enums' => [
        'announce_placement' => [
            'top' => 'Augšā',
            'bottom' => 'Fiksēts apakšā',
            'popup' => 'Uznirstošais logs',
            'theme' => 'Iebūvēts tēmā',
        ],

        'text_alignment' => [
            'start' => 'Sākums',
            'center' => 'Centrs',
        ],
    ],

    'validation' => [
        'font_size' => 'Fonta lielumam jābūt derīgam CSS fonta lieluma vērtībai.',
        'text_color' => 'Teksta krāsai jābūt derīgai heksadecimālai krāsas vērtībai.',
    ],

    'create' => 'Izveidot jaunu paziņojumu',
    'add_new' => 'Pievienot jaunu',
    'settings' => [
        'name' => 'Paziņojums',
        'description' => 'Pārvaldīt paziņojumu iestatījumus',
    ],

    'background_color' => 'Fona krāsa',
    'font_size' => 'Fonta lielums',
    'font_size_help' => 'Atstājiet tukšu, lai izmantotu noklusējumu. Piemērs: 1rem, 1em, 12px, ...',
    'text_color' => 'Teksta krāsa',
    'start_date' => 'Sākuma datums',
    'end_date' => 'Beigu datums',
    'has_action' => 'Ir darbība',
    'action_label' => 'Darbības etiķete',
    'action_url' => 'Darbības URL',
    'action_open_new_tab' => 'Atvērt jaunā cilnē',
    'dismissible_label' => 'Atļaut lietotājam aizvērt paziņojumu',
    'placement' => 'Novietojums',
    'text_alignment' => 'Teksta izlīdzināšana',
    'is_active' => 'Ir aktīvs',
    'max_width' => 'Maksimālais platums',
    'max_width_help' => 'Atstājiet tukšu, lai izmantotu noklusējuma vērtību. Piemērs: 100%, 500px, ...',
    'max_width_unit' => 'Maksimālā platuma vienība',
    'font_size_unit' => 'Fonta lieluma vienība',
    'autoplay_label' => 'Automātiskā atskaņošana',
    'autoplay_delay_label' => 'Automātiskās atskaņošanas aizkave',
    'autoplay_delay_help' => 'Aizkave starp katru paziņojumu milisekundēs. Atstājiet tukšu, lai izmantotu noklusējuma vērtību (5000).',
    'lazy_loading' => 'Sliņķa ielāde',
    'lazy_loading_description' => 'Iespējojiet šo opciju, lai uzlabotu lapas ielādes ātrumu',
    'hide_on_mobile' => 'Paslēpt mobilajā ierīcē',
    'dismiss' => 'Aizvērt',

    // Placeholders and help texts
    'name_placeholder' => 'Ievadiet paziņojuma nosaukumu',
    'name_help' => 'Nosaukums tikai iekšējai atsaucei, nav redzams lietotājiem',
    'content_placeholder' => 'Ievadiet savu paziņojuma ziņojumu šeit...',
    'content_help' => 'Ziņojums, kas tiks parādīts lietotājiem. Atbalsta HTML formatējumu.',
    'start_date_placeholder' => 'Atlasiet sākuma datumu un laiku',
    'start_date_help' => 'Paziņojums būs redzams no šī datuma. Atstājiet tukšu, lai sāktu nekavējoties.',
    'end_date_placeholder' => 'Atlasiet beigu datumu un laiku',
    'end_date_help' => 'Paziņojums tiks paslēpts pēc šī datuma. Atstājiet tukšu bez derīguma termiņa.',
    'has_action_help' => 'Pievienojiet aicinājuma uz rīcību pogu savam paziņojumam',
    'action_label_placeholder' => 'piem., Uzzināt vairāk, Pirkt tagad',
    'action_label_help' => 'Teksts, kas tiek parādīts uz darbības pogas',
    'action_url_placeholder' => 'https://piemers.lv/lapa',
    'action_url_help' => 'URL, uz kuru lietotāji tiks novirzīti, noklikšķinot uz darbības pogas',
    'action_open_new_tab_help' => 'Atvērt darbības saiti jaunā pārlūka cilnē',
    'is_active_help' => 'Iespējot vai atspējot šo paziņojumu, to nedzēšot',
];
