<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;

class LinkCartOnRegistration
{
    public function handle(Registered $event): void
    {
        if (! $event->user instanceof Customer) {
            return;
        }

        $customer = $event->user;
        $guestIdentifier = request()->cookie('guest_cart_id');

        if (! $guestIdentifier) {
            if (Cart::instance('cart')->isNotEmpty()) {
                Cart::instance('cart')->storeForCustomerQuietly($customer->id);
            }

            return;
        }

        Cart::instance('cart')->linkGuestCartToCustomer($guestIdentifier, $customer->id);

        Cookie::queue(Cookie::forget('guest_cart_id'));
    }
}
