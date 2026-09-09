<?php

return [
    'webhook_secret' => 'Rahsia Webhook',
    'webhook_secret_helper' => 'Cari ini dalam Stripe Dashboard anda di bawah Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Cari ini dalam Stripe Dashboard anda di bawah Developers > API keys.',
    'secret_key_helper' => 'Cari ini dalam Stripe Dashboard anda di bawah Developers > API keys. Simpan kunci ini dengan sulit.',
    'webhook_setup_guide' => [
        'title' => 'Panduan Persediaan Stripe Webhook',
        'description' => 'Ikuti langkah-langkah ini untuk menyediakan webhook Stripe',
        'step_1_label' => 'Log masuk ke Papan Pemuka Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Pilih acara dan konfigurasikan endpoint',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Tambah endpoint',
        'step_3_description' => 'Klik butang "Tambah Endpoint" untuk menyimpan webhook.',
        'step_4_label' => 'Salin rahsia tandatangan',
        'step_4_description' => 'Salin nilai "Signing Secret" dari bahagian "Webhook Details" dan tampalkan ke dalam medan "Stripe Webhook Secret" di bahagian "Stripe" pada tab "Pembayaran" di halaman "Tetapan".',
    ],
    'no_payment_charge' => 'Tiada caj pembayaran. Sila cuba lagi!',
    'payment_failed' => 'Pembayaran gagal!',
    'payment_type' => 'Jenis Pembayaran',
];
