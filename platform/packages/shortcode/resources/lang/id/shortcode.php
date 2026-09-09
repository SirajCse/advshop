<?php

return [
    'shortcode' => 'Shortcode',
    'use' => 'Gunakan',
    'ui-blocks' => 'Blok UI',
    'search' => 'Cari...',
    'no_shortcode_found' => 'Shortcode tidak ditemukan.',
    'shortcode_not_available' => 'Shortcode ":name" tidak tersedia atau telah dihapus.',

    'cache_suggestion' => [
        'title' => 'Saran Performa',
        'description' => 'Anda dapat meningkatkan performa situs dengan mengaktifkan cache shortcode.',
        'benefits' => 'Ini dapat secara signifikan mengurangi waktu muat halaman dengan menyimpan shortcode yang dirender dalam cache.',
        'enable_button' => 'Aktifkan cache shortcode',
        'dismiss_button' => 'Abaikan selama seminggu',
    ],
    'form' => [
        'enable_lazy_loading' => 'Aktifkan lazy loading',
        'no' => 'Tidak',
        'yes' => 'Ya',
        'lazy_loading_helper' => 'Ketika diaktifkan, konten shortcode akan dimuat secara bertahap saat halaman dimuat, bukan sekaligus. Ini dapat membantu meningkatkan waktu muat halaman.',
        'enable_caching' => 'Aktifkan caching',
        'caching_helper' => 'Jika diaktifkan, konten shortcode ini akan di-cache untuk meningkatkan performa. Nonaktifkan untuk konten dinamis yang sering berubah.',
        'cache_disabled_notice' => 'Karena masalah antarmuka, cache untuk blok UI ini dinonaktifkan melalui kode. Shortcode ini tidak akan di-cache meskipun caching diaktifkan.',
        'lazy_loading_disabled_notice' => 'Lazy loading untuk blok UI ini dinonaktifkan melalui kode. Shortcode ini tidak akan menggunakan lazy loading meskipun diaktifkan.',
        'custom_css' => 'CSS kustom (opsional)',
        'custom_css_helper' => 'Silakan masukkan kode CSS Anda dalam satu baris. Tidak akan berfungsi jika memiliki baris baru. Beberapa karakter khusus mungkin di-escape.',
        'background_color' => 'Warna latar belakang (opsional)',
        'text_color' => 'Warna teks (opsional)',
        'text_color_helper' => 'Warna ini mungkin ditimpa oleh tema. Jika tidak berfungsi, silakan tambahkan CSS Anda di Tampilan -> CSS Kustom.',
        'background_image' => 'Gambar latar (opsional)',
        'quantity' => 'Jumlah',
        'tab_number' => 'Tab #:number',
    ],
];
