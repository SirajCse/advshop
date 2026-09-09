<?php

return [
    'name' => 'Duyurular',

    'enums' => [
        'announce_placement' => [
            'top' => 'Üst',
            'bottom' => 'Altta sabit',
            'popup' => 'Açılır pencere',
            'theme' => 'Temaya yerleşik',
        ],

        'text_alignment' => [
            'start' => 'Başlangıç',
            'center' => 'Orta',
        ],
    ],

    'validation' => [
        'font_size' => 'Yazı tipi boyutu geçerli bir CSS yazı tipi boyutu değeri olmalıdır.',
        'text_color' => 'Metin rengi geçerli bir onaltılık renk değeri olmalıdır.',
    ],

    'create' => 'Yeni duyuru oluştur',
    'add_new' => 'Yeni ekle',
    'settings' => [
        'name' => 'Duyuru',
        'description' => 'Duyuru ayarlarını yönet',
    ],

    'background_color' => 'Arka plan rengi',
    'font_size' => 'Yazı tipi boyutu',
    'font_size_help' => 'Varsayılanı kullanmak için boş bırakın. Örnek: 1rem, 1em, 12px, ...',
    'text_color' => 'Metin rengi',
    'start_date' => 'Başlangıç tarihi',
    'end_date' => 'Bitiş tarihi',
    'has_action' => 'Eylem var',
    'action_label' => 'Eylem etiketi',
    'action_url' => 'Eylem URL\'si',
    'action_open_new_tab' => 'Yeni sekmede aç',
    'dismissible_label' => 'Kullanıcının duyuruyu kapatmasına izin ver',
    'placement' => 'Yerleşim',
    'text_alignment' => 'Metin hizalama',
    'is_active' => 'Aktif',
    'max_width' => 'Maksimum genişlik',
    'max_width_help' => 'Varsayılan değeri kullanmak için boş bırakın. Örnek: 100%, 500px, ...',
    'max_width_unit' => 'Maksimum genişlik birimi',
    'font_size_unit' => 'Yazı tipi boyutu birimi',
    'autoplay_label' => 'Otomatik oynatma',
    'autoplay_delay_label' => 'Otomatik oynatma gecikmesi',
    'autoplay_delay_help' => 'Her duyuru arasındaki milisaniye cinsinden gecikme. Varsayılan değeri kullanmak için boş bırakın (5000).',
    'lazy_loading' => 'Tembel yükleme',
    'lazy_loading_description' => 'Sayfa yükleme hızını artırmak için bu seçeneği etkinleştirin',
    'hide_on_mobile' => 'Mobilde gizle',
    'dismiss' => 'Kapat',

    // Placeholders and help texts
    'name_placeholder' => 'Duyuru adını girin',
    'name_help' => 'Sadece dahili referans için ad, kullanıcılar tarafından görünmez',
    'content_placeholder' => 'Duyuru mesajınızı buraya girin...',
    'content_help' => 'Kullanıcılara görüntülenecek mesaj. HTML biçimlendirmesini destekler.',
    'start_date_placeholder' => 'Başlangıç tarihini ve saatini seçin',
    'start_date_help' => 'Duyuru bu tarihten itibaren görünür olacaktır. Hemen başlamak için boş bırakın.',
    'end_date_placeholder' => 'Bitiş tarihini ve saatini seçin',
    'end_date_help' => 'Duyuru bu tarihten sonra gizlenecektir. Süre sonu olmaması için boş bırakın.',
    'has_action_help' => 'Duyurunuza bir harekete geçirici mesaj düğmesi ekleyin',
    'action_label_placeholder' => 'örn., Daha Fazla Bilgi, Şimdi Satın Al',
    'action_label_help' => 'Eylem düğmesinde görüntülenen metin',
    'action_url_placeholder' => 'https://ornek.com/sayfa',
    'action_url_help' => 'Eylem düğmesine tıklandığında kullanıcıların yönlendirileceği URL',
    'action_open_new_tab_help' => 'Eylem bağlantısını yeni bir tarayıcı sekmesinde aç',
    'is_active_help' => 'Bu duyuruyu silmeden etkinleştirin veya devre dışı bırakın',
];
