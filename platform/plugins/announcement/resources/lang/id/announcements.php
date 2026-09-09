<?php

return [
    'name' => 'Pengumuman',

    'enums' => [
        'announce_placement' => [
            'top' => 'Atas',
            'bottom' => 'Tetap di bawah',
            'popup' => 'Popup',
            'theme' => 'Bawaan tema',
        ],

        'text_alignment' => [
            'start' => 'Awal',
            'center' => 'Tengah',
        ],
    ],

    'validation' => [
        'font_size' => 'Ukuran font harus berupa nilai ukuran font CSS yang valid.',
        'text_color' => 'Warna teks harus berupa nilai warna hex yang valid.',
    ],

    'create' => 'Buat pengumuman baru',
    'add_new' => 'Tambah baru',
    'settings' => [
        'name' => 'Pengumuman',
        'description' => 'Kelola pengaturan pengumuman',
    ],

    'background_color' => 'Warna latar belakang',
    'font_size' => 'Ukuran font',
    'font_size_help' => 'Biarkan kosong untuk menggunakan default. Contoh: 1rem, 1em, 12px, ...',
    'text_color' => 'Warna teks',
    'start_date' => 'Tanggal mulai',
    'end_date' => 'Tanggal berakhir',
    'has_action' => 'Memiliki tindakan',
    'action_label' => 'Label tindakan',
    'action_url' => 'URL tindakan',
    'action_open_new_tab' => 'Buka di tab baru',
    'dismissible_label' => 'Izinkan pengguna menutup pengumuman',
    'placement' => 'Penempatan',
    'text_alignment' => 'Perataan teks',
    'is_active' => 'Aktif',
    'max_width' => 'Lebar maksimal',
    'max_width_help' => 'Biarkan kosong untuk menggunakan nilai default. Contoh: 100%, 500px, ...',
    'max_width_unit' => 'Satuan lebar maksimal',
    'font_size_unit' => 'Satuan ukuran font',
    'autoplay_label' => 'Putar otomatis',
    'autoplay_delay_label' => 'Penundaan putar otomatis',
    'autoplay_delay_help' => 'Penundaan antara setiap pengumuman dalam milidetik. Biarkan kosong untuk menggunakan nilai default (5000).',
    'lazy_loading' => 'Lazy loading',
    'lazy_loading_description' => 'Aktifkan opsi ini untuk meningkatkan kecepatan pemuatan halaman',
    'hide_on_mobile' => 'Sembunyikan di mobile',
    'dismiss' => 'Tutup',

    // Placeholders and help texts
    'name_placeholder' => 'Masukkan nama pengumuman',
    'name_help' => 'Nama hanya untuk referensi internal, tidak terlihat oleh pengguna',
    'content_placeholder' => 'Masukkan pesan pengumuman Anda di sini...',
    'content_help' => 'Pesan yang akan ditampilkan kepada pengguna. Mendukung format HTML.',
    'start_date_placeholder' => 'Pilih tanggal dan waktu mulai',
    'start_date_help' => 'Pengumuman akan terlihat mulai tanggal ini. Biarkan kosong untuk mulai segera.',
    'end_date_placeholder' => 'Pilih tanggal dan waktu berakhir',
    'end_date_help' => 'Pengumuman akan disembunyikan setelah tanggal ini. Biarkan kosong untuk tanpa kedaluwarsa.',
    'has_action_help' => 'Tambahkan tombol call-to-action ke pengumuman Anda',
    'action_label_placeholder' => 'misalnya, Pelajari Lebih Lanjut, Belanja Sekarang',
    'action_label_help' => 'Teks yang ditampilkan pada tombol tindakan',
    'action_url_placeholder' => 'https://contoh.com/halaman',
    'action_url_help' => 'URL ke mana pengguna akan diarahkan saat mengklik tombol tindakan',
    'action_open_new_tab_help' => 'Buka tautan tindakan di tab browser baru',
    'is_active_help' => 'Aktifkan atau nonaktifkan pengumuman ini tanpa menghapusnya',
];
