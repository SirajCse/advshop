<?php

return [
    'payment_description' => 'Pelanggan dapat membeli produk dan membayar langsung menggunakan Visa, Kartu kredit melalui :name',
    'api_key' => 'Kunci API',
    'api_key_helper' => 'Dapatkan kunci API Anda dari Dashboard Mollie',
    'webhook_secret' => 'Webhook Secret (Opsional)',
    'webhook_secret_helper' => 'Opsional: Tambahkan webhook secret untuk keamanan yang lebih baik. Konfigurasikan ini di Dashboard Mollie Anda di bawah Developers > Webhooks',
    'register_account' => 'Daftarkan akun di :name',
    'after_registration' => 'Setelah pendaftaran di :name, Anda akan memiliki kunci API',
    'enter_api_key' => 'Masukkan kunci API ke dalam kotak di sebelah kanan',
    'webhook_configuration' => 'Konfigurasi Webhook:',
    'webhook_url_instruction' => 'Di Dashboard Mollie Anda, konfigurasikan URL webhook sebagai:',
    'webhook_note' => 'Catatan: Ganti {token} dengan token pembayaran yang sebenarnya. Webhook akan dipanggil secara otomatis oleh Mollie untuk memperbarui status pembayaran.',
    'security_optional' => 'Keamanan (Opsional):',
    'security_instruction' => 'Untuk keamanan yang lebih baik, Anda dapat mengkonfigurasi webhook secret di Dashboard Mollie Anda di bawah Developers > Webhooks, kemudian masukkan ke dalam field Webhook Secret.',
];
