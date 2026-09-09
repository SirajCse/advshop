<?php

return [
    'name' => 'Revenues',
    'edit' => 'Update wallet balance',
    'view_store' => 'View store ":store"',
    'forms' => [
        'description_placeholder' => 'Update the exact amount with order #__ORDER_ID__ with reason: __REASON__',
        'amount' => 'Amount',
        'amount_placeholder' => 'Enter Amount',
        'type' => 'Type',
        'created_by' => 'Created By',
    ],
    'types' => [
        'add-amount' => 'Credit',
        'subtract-amount' => 'Debit',
        'order-return' => 'Order Return',
    ],
    'store_name' => 'Store Name',
    'vendor_name' => 'Vendor Name',
    'balance' => 'Balance',
    'store_information' => 'Store information',
    'update_balance' => 'Adjust Balance',
    'update_balance_title' => 'Add/Subtract amount of vendor',
    'statements' => 'Statements',
    'products' => 'Products',
    'items_earning_sales' => 'Items Earning Sales: :amount',
];
