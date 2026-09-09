<?php

return [
    'webhook_secret' => 'Webhook Secret',
    'webhook_secret_helper' => 'Temukan ini di Stripe Dashboard Anda di bawah Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Temukan ini di Stripe Dashboard Anda di bawah Developers > API keys.',
    'secret_key_helper' => 'Temukan ini di Stripe Dashboard Anda di bawah Developers > API keys. Jaga kerahasiaan kunci ini.',
    'webhook_setup_guide' => [
        'title' => 'Panduan Pengaturan Stripe Webhook',
        'description' => 'Ikuti langkah-langkah ini untuk mengatur webhook Stripe',
        'step_1_label' => 'Masuk ke Dashboard Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Pilih event dan konfigurasi endpoint',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Tambahkan endpoint',
        'step_3_description' => 'Klik tombol "Add Endpoint" untuk menyimpan webhook.',
        'step_4_label' => 'Salin signing secret',
        'step_4_description' => 'Salin nilai "Signing Secret" dari bagian "Webhook Details" dan tempelkan ke kolom "Stripe Webhook Secret" di bagian "Stripe" pada tab "Payment" di halaman "Settings".',
    ],
    'no_payment_charge' => 'Tidak ada biaya pembayaran. Silakan coba lagi!',
    'payment_failed' => 'Pembayaran gagal!',
    'payment_type' => 'Tipe Pembayaran',
];
