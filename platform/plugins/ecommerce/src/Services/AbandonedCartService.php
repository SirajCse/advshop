<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\AbandonedCart;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AbandonedCartService
{
    public function trackCart(): void
    {
        if (Cart::count() === 0) {
            return;
        }

        $customer = Auth::guard('customer')->user();
        $sessionId = Session::getId();

        $cartData = [];
        foreach (Cart::content() as $item) {
            $cartData[] = [
                'id' => $item->id,
                'name' => $item->name,
                'qty' => $item->qty,
                'price' => $item->price,
                'options' => $item->options->toArray(),
            ];
        }

        $abandonedCart = AbandonedCart::query()->updateOrCreate([
            'customer_id' => $customer?->id,
            'session_id' => $customer ? null : $sessionId,
            'is_recovered' => false,
        ], [
            'cart_data' => $cartData,
            'total_amount' => Cart::rawTotal(),
            'items_count' => Cart::count(),
            'email' => $customer?->email ?? session('abandoned_cart_email'),
            'phone' => $customer?->phone ?? session('abandoned_cart_phone'),
            'customer_name' => $customer?->name ?? session('abandoned_cart_name'),
            'updated_at' => now(),
        ]);

        if (! $abandonedCart->abandoned_at) {
            $abandonedCart->update(['abandoned_at' => now()]);
        }
    }

    public function markCartAsRecovered(Order $order): void
    {
        $customer = $order->user;
        $sessionId = Session::getId();

        $abandonedCart = AbandonedCart::query()
            ->where('is_recovered', false)
            ->where(function ($query) use ($customer, $sessionId): void {
                if ($customer) {
                    $query->where('customer_id', $customer->id);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->latest()
            ->first();

        if ($abandonedCart) {
            $abandonedCart->markAsRecovered($order);
        }
    }

    public function getCartsForSequence(int $sequence): Collection
    {
        $maxReminders = (int) get_ecommerce_setting('abandoned_cart_max_reminders', 3);

        if ($sequence > $maxReminders) {
            return new Collection();
        }

        $hoursDelay = $this->getSequenceDelay($sequence);

        return AbandonedCart::query()
            ->needsSequenceEmail($sequence, $hoursDelay)
            ->get();
    }

    public function getSequenceDelay(int $sequence): int
    {
        return match ($sequence) {
            1 => (int) get_ecommerce_setting('abandoned_cart_email_1_delay', 1),
            2 => (int) get_ecommerce_setting('abandoned_cart_email_2_delay', 24),
            3 => (int) get_ecommerce_setting('abandoned_cart_email_3_delay', 72),
            default => 1,
        };
    }

    public function identifyAbandonedCarts(int $hoursThreshold = 1): Collection
    {
        $reminderInterval = get_ecommerce_setting('abandoned_cart_reminder_interval', 24);
        $maxReminders = get_ecommerce_setting('abandoned_cart_max_reminders', 3);

        return AbandonedCart::query()
            ->abandoned()
            ->where('abandoned_at', '<=', now()->subHours($hoursThreshold))
            ->where(function ($query) use ($reminderInterval): void {
                $query->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<=', now()->subHours($reminderInterval));
            })
            ->where('reminders_sent', '<', $maxReminders)
            ->get();
    }

    public function cleanupOldAbandonedCarts(int $daysToKeep = 30): int
    {
        return AbandonedCart::query()
            ->where('created_at', '<=', now()->subDays($daysToKeep))
            ->where('is_recovered', false)
            ->delete();
    }

    public function getAbandonmentRate(): float
    {
        $total = AbandonedCart::query()->count();
        if ($total === 0) {
            return 0;
        }

        $recovered = AbandonedCart::query()->where('is_recovered', true)->count();

        return round((($total - $recovered) / $total) * 100, 2);
    }

    public function getRecoveryRate(): float
    {
        $total = AbandonedCart::query()->count();
        if ($total === 0) {
            return 0;
        }

        $recovered = AbandonedCart::query()->where('is_recovered', true)->count();

        return round(($recovered / $total) * 100, 2);
    }

    public function getRevenueRecovered(): float
    {
        return (float) AbandonedCart::query()
            ->where('is_recovered', true)
            ->sum('total_amount');
    }

    public function getClickRate(): float
    {
        $sent = AbandonedCart::query()->where('reminders_sent', '>', 0)->count();
        if ($sent === 0) {
            return 0;
        }

        $clicked = AbandonedCart::query()
            ->where('reminders_sent', '>', 0)
            ->whereNotNull('clicked_at')
            ->count();

        return round(($clicked / $sent) * 100, 2);
    }

    public function recoverCart(string $token): ?AbandonedCart
    {
        $abandonedCart = AbandonedCart::query()
            ->where('recovery_token', $token)
            ->where('is_recovered', false)
            ->notExpired(30)
            ->first();

        if (! $abandonedCart) {
            return null;
        }

        $abandonedCart->markAsClicked();

        Cart::instance('cart')->destroy();

        foreach ($abandonedCart->cart_data as $item) {
            try {
                $product = Product::query()->find($item['id']);

                if (! $product || $product->isOutOfStock()) {
                    continue;
                }

                Cart::instance('cart')->addQuietly(
                    $product->getKey(),
                    $product->name,
                    $item['qty'] ?? 1,
                    $product->front_sale_price ?: $product->price,
                    $item['options'] ?? []
                );
            } catch (\Throwable) {
                continue;
            }
        }

        if ($abandonedCart->coupon_code) {
            session(['applied_coupon_code' => $abandonedCart->coupon_code]);
        }

        return $abandonedCart;
    }

    public function isOptedOut(AbandonedCart $cart): bool
    {
        if ($cart->customer_id && $cart->customer?->getMetaData('abandoned_cart_emails_opt_out', true)) {
            return true;
        }

        $abandonedCart = AbandonedCart::query()
            ->where('email', $cart->email)
            ->where('id', '!=', $cart->id)
            ->whereNotNull('unsubscribed_at')
            ->exists();

        return $abandonedCart;
    }

    public function unsubscribe(string $token): bool
    {
        $abandonedCart = AbandonedCart::query()
            ->where('unsubscribe_token', $token)
            ->first();

        if (! $abandonedCart) {
            return false;
        }

        $abandonedCart->update(['unsubscribed_at' => now()]);

        if ($abandonedCart->customer_id && $abandonedCart->customer) {
            $abandonedCart->customer->setMeta('abandoned_cart_emails_opt_out', true);
            $abandonedCart->customer->save();
        }

        AbandonedCart::query()
            ->where('email', $abandonedCart->email)
            ->whereNull('unsubscribed_at')
            ->update(['unsubscribed_at' => now()]);

        return true;
    }

    public function updateCustomerInfo(array $data): void
    {
        $sessionId = Session::getId();

        $abandonedCart = AbandonedCart::query()
            ->where('session_id', $sessionId)
            ->where('is_recovered', false)
            ->latest()
            ->first();

        if ($abandonedCart) {
            $abandonedCart->update([
                'email' => $data['email'] ?? $abandonedCart->email,
                'phone' => $data['phone'] ?? $abandonedCart->phone,
                'customer_name' => $data['name'] ?? $abandonedCart->customer_name,
            ]);
        }

        session([
            'abandoned_cart_email' => $data['email'] ?? null,
            'abandoned_cart_phone' => $data['phone'] ?? null,
            'abandoned_cart_name' => $data['name'] ?? null,
        ]);
    }
}
