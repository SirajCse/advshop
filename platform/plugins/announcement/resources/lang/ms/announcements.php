<?php

return [
    'name' => 'Pengumuman',

    'enums' => [
        'announce_placement' => [
            'top' => 'Atas',
            'bottom' => 'Tetap di bawah',
            'popup' => 'Popup',
            'theme' => 'Terbina dalam tema',
        ],

        'text_alignment' => [
            'start' => 'Mula',
            'center' => 'Tengah',
        ],
    ],

    'validation' => [
        'font_size' => 'Saiz fon mestilah nilai saiz fon CSS yang sah.',
        'text_color' => 'Warna teks mestilah nilai warna hex yang sah.',
    ],

    'create' => 'Cipta pengumuman baharu',
    'add_new' => 'Tambah baharu',
    'settings' => [
        'name' => 'Pengumuman',
        'description' => 'Urus tetapan pengumuman',
    ],

    'background_color' => 'Warna latar belakang',
    'font_size' => 'Saiz fon',
    'font_size_help' => 'Biarkan kosong untuk menggunakan lalai. Contoh: 1rem, 1em, 12px, ...',
    'text_color' => 'Warna teks',
    'start_date' => 'Tarikh mula',
    'end_date' => 'Tarikh tamat',
    'has_action' => 'Mempunyai tindakan',
    'action_label' => 'Label tindakan',
    'action_url' => 'URL tindakan',
    'action_open_new_tab' => 'Buka dalam tab baharu',
    'dismissible_label' => 'Benarkan pengguna tutup pengumuman',
    'placement' => 'Penempatan',
    'text_alignment' => 'Penjajaran teks',
    'is_active' => 'Aktif',
    'max_width' => 'Lebar maksimum',
    'max_width_help' => 'Biarkan kosong untuk menggunakan nilai lalai. Contoh: 100%, 500px, ...',
    'max_width_unit' => 'Unit lebar maksimum',
    'font_size_unit' => 'Unit saiz fon',
    'autoplay_label' => 'Main automatik',
    'autoplay_delay_label' => 'Kelewatan main automatik',
    'autoplay_delay_help' => 'Kelewatan antara setiap pengumuman dalam milisaat. Biarkan kosong untuk menggunakan nilai lalai (5000).',
    'lazy_loading' => 'Pemuatan malas',
    'lazy_loading_description' => 'Dayakan pilihan ini untuk meningkatkan kelajuan pemuatan halaman',
    'hide_on_mobile' => 'Sembunyikan pada mudah alih',
    'dismiss' => 'Tutup',

    // Placeholders and help texts
    'name_placeholder' => 'Masukkan nama pengumuman',
    'name_help' => 'Nama untuk rujukan dalaman sahaja, tidak kelihatan kepada pengguna',
    'content_placeholder' => 'Masukkan mesej pengumuman anda di sini...',
    'content_help' => 'Mesej yang akan dipaparkan kepada pengguna. Menyokong pemformatan HTML.',
    'start_date_placeholder' => 'Pilih tarikh dan masa mula',
    'start_date_help' => 'Pengumuman akan kelihatan dari tarikh ini. Biarkan kosong untuk mula serta-merta.',
    'end_date_placeholder' => 'Pilih tarikh dan masa tamat',
    'end_date_help' => 'Pengumuman akan disembunyikan selepas tarikh ini. Biarkan kosong untuk tiada tamat tempoh.',
    'has_action_help' => 'Tambahkan butang seruan tindakan pada pengumuman anda',
    'action_label_placeholder' => 'cth., Ketahui Lebih Lanjut, Beli Sekarang',
    'action_label_help' => 'Teks yang dipaparkan pada butang tindakan',
    'action_url_placeholder' => 'https://contoh.com/halaman',
    'action_url_help' => 'URL di mana pengguna akan diubah hala apabila mengklik butang tindakan',
    'action_open_new_tab_help' => 'Buka pautan tindakan dalam tab pelayar baharu',
    'is_active_help' => 'Dayakan atau lumpuhkan pengumuman ini tanpa memadamkannya',
];
