<?php

return [
    'title' => 'Instalasi',
    'next' => 'Langkah Berikutnya',
    'forms' => [
        'errorTitle' => 'Kesalahan berikut terjadi:',
    ],
    'welcome' => [
        'title' => 'Selamat Datang',
        'message' => 'Sebelum memulai, kami memerlukan beberapa informasi tentang database. Anda perlu mengetahui item berikut sebelum melanjutkan.',
        'language' => 'Bahasa',
        'next' => 'Ayo mulai',
    ],
    'requirements' => [
        'title' => 'Persyaratan Server',
        'php_version_required' => 'Memerlukan PHP versi :version',
    ],
    'permissions' => [
        'next' => 'Konfigurasi Lingkungan',
    ],
    'environment' => [
        'wizard' => [
            'title' => 'Pengaturan Lingkungan',
            'form' => [
                'name_required' => 'Nama lingkungan diperlukan.',
                'app_name_label' => 'Judul situs',
                'app_url_label' => 'URL',
                'db_connection_label' => 'Koneksi Database',
                'db_connection_label_mysql' => 'MySQL',
                'db_host_label' => 'Host database',
                'db_port_label' => 'Port database',
                'db_name_label' => 'Nama database',
                'db_name_placeholder' => 'Nama database',
                'db_username_label' => 'Username database',
                'db_username_placeholder' => 'Username database',
                'db_password_label' => 'Password database',
                'db_password_placeholder' => 'Password database',
                'buttons' => [
                    'install' => 'Instal',
                ],
                'db_host_helper' => 'Jika Anda menggunakan Laravel Sail, cukup ubah DB_HOST menjadi DB_HOST=mysql. Di beberapa hosting DB_HOST bisa berupa localhost bukan 127.0.0.1',
                'db_connections' => [
                    'mysql' => 'MySQL',
                    'sqlite' => 'SQLite',
                    'pgsql' => 'PostgreSQL',
                ],
            ],
        ],
        'success' => 'Pengaturan file .env Anda telah disimpan.',
        'errors' => 'Tidak dapat menyimpan file .env, Silakan buat secara manual.',
    ],
    'theme' => [
        'title' => 'Pilih tema',
        'message' => 'Pilih tema untuk mempersonalisasi tampilan situs web Anda. Pilihan ini juga akan mengimpor data sampel yang disesuaikan dengan tema yang dipilih.',
    ],
    'theme_preset' => [
        'title' => 'Pilih preset tema',
        'message' => 'Pilih preset tema untuk mempersonalisasi tampilan situs web Anda. Pilihan ini juga akan mengimpor data sampel yang disesuaikan dengan tema yang dipilih.',
    ],
    'createAccount' => [
        'title' => 'Buat akun',
        'form' => [
            'first_name' => 'Nama depan',
            'last_name' => 'Nama belakang',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi password',
            'create' => 'Buat',
        ],
    ],
    'license' => [
        'title' => 'Aktifkan Lisensi',
        'skip' => 'Lewati untuk sekarang',
    ],
    'final' => [
        'pageTitle' => 'Instalasi Selesai',
        'title' => 'Selesai',
        'message' => 'Aplikasi telah berhasil diinstal.',
        'exit' => 'Pergi ke dashboard admin',
    ],
    'install_step_title' => 'Instalasi - Langkah :step: :title',
];
