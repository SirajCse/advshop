<?php

return [
    'webhook_secret' => 'Webhook Gizli',
    'webhook_secret_helper' => 'Bunu Stripe Dashboard\'unuzda Developers > Webhooks > Signing secret altında bulabilirsiniz.',
    'public_key_helper' => 'Bunu Stripe Dashboard\'unuzda Developers > API keys altında bulabilirsiniz.',
    'secret_key_helper' => 'Bunu Stripe Dashboard\'unuzda Developers > API keys altında bulabilirsiniz. Bu anahtarı gizli tutun.',
    'webhook_setup_guide' => [
        'title' => 'Stripe Webhook Kurulum Kılavuzu',
        'description' => 'Stripe webhook\'u kurmak için şu adımları izleyin',
        'step_1_label' => 'Stripe Pano\'ya giriş yapın',
        'step_1_description' => ':link\'e erişin ve Geliştiriciler sekmesinin Webhooks bölümündeki Uç Nokta Ekle düğmesine tıklayın.',
        'step_2_label' => 'Olayı Seçin ve Uç Noktayı Yapılandırın',
        'step_2_description' => 'payment_intent.succeeded olayını seçin ve Uç Nokta URL alanına aşağıdaki URL\'yi girin: :url',
        'step_3_label' => 'Son Nokta Ekle',
        'step_3_description' => 'Webhook\'u kaydetmek için Uç Nokta Ekle düğmesine tıklayın.',
        'step_4_label' => 'İmzalama Gizli Kopyası',
        'step_4_description' => 'Webhook Ayrıntıları bölümünden İmzalama Gizli Değeri değerini kopyalayın ve Ayarlar sayfasındaki Ödeme sekmesinin Stripe bölümündeki Stripe Webhook Gizli Değeri alanına yapıştırın.',
    ],
    'no_payment_charge' => 'Ödeme ücreti yok. Lütfen tekrar deneyin!',
    'payment_failed' => 'Ödeme başarısız oldu!',
    'payment_type' => 'Ödeme Türü',
];
