<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

class SyncCartOnLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof Customer) {
            return;
        }

        $customer = $event->user;
        $guestIdentifier = request()->cookie('guest_cart_id');

        Cart::instance('cart')->restoreForCustomerQuietly($customer->id);

        if ($guestIdentifier && Cart::instance('cart')->storedCartWithIdentifierExists($guestIdentifier)) {
            Cart::instance('cart')->mergeGuestCartQuietly($guestIdentifier, $customer->id);
            Cookie::queue(Cookie::forget('guest_cart_id'));
        }

        if (Cart::instance('cart')->isNotEmpty()) {
            Cart::instance('cart')->storeForCustomerQuietly($customer->id);
        }
    }
}
