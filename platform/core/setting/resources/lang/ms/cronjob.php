<?php

return [
    'name' => 'Cronjob',
    'description' => 'Sediakan tugas latar belakang automatik untuk memastikan laman web anda berjalan lancar.',
    'is_not_ready' => 'Cronjob belum dikonfigurasi',
    'is_not_ready_description' => 'Sila ikut arahan di bawah untuk menyediakan cronjob. Ini diperlukan untuk ciri-ciri seperti peringatan troli terbiar, penjadualan e-mel, dan tugas automatik lain.',
    'is_working' => 'Cronjob berjalan dengan baik!',
    'is_not_working' => 'Cronjob telah berhenti berfungsi',
    'is_not_working_description' => 'Cronjob tidak berjalan dalam 10 minit terakhir. Sila semak konfigurasi pelayan anda atau hubungi pembekal hosting.',
    'last_checked' => 'Aktiviti terakhir: :time',
    'copy_button' => 'Salin arahan',
    'what_is' => [
        'title' => 'Apa itu Cronjob?',
        'description' => 'Cronjob adalah tugas automatik yang berjalan di latar belakang pelayan anda. Ia membolehkan laman web anda melakukan tugas penting secara automatik tanpa perlu melakukan apa-apa secara manual.',
        'examples' => 'Contoh',
        'features' => 'Menghantar peringatan troli terbiar, memproses e-mel berjadual, membersihkan data lama, menjana laporan, dan banyak lagi.',
    ],
    'command' => [
        'title' => 'Arahan Cronjob Anda',
        'description' => 'Salin arahan ini dan tambahkan ke panel kawalan hosting anda. Arahan ini perlu dijalankan setiap minit untuk memastikan tugas automatik anda berfungsi.',
    ],
    'setup' => [
        'name' => 'Cara menyediakan',
        'copied' => 'Disalin ke papan keratan!',
        'choose_hosting' => 'Pilih panel kawalan hosting anda di bawah dan ikuti arahan langkah demi langkah:',
    ],
    'cpanel' => [
        'step1' => 'Log masuk ke akaun <strong>cPanel</strong> anda',
        'step2' => 'Cari dan klik pada <strong>"Cron Jobs"</strong> di bahagian "Advanced"',
        'step3' => 'Di bawah "Add New Cron Job", pilih <strong>"Once Per Minute (* * * * *)"</strong> dari menu lungsur',
        'step4' => '<strong>Tampal arahan</strong> yang anda salin di atas ke medan "Command"',
        'step5' => 'Klik <strong>"Add New Cron Job"</strong> untuk menyimpan',
    ],
    'plesk' => [
        'step1' => 'Log masuk ke panel kawalan <strong>Plesk</strong> anda',
        'step2' => 'Pergi ke <strong>"Scheduled Tasks"</strong> (atau "Cron Jobs")',
        'step3' => 'Klik <strong>"Add Task"</strong> atau <strong>"Schedule a Task"</strong>',
        'step4' => 'Tetapkan jadual untuk berjalan <strong>setiap minit</strong> dan tampal arahan',
        'step5' => 'Klik <strong>"OK"</strong> atau <strong>"Apply"</strong> untuk menyimpan',
    ],
    'directadmin' => [
        'step1' => 'Log masuk ke panel <strong>DirectAdmin</strong> anda',
        'step2' => 'Navigasi ke <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klik <strong>"Add Cron Job"</strong>',
        'step4' => 'Tetapkan semua medan masa ke <strong>*</strong> (setiap minit) dan tampal arahan',
        'step5' => 'Klik <strong>"Add"</strong> untuk menyimpan cronjob',
    ],
    'ssh' => [
        'step1' => 'Sambung ke pelayan anda melalui <strong>SSH</strong> menggunakan Terminal atau PuTTY',
        'step2' => 'Taip <code>crontab -e</code> dan tekan Enter untuk mengedit fail crontab',
        'step3' => 'Tambah baris baru di bawah dan <strong>tampal arahan</strong>',
        'step4' => 'Tekan <strong>Ctrl+X</strong>, kemudian <strong>Y</strong>, kemudian <strong>Enter</strong> untuk menyimpan (untuk editor nano)',
        'step5' => 'Cronjob kini aktif dan akan berjalan setiap minit',
    ],
    'need_help' => 'Perlukan bantuan? Hubungi pembekal hosting anda dan minta mereka menyediakan cronjob yang berjalan setiap minit dengan arahan yang ditunjukkan di atas.',
];
