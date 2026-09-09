<?php

return [
    'name' => 'Cronjob',
    'description' => 'Atur tugas latar belakang otomatis untuk menjaga website Anda berjalan lancar.',
    'is_not_ready' => 'Cronjob belum dikonfigurasi',
    'is_not_ready_description' => 'Silakan ikuti petunjuk di bawah untuk mengatur cronjob. Ini diperlukan untuk fitur seperti pengingat keranjang belanja yang ditinggalkan, penjadwalan email, dan tugas otomatis lainnya.',
    'is_working' => 'Cronjob berjalan dengan benar!',
    'is_not_working' => 'Cronjob berhenti bekerja',
    'is_not_working_description' => 'Cronjob tidak berjalan dalam 10 menit terakhir. Silakan periksa konfigurasi server Anda atau hubungi penyedia hosting.',
    'last_checked' => 'Aktivitas terakhir: :time',
    'copy_button' => 'Salin perintah',
    'what_is' => [
        'title' => 'Apa itu Cronjob?',
        'description' => 'Cronjob adalah tugas otomatis yang berjalan di latar belakang server Anda. Ini memungkinkan website Anda melakukan tugas penting secara otomatis tanpa harus melakukan apa pun secara manual.',
        'examples' => 'Contoh',
        'features' => 'Mengirim pengingat keranjang yang ditinggalkan, memproses email terjadwal, membersihkan data lama, membuat laporan, dan lainnya.',
    ],
    'command' => [
        'title' => 'Perintah Cronjob Anda',
        'description' => 'Salin perintah ini dan tambahkan ke panel kontrol hosting Anda. Perintah ini perlu dijalankan setiap menit agar tugas otomatis Anda berfungsi.',
    ],
    'setup' => [
        'name' => 'Cara mengatur',
        'copied' => 'Disalin ke clipboard!',
        'choose_hosting' => 'Pilih panel kontrol hosting Anda di bawah dan ikuti petunjuk langkah demi langkah:',
    ],
    'cpanel' => [
        'step1' => 'Masuk ke akun <strong>cPanel</strong> Anda',
        'step2' => 'Temukan dan klik <strong>"Cron Jobs"</strong> di bagian "Advanced"',
        'step3' => 'Di bawah "Add New Cron Job", pilih <strong>"Once Per Minute (* * * * *)"</strong> dari dropdown waktu',
        'step4' => '<strong>Tempel perintah</strong> yang Anda salin di atas ke kolom "Command"',
        'step5' => 'Klik <strong>"Add New Cron Job"</strong> untuk menyimpan',
    ],
    'plesk' => [
        'step1' => 'Masuk ke panel kontrol <strong>Plesk</strong> Anda',
        'step2' => 'Buka <strong>"Scheduled Tasks"</strong> (atau "Cron Jobs")',
        'step3' => 'Klik <strong>"Add Task"</strong> atau <strong>"Schedule a Task"</strong>',
        'step4' => 'Atur jadwal untuk berjalan <strong>setiap menit</strong> dan tempel perintah',
        'step5' => 'Klik <strong>"OK"</strong> atau <strong>"Apply"</strong> untuk menyimpan',
    ],
    'directadmin' => [
        'step1' => 'Masuk ke panel <strong>DirectAdmin</strong> Anda',
        'step2' => 'Navigasi ke <strong>"Advanced Features"</strong> → <strong>"Cron Jobs"</strong>',
        'step3' => 'Klik <strong>"Add Cron Job"</strong>',
        'step4' => 'Atur semua kolom waktu ke <strong>*</strong> (setiap menit) dan tempel perintah',
        'step5' => 'Klik <strong>"Add"</strong> untuk menyimpan cronjob',
    ],
    'ssh' => [
        'step1' => 'Hubungkan ke server Anda melalui <strong>SSH</strong> menggunakan Terminal atau PuTTY',
        'step2' => 'Ketik <code>crontab -e</code> dan tekan Enter untuk mengedit file crontab',
        'step3' => 'Tambahkan baris baru di bawah dan <strong>tempel perintah</strong>',
        'step4' => 'Tekan <strong>Ctrl+X</strong>, lalu <strong>Y</strong>, lalu <strong>Enter</strong> untuk menyimpan (untuk editor nano)',
        'step5' => 'Cronjob sekarang aktif dan akan berjalan setiap menit',
    ],
    'need_help' => 'Butuh bantuan? Hubungi penyedia hosting Anda dan minta mereka mengatur cronjob yang berjalan setiap menit dengan perintah yang ditampilkan di atas.',
];
