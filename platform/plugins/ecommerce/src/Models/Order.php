<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderCancellationReasonEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Support\Facades\DB;

class Order extends BaseModel
{
    protected $table = 'ec_orders';

    protected $fillable = [
        'status',
        'user_id',
        'amount',
        'tax_amount',
        'shipping_method',
        'shipping_option',
        'shipping_amount',
        'shipping_tax_amount',
        'payment_fee',
        'description',
        'coupon_code',
        'discount_amount',
        'sub_total',
        'is_confirmed',
        'discount_description',
        'is_finished',
        'cancellation_reason',
        'cancellation_reason_description',
        'token',
        'completed_at',
        'proof_file',
        'private_notes',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'shipping_method' => ShippingMethodEnum::class,
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        self::deleted(function (Order $order): void {
            $relations = [
                'products',
                'shipment',
                'histories',
                'address',
                'invoice',
                'orderMetadata',
            ];

            if (is_plugin_active('payment')) {
                $relations[] = 'payment';
            }

            $order->loadMissing($relations);

            $order->restockProductQuantities();

            if ($order->relationLoaded('shipment') && $order->shipment->id) {
                $order->shipment->delete();
            }

            if ($order->relationLoaded('histories')) {
                $order->histories()->delete();
            }

            if ($order->relationLoaded('products')) {
                $order->products()->delete();
            }

            if ($order->relationLoaded('address')) {
                $order->address()->delete();
            }

            if ($order->relationLoaded('invoice') && $order->invoice->id) {
                $order->invoice->delete();
            }

            if (is_plugin_active('payment') && $order->relationLoaded('payment') && $order->payment->id) {
                $order->payment->delete();
            }

            if ($order->relationLoaded('orderMetadata')) {
                $order->orderMetadata()->delete();
            }
        });

        static::creating(fn (Order $order) => $order->code = static::generateUniqueCode());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id')->withDefault();
    }

    protected function userName(): Attribute
    {
        return Attribute::get(fn () => $this->user->name);
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::get(fn () => $this->shippingAddress->full_address);
    }

    protected function shippingMethodName(): Attribute
    {
        return Attribute::get(
            fn () => OrderHelper::getShippingMethod(
                $this->attributes['shipping_method'],
                $this->attributes['shipping_option']
            )
        );
    }

    public function address(): HasOne
    {
        return $this->shippingAddress();
    }

    public function shippingAddress(): HasOne
    {
        return $this
            ->hasOne(OrderAddress::class, 'order_id')
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->withDefault();
    }

    public function billingAddress(): HasOne
    {
        return $this
            ->hasOne(OrderAddress::class, 'order_id')
            ->where('type', OrderAddressTypeEnum::BILLING)
            ->withDefault();
    }

    public function referral(): HasOne
    {
        return $this->hasOne(OrderReferral::class, 'order_id')->withDefault();
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id')->with(['product']);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'order_id')->with(['user', 'order']);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class)->withDefault();
    }

