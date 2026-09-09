<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbandonedCart extends BaseModel
{
    protected $table = 'ec_abandoned_carts';

    protected $fillable = [
        'customer_id',
        'session_id',
        'cart_data',
        'total_amount',
        'items_count',
        'email',
        'phone',
        'customer_name',
        'abandoned_at',
        'reminder_sent_at',
        'reminders_sent',
        'last_email_sequence',
        'recovery_token',
        'coupon_code',
        'clicked_at',
        'unsubscribe_token',
        'unsubscribed_at',
        'is_recovered',
        'recovered_at',
        'recovered_order_id',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total_amount' => 'decimal:2',
        'items_count' => 'integer',
        'abandoned_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'reminders_sent' => 'integer',
        'last_email_sequence' => 'integer',
        'clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'is_recovered' => 'boolean',
        'recovered_at' => 'datetime',
        'customer_name' => SafeContent::class,
        'email' => SafeContent::class,
        'phone' => SafeContent::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (! $model->recovery_token) {
                $model->recovery_token = Str::random(64);
            }

            if (! $model->unsubscribe_token) {
                $model->unsubscribe_token = Str::random(64);
            }
        });

        static::saving(function (self $model): void {
            if ($model->customer_id && ! Customer::query()->where('id', $model->customer_id)->exists()) {
                $model->customer_id = null;
            }

            if ($model->recovered_order_id && ! Order::query()->where('id', $model->recovered_order_id)->exists()) {
                $model->recovered_order_id = null;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    public function scopeAbandoned(Builder $query): Builder
    {
        return $query->where('is_recovered', false)
            ->whereNotNull('abandoned_at');
    }

    public function scopeNotReminded(Builder $query): Builder
    {
        return $query->whereNull('reminder_sent_at');
    }

    public function scopeCanSendReminder(Builder $query, int $hoursAfterAbandonment = 1): Builder
    {
        return $query->abandoned()
            ->where('abandoned_at', '<=', now()->subHours($hoursAfterAbandonment));
    }

    public function scopeNeedsSequenceEmail(Builder $query, int $sequence, int $hoursDelay): Builder
    {
        $query
            ->abandoned()
            ->whereNotNull('email')
            ->whereNull('unsubscribed_at')
            ->where('last_email_sequence', $sequence - 1)
            ->where('abandoned_at', '<=', now()->subHours($hoursDelay))
            ->where('abandoned_at', '>=', now()->subDays(30));

        if ($sequence > 1) {
            $query->where('reminder_sent_at', '<=', now()->subHour());
        }

        return $query;
    }

    public function scopeNotExpired(Builder $query, int $days = 30): Builder
    {
        return $query->where('abandoned_at', '>=', now()->subDays($days));
    }

    public function markAsRecovered(Order $order): void
    {
        $this->update([
            'is_recovered' => true,
            'recovered_at' => now(),
            'recovered_order_id' => $order->id,
        ]);
    }

    public function incrementRemindersSent(): void
    {
        $this->update([
            'reminder_sent_at' => now(),
            'reminders_sent' => $this->reminders_sent + 1,
        ]);
    }

    public function updateEmailSequence(int $sequence): void
    {
        $this->update([
            'last_email_sequence' => $sequence,
            'reminder_sent_at' => now(),
            'reminders_sent' => $this->reminders_sent + 1,
        ]);
    }

    public function markAsClicked(): void
    {
        if (! $this->clicked_at) {
            $this->update(['clicked_at' => now()]);
        }
    }

    public function isExpired(int $days = 30): bool
    {
        return $this->abandoned_at && $this->abandoned_at->lt(now()->subDays($days));
    }

    public function getCartItems(): array
    {
        $items = [];
        $cartData = $this->cart_data ?? [];

        foreach ($cartData as $item) {
            $product = Product::query()->find($item['id'] ?? null);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $item['qty'] ?? 1,
                    'options' => $item['options'] ?? [],
                ];
            }
        }

        return $items;
    }
}
