<?php

return [
    'name' => 'Mga Anunsyo',

    'enums' => [
        'announce_placement' => [
            'top' => 'Itaas',
            'bottom' => 'Nakaayos sa ibaba',
            'popup' => 'Popup',
            'theme' => 'Built-in sa tema',
        ],

        'text_alignment' => [
            'start' => 'Simula',
            'center' => 'Gitna',
        ],
    ],

    'validation' => [
        'font_size' => 'Ang laki ng font ay dapat na valid na CSS font size value.',
        'text_color' => 'Ang kulay ng teksto ay dapat na valid na hex color value.',
    ],

    'create' => 'Lumikha ng bagong anunsyo',
    'add_new' => 'Magdagdag ng bago',
    'settings' => [
        'name' => 'Anunsyo',
        'description' => 'Pamahalaan ang mga setting ng anunsyo',
    ],

    'background_color' => 'Kulay ng background',
    'font_size' => 'Laki ng font',
    'font_size_help' => 'Iwanang blangko para gumamit ng default. Halimbawa: 1rem, 1em, 12px, ...',
    'text_color' => 'Kulay ng teksto',
    'start_date' => 'Petsa ng pagsisimula',
    'end_date' => 'Petsa ng pagtatapos',
    'has_action' => 'May aksyon',
    'action_label' => 'Label ng aksyon',
    'action_url' => 'URL ng aksyon',
    'action_open_new_tab' => 'Buksan sa bagong tab',
    'dismissible_label' => 'Payagan ang user na isara ang anunsyo',
    'placement' => 'Lokasyon',
    'text_alignment' => 'Alignment ng teksto',
    'is_active' => 'Aktibo',
    'max_width' => 'Pinakamataas na lapad',
    'max_width_help' => 'Iwanang blangko para gumamit ng default na value. Halimbawa: 100%, 500px, ...',
    'max_width_unit' => 'Unit ng pinakamataas na lapad',
    'font_size_unit' => 'Unit ng laki ng font',
    'autoplay_label' => 'Autoplay',
    'autoplay_delay_label' => 'Pagkaantala ng autoplay',
    'autoplay_delay_help' => 'Ang pagkaantala sa pagitan ng bawat anunsyo sa milliseconds. Iwanang blangko para gumamit ng default na value (5000).',
    'lazy_loading' => 'Lazy loading',
    'lazy_loading_description' => 'I-enable ang opsyong ito upang mapabuti ang bilis ng pag-load ng page',
    'hide_on_mobile' => 'Itago sa mobile',
    'dismiss' => 'Isara',

    // Placeholders and help texts
    'name_placeholder' => 'Ilagay ang pangalan ng anunsyo',
    'name_help' => 'Pangalan para sa internal reference lamang, hindi makikita ng mga user',
    'content_placeholder' => 'Ilagay ang iyong mensahe ng anunsyo dito...',
    'content_help' => 'Ang mensaheng ipapakita sa mga user. Sumusuporta ng HTML formatting.',
    'start_date_placeholder' => 'Pumili ng petsa at oras ng pagsisimula',
    'start_date_help' => 'Ang anunsyo ay makikita mula sa petsang ito. Iwanang blangko para magsimula kaagad.',
    'end_date_placeholder' => 'Pumili ng petsa at oras ng pagtatapos',
    'end_date_help' => 'Ang anunsyo ay ikukubli pagkatapos ng petsang ito. Iwanang blangko para walang expiration.',
    'has_action_help' => 'Magdagdag ng call-to-action button sa iyong anunsyo',
    'action_label_placeholder' => 'hal., Matuto Pa, Bumili Ngayon',
    'action_label_help' => 'Tekstong ipapakita sa action button',
    'action_url_placeholder' => 'https://halimbawa.com/pahina',
    'action_url_help' => 'URL kung saan ire-redirect ang mga user kapag nag-click sa action button',
    'action_open_new_tab_help' => 'Buksan ang action link sa bagong tab ng browser',
    'is_active_help' => 'I-enable o i-disable ang anunsyong ito nang hindi ito binubura',
];
