<?php

return [
    'stripe_account_id' => 'Stripe fiók azonosító',
    'go_to_dashboard' => 'Ugrás az Express irányítópultra',
    'connect' => [
        'label' => 'Csatlakozás a Stripe-hoz',
        'description' => 'Csatlakoztassa Stripe fiókját a fizetések gyűjtéséhez.',
    ],
    'disconnect' => [
        'label' => 'Stripe leválasztása',
        'confirm' => 'Biztos, hogy le szeretné választani Stripe fiókját?',
    ],
    'notifications' => [
        'connected' => 'Stripe fiókja csatlakoztatva lett.',
        'disconnected' => 'Stripe fiókja le lett választva.',
        'now_active' => 'Stripe fiókja most aktív.',
    ],
    'withdrawal' => [
        'payout_info' => 'Kifizetése automatikusan átkerül Stripe fiókjára, azonosító: :stripe_account_id.',
    ],
];
