<?php

return [
    'webhook_secret' => 'Μυστικό Webhook',
    'webhook_secret_helper' => 'Βρείτε το στο Stripe Dashboard σας στην ενότητα Developers > Webhooks > Signing secret.',
    'public_key_helper' => 'Βρείτε το στο Stripe Dashboard σας στην ενότητα Developers > API keys.',
    'secret_key_helper' => 'Βρείτε το στο Stripe Dashboard σας στην ενότητα Developers > API keys. Κρατήστε αυτό το κλειδί εμπιστευτικό.',
    'webhook_setup_guide' => [
        'title' => 'Οδηγός ρύθμισης Stripe Webhook',
        'description' => 'Ακολουθήστε αυτά τα βήματα για να ρυθμίσετε ένα Stripe webhook',
        'step_1_label' => 'Συνδεθείτε στον πίνακα ελέγχου του Stripe',
        'step_1_description' => 'Access the :link and click on the "Add Endpoint" button in the "Webhooks" section of the "Developers" tab.',
        'step_2_label' => 'Επιλέξτε συμβάν και διαμορφώστε το τελικό σημείο',
        'step_2_description' => 'Select the "payment_intent.succeeded" event and enter the following URL in the "Endpoint URL" field: :url',
        'step_3_label' => 'Προσθήκη τελικού σημείου',
        'step_3_description' => 'Κάντε κλικ στο κουμπί "Προσθήκη τελικού σημείου" για να αποθηκεύσετε το webhook.',
        'step_4_label' => 'Αντιγραφή μυστικού υπογραφής',
        'step_4_description' => 'Αντιγράψτε την τιμή "Signing Secret" από την ενότητα "Webhook Details" και επικολλήστε την στο πεδίο "Stripe Webhook Secret" στην ενότητα "Stripe" της καρτέλας "Πληρωμή" στη σελίδα "Ρυθμίσεις".',
    ],
    'no_payment_charge' => 'Χωρίς χρέωση πληρωμής. Παρακαλώ δοκιμάστε ξανά!',
    'payment_failed' => 'Η πληρωμή απέτυχε!',
    'payment_type' => 'Τύπος πληρωμής',
];
