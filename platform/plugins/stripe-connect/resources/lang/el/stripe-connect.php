<?php

return [
    'stripe_account_id' => 'Αναγνωριστικό λογαριασμού Stripe',
    'go_to_dashboard' => 'Μετάβαση στον πίνακα ελέγχου Express',
    'connect' => [
        'label' => 'Σύνδεση με το Stripe',
        'description' => 'Συνδέστε τον λογαριασμό σας Stripe για να συλλέγετε πληρωμές.',
    ],
    'disconnect' => [
        'label' => 'Αποσύνδεση Stripe',
        'confirm' => 'Είστε βέβαιοι ότι θέλετε να αποσυνδέσετε τον λογαριασμό σας Stripe;',
    ],
    'notifications' => [
        'connected' => 'Ο λογαριασμός σας Stripe έχει συνδεθεί.',
        'disconnected' => 'Ο λογαριασμός σας Stripe έχει αποσυνδεθεί.',
        'now_active' => 'Ο λογαριασμός σας Stripe είναι τώρα ενεργός.',
    ],
    'withdrawal' => [
        'payout_info' => 'Η πληρωμή σας θα μεταφερθεί αυτόματα στον λογαριασμό σας Stripe με αναγνωριστικό: :stripe_account_id.',
    ],
];
