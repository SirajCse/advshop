<?php

return [
    'stripe_account_id' => 'Stripe ანგარიშის ID',
    'go_to_dashboard' => 'გადადით Express დაფაზე',
    'connect' => [
        'label' => 'დაკავშირება Stripe-თან',
        'description' => 'დააკავშირეთ თქვენი Stripe ანგარიში გადახდების შესაგროვებლად.',
    ],
    'disconnect' => [
        'label' => 'Stripe-ის გათიშვა',
        'confirm' => 'დარწმუნებული ხართ, რომ გსურთ თქვენი Stripe ანგარიშის გათიშვა?',
    ],
    'notifications' => [
        'connected' => 'თქვენი Stripe ანგარიში დაკავშირებულია.',
        'disconnected' => 'თქვენი Stripe ანგარიში გათიშულია.',
        'now_active' => 'თქვენი Stripe ანგარიში ახლა აქტიურია.',
    ],
    'withdrawal' => [
        'payout_info' => 'თქვენი გადახდა ავტომატურად გადაირიცხება თქვენს Stripe ანგარიშზე ID-ით: :stripe_account_id.',
    ],
];
