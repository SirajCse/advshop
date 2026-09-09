<?php

return [
    'stripe_account_id' => 'ID účtu Stripe',
    'go_to_dashboard' => 'Přejít na Express Dashboard',
    'connect' => [
        'label' => 'Připojit se ke Stripe',
        'description' => 'Připojte svůj účet Stripe pro příjem plateb.',
    ],
    'disconnect' => [
        'label' => 'Odpojit Stripe',
        'confirm' => 'Opravdu chcete odpojit svůj účet Stripe?',
    ],
    'notifications' => [
        'connected' => 'Váš účet Stripe byl připojen.',
        'disconnected' => 'Váš účet Stripe byl odpojen.',
        'now_active' => 'Váš účet Stripe je nyní aktivní.',
    ],
    'withdrawal' => [
        'payout_info' => 'Vaše výplata bude automaticky převedena na váš účet Stripe s ID: :stripe_account_id.',
    ],
];
