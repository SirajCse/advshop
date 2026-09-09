<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Auth\Events\Logout;

class PersistCartOnLogout
{
    public function handle(Logout $event): void
    {
        if (! $event->user instanceof Customer) {
            return;
        }

        $customer = $event->user;

        if (Cart::instance('cart')->isEmpty()) {
            return;
        }

        Cart::instance('cart')->storeForCustomerQuietly($customer->id);
    }
}
