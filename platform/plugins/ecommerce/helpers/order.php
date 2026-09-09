<?php

if (! function_exists('render_cart_form')) {
    function render_cart_form(): string
    {
        return view('plugins/ecommerce::orders.partials.cart')->render();
    }
}

if (! function_exists('get_order_code')) {
    function get_order_code(int|string $orderId): string
    {
        $prefix = '#' . (get_ecommerce_setting('store_order_prefix') ? get_ecommerce_setting('store_order_prefix') . '-' : '');
        $prefix = apply_filters('ecommerce_order_code_prefix', $prefix);

        $suffix = get_ecommerce_setting('store_order_suffix') ? '-' . get_ecommerce_setting('store_order_suffix') : '';

        if (is_string($orderId) && ! is_numeric($orderId)) {
            return $prefix . $orderId . $suffix;
        }

        $startNumber = (int) get_ecommerce_setting('default_order_start_number', config('plugins.ecommerce.order.default_order_start_number'));
        $orderNumberLength = (int) get_ecommerce_setting('order_number_length', 8);
        $orderNumber = str_pad((string) ($startNumber + $orderId), $orderNumberLength, '0', STR_PAD_LEFT);

        return $prefix . $orderNumber . $suffix;
    }
}

if (! function_exists('get_order_id_from_order_code')) {
    function get_order_id_from_order_code(string $code): int|string
    {
        $prefix = '#' . (get_ecommerce_setting('store_order_prefix') ? (get_ecommerce_setting('store_order_prefix') . '-') : '');

        $prefix = apply_filters('ecommerce_order_code_prefix', $prefix);

        $suffix = get_ecommerce_setting('store_order_suffix') ? '-' . get_ecommerce_setting('store_order_suffix') : '';

        $orderId = substr($code, strlen($prefix));

        if ($suffix) {
            $orderId = substr($orderId, 0, strrpos($orderId, $suffix));
        }

        if (strlen($orderId) === 36 && substr_count($orderId, '-') === 4) {
            return $orderId;
        }

        $startNumber = (int) get_ecommerce_setting('default_order_start_number', config('plugins.ecommerce.order.default_order_start_number'));

        return (int) ltrim($orderId, '0') - $startNumber;
    }
}