    public function payment(): BelongsTo
    {
        if (! is_plugin_active('payment')) {
            return $this->belongsTo(self::class, 'payment_id')->whereRaw('1 = 0')->withDefault();
        }

        return $this->belongsTo(Payment::class, 'payment_id')->withDefault();
    }

    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'reference')->withDefault();
    }

    public function taxInformation(): HasOne
    {
        return $this->hasOne(OrderTaxInformation::class, 'order_id');
    }

    public function orderMetadata(): HasMany
    {
        return $this->hasMany(OrderMetadata::class, 'order_id');
    }

    public function scopeSearchByKeyword($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        $keyword = '%' . $keyword . '%';

        return $query->where(function ($query) use ($keyword): void {
            $query
                ->whereHas('address', function ($subQuery) use ($keyword) {
                    return $subQuery
                        ->where('name', 'LIKE', $keyword)
                        ->orWhere('email', 'LIKE', $keyword)
                        ->orWhere('phone', 'LIKE', $keyword);
                })
                ->orWhereHas('user', function ($subQuery) use ($keyword) {
                    return $subQuery
                        ->where('name', 'LIKE', $keyword)
                        ->orWhere('email', 'LIKE', $keyword)
                        ->orWhere('phone', 'LIKE', $keyword);
                })
                ->orWhereHas('products.product', function ($subQuery) use ($keyword) {
                    return $subQuery->where('sku', 'LIKE', $keyword);
                })
                ->orWhere('code', 'LIKE', $keyword);
        });
    }

    public function canBeCanceled(): bool
    {
        if ($this->status == OrderStatusEnum::CANCELED) {
            return false;
        }

        if ($this->shipment && $this->shipment->id) {
            $pendingShippingStatuses = [
                ShippingStatusEnum::ARRANGE_SHIPMENT,
                ShippingStatusEnum::PENDING,
                ShippingStatusEnum::NOT_APPROVED,
                ShippingStatusEnum::APPROVED,
            ];

            return in_array($this->shipment->status, $pendingShippingStatuses);
        }

        return $this->status == OrderStatusEnum::PENDING;
    }

    public function canBeCanceledByAdmin(): bool
    {
        if ($this->status == OrderStatusEnum::CANCELED) {
            return false;
        }

        if ($this->shipment && $this->shipment->id) {
            if ($this->shipment->status == ShippingStatusEnum::CANCELED) {
                return true;
            }

            $pendingShippingStatuses = [
                ShippingStatusEnum::APPROVED,
                ShippingStatusEnum::ARRANGE_SHIPMENT,
                ShippingStatusEnum::PENDING,
                ShippingStatusEnum::NOT_APPROVED,
                ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT,
            ];

            return in_array($this->shipment->status, $pendingShippingStatuses);
        }

        return in_array($this->status, [OrderStatusEnum::PENDING, OrderStatusEnum::PROCESSING]);
    }

    public function getIsFreeShippingAttribute(): bool
    {
        return $this->shipping_amount == 0 && $this->discount_amount == 0 && $this->coupon_code;
    }

    public function getAmountFormatAttribute(): string
    {
        return format_price($this->amount);
    }

    public function getDiscountAmountFormatAttribute(): string
    {
        return format_price($this->discount_amount);
    }

    public function isInvoiceAvailable(): bool
    {
        return $this->invoice()->exists()
            && (! EcommerceHelper::disableOrderInvoiceUntilOrderConfirmed() || $this->is_confirmed)
            && $this->status != OrderStatusEnum::CANCELED;
    }

    public function getProductsWeightAttribute(): float|int
    {
        $weight = 0;

        foreach ($this->products as $product) {
            if ($product && $product->weight) {
                $weight += $product->weight * $product->qty;
            }
        }

        return EcommerceHelper::validateOrderWeight($weight);
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(OrderReturn::class, 'order_id')->withDefault();
    }

    public function canBeReturned(): bool
    {
        if (! EcommerceHelper::isOrderReturnEnabled()) {
            return false;
        }

        if ($this->status != OrderStatusEnum::COMPLETED || ! $this->completed_at) {
            return false;
        }

        $overReturnDate = Carbon::now()->subDays(EcommerceHelper::getReturnableDays())->gt($this->completed_at);

        if ($overReturnDate) {
            return false;
        }

        if (EcommerceHelper::isEnabledSupportDigitalProducts()) {
            if ($this->products->where('times_downloaded')->count()) {
                return false;
            }
        }

        return ! $this->returnRequest()->exists();
    }

    public function restockProductQuantities(bool $updateRestockQuantity = false): void
    {
        foreach ($this->products as $orderProduct) {
            $product = $orderProduct->product;

            if (! $product || ! $product->id) {
                continue;
            }

            $quantityToRestore = $orderProduct->qty - ($orderProduct->restock_quantity ?? 0);

            if ($product->with_storehouse_management && $quantityToRestore > 0) {
                $product->quantity += $quantityToRestore;
                $product->save();

                if ($updateRestockQuantity) {
                    $orderProduct->restock_quantity = $orderProduct->qty;
                    $orderProduct->save();
                }

                event(new ProductQuantityUpdatedEvent($product));
            }
        }
    }

    public static function generateUniqueCode(): string
    {
        $nextInsertId = BaseModel::determineIfUsingUuidsForId() ? static::query()->count() + 1 : static::query()->max(
            'id'
        ) + 1;

        do {
            $code = get_order_code($nextInsertId);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function digitalProducts(): Collection
    {
        return $this->products->filter(fn ($item) => $item->isTypeDigital());
    }

    public static function countRevenueByDateRange(CarbonInterface $startDate, CarbonInterface $endDate): float
    {
        if (! is_plugin_active('payment')) {
            return (float) self::query()
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('is_finished', true)
                ->sum('amount');
        }

        return self::query()
            ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->where(function ($q) use ($startDate, $endDate): void {
                $q->where(function ($subQ) use ($startDate, $endDate): void {
                    $subQ->whereDate('payments.created_at', '>=', $startDate)
                        ->whereDate('payments.created_at', '<=', $endDate);
                })->orWhereNull('ec_orders.payment_id');
            })
            ->where(function ($q): void {
                $q->where('payments.status', PaymentStatusEnum::COMPLETED)
                    ->orWhereNull('ec_orders.payment_id');
            })
            ->where('ec_orders.is_finished', true)
            ->sum(DB::raw('COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)'));
    }

    public static function getRevenueData(
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        $select = []
    ): Collection {
        if (! is_plugin_active('payment')) {
            return self::query()
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('is_finished', true)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->select([
                    DB::raw('DATE(created_at) AS date'),
                    DB::raw('SUM(amount) as revenue'),
                ])
                ->get();
        }

        if (empty($select)) {
            $select = [
                DB::raw('DATE(COALESCE(payments.created_at, ec_orders.created_at)) AS date'),
                DB::raw('SUM(COALESCE(payments.amount, 0) - COALESCE(payments.refunded_amount, 0)) as revenue'),
            ];
        }

        return self::query()
            ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
            ->where(function ($q) use ($startDate, $endDate): void {
                $q->where(function ($subQ) use ($startDate, $endDate): void {
                    $subQ->whereDate('payments.created_at', '>=', $startDate)
                        ->whereDate('payments.created_at', '<=', $endDate);
                })->orWhere(function ($subQ) use ($startDate, $endDate): void {
                    $subQ->whereNull('ec_orders.payment_id')
                        ->whereDate('ec_orders.created_at', '>=', $startDate)
                        ->whereDate('ec_orders.created_at', '<=', $endDate);
                });
            })
            ->where(function ($q): void {
                $q->where('payments.status', PaymentStatusEnum::COMPLETED)
                    ->orWhereNull('ec_orders.payment_id');
            })
            ->where('ec_orders.is_finished', true)
            ->groupBy('date')
            ->select($select)
            ->get();
    }

    protected function cancellationReasonMessage(): Attribute
    {
        return Attribute::get(function () {
            $reason = OrderCancellationReasonEnum::getLabel($this->cancellation_reason);

            if ($this->cancellation_reason_description) {
                return sprintf('%s (%s)', $reason, $this->cancellation_reason_description);
            }

            return $reason;
        });
    }

    public function getOrderProducts(): IlluminateCollection
    {
        $productsIds = $this->products->pluck('product_id')->all();

        if (empty($productsIds)) {
            return collect();
        }

        return get_products([
            'condition' => [
                ['ec_products.id', 'IN', $productsIds],
            ],
            'select' => [
                'ec_products.id',
                'ec_products.images',
                'ec_products.name',
                'ec_products.brand_id',
                'ec_products.price',
                'ec_products.sale_price',
                'ec_products.sale_type',
                'ec_products.start_date',
                'ec_products.end_date',
                'ec_products.sku',
                'ec_products.order',
                'ec_products.created_at',
                'ec_products.is_variation',
                'ec_products.with_storehouse_management',
                'ec_products.stock_status',
                'ec_products.quantity',
                'ec_products.allow_checkout_when_out_of_stock',
            ],
            'with' => [
                'variationProductAttributes',
            ],
        ]);
    }

    public function toWebhookData(): array
    {
        $shippingAddress = $this->shippingAddress;

        $customerData = [
            'name' => $shippingAddress->name,
            'email' => $shippingAddress->email,
            'phone' => $shippingAddress->phone,
            'address' => [
                'address' => $shippingAddress->address,
                'city' => $shippingAddress->city,
                'state' => $shippingAddress->state,
                'country' => $shippingAddress->country,
                'zip_code' => $shippingAddress->zip_code,
            ],
        ];

        if ($this->user_id) {
            $customerData['id'] = $this->user_id;
        }

        $data = [
            'id' => $this->id,
            'status' => [
                'value' => $this->status->getValue(),
                'text' => $this->status->label(),
            ],
            'shipping_status' => $this->shipment->id ? [
                'value' => $this->shipment->status->getValue(),
                'text' => $this->shipment->status->label(),
            ] : [],
            'payment_method' => is_plugin_active('payment') && $this->payment->id ? [
                'value' => $this->payment->payment_channel->getValue(),
                'text' => $this->payment->payment_channel->displayName(),
            ] : [],
            'payment_status' => is_plugin_active('payment') && $this->payment->id ? [
                'value' => $this->payment->status->getValue(),
                'text' => $this->payment->status->label(),
            ] : [],
            'customer' => $customerData,
            'sub_total' => $this->sub_total,
            'tax_amount' => $this->tax_amount,
            'shipping_method' => $this->shipping_method->getValue(),
            'shipping_option' => $this->shipping_option,
            'shipping_amount' => $this->shipping_amount,
            'amount' => $this->amount,
            'coupon_code' => $this->coupon_code,
            'discount_amount' => $this->discount_amount,
            'discount_description' => $this->discount_description,
            'note' => $this->description,
            'is_confirmed' => $this->is_confirmed,
        ];

        return apply_filters('ecommerce_order_webhook_data', $data, $this);
    }

    public function getOrderTrackingUrl(): string
    {
        $params = [
            'order_id' => $this->code,
        ];

        if (EcommerceHelper::isOrderTrackingUsingPhone()) {
            $params['phone'] = $this->user->phone ?: $this->address->phone;
        } else {
            $params['email'] = $this->user->email ?: $this->address->email;
        }

        return route('public.orders.tracking', $params);
    }

    public function isPaymentProofEnabled(): bool
    {
        if (! $this->payment || ! $this->payment->payment_channel) {
            return false;
        }

        return EcommerceHelper::isPaymentProofEnabledForPaymentMethod($this->payment->payment_channel->getValue());
    }

    public function getOrderMetadata(string $key, $default = null)
    {
        $metadata = $this->orderMetadata()->where('meta_key', $key)->first();

        return $metadata ? $metadata->meta_value : $default;
    }

    public function setOrderMetadata(string $key, $value): void
    {
        $this->orderMetadata()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }

    public function storeCustomerLocale(): void
    {
        $locale = app()->getLocale();

        if ($locale) {
            $this->setOrderMetadata('customer_locale', $locale);
        }
    }

    public function storeCustomerCurrency(): void
    {
        $currency = get_application_currency();

        if ($currency) {
            $this->setOrderMetadata('customer_currency', $currency->title);
        }
    }

    public function getCustomerCurrency(): ?string
    {
        return $this->getOrderMetadata('customer_currency');
    }
}
