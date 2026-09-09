<?php

namespace Botble\Ecommerce\Supports;

use Botble\ACL\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Html;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Enums\OrderHistoryActionEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Events\OrderProductCreatedEvent;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Ecommerce\Exceptions\ProductIsNotActivatedYetException;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\Discount;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\EcommerceHelper as EcommerceHelperFacade;
use Botble\Ecommerce\Facades\FlashSale;
use Botble\Ecommerce\Facades\InvoiceHelper as InvoiceHelperFacade;
use Botble\Ecommerce\Http\Requests\CheckoutRequest;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Currency as CurrencyModel;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Services\Footprints\FootprinterInterface;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Media\Facades\RvMedia;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentFeeHelper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class OrderHelper
{
    public function processOrder(string|array|null $orderIds, ?string $chargeId = null): bool|Collection|array|Model
    {
        if (! empty($data['is_refund_update'])) {
            return false;
        }

        $orderIds = (array) $orderIds;

        $orders = Order::query()->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            return false;
        }

        if (is_plugin_active('payment') && $chargeId) {
            $payments = Payment::query()
                ->where('charge_id', $chargeId)
                ->whereIn('order_id', $orderIds)
                ->get();

            if ($payments->isNotEmpty()) {
                foreach ($orders as $order) {
                    $payment = $payments->firstWhere('order_id', $order->getKey());
                    if ($payment) {
                        $order->payment_id = $payment->getKey();
                        $order->save();
                    }
                }
            }
        }

        foreach ($orders as $order) {
            /**
             * @var Order $order
             */
            if (
                (float) $order->amount
                && (is_plugin_active('payment') && ! empty(PaymentMethods::methods()) && ! $order->payment_id)
            ) {
                continue;
            }

            if ($order->coupon_code && $order->discount_amount == 0) {
                $applyCouponService = app(HandleApplyCouponService::class);

                $sessionData = [
                    'shipping_amount' => $order->shipping_amount,
                    'raw_total' => $order->sub_total,
                    'promotion_discount_amount' => 0,
                ];

                $discount = $applyCouponService->getCouponData($order->coupon_code, $sessionData);

                if ($discount) {
                    $customerId = $order->user_id;
                    $resultCondition = $applyCouponService->checkConditionDiscount($discount, $sessionData, $customerId);

                    if (! Arr::get($resultCondition, 'error')) {
                        $orderProducts = $order->products;
                        $cartData = [
                            'rawTotal' => $order->sub_total,
                            'productItems' => $orderProducts->map(function ($orderProduct) {
                                $product = Product::query()->find($orderProduct->product_id);
                                if ($product) {
                                    $product->qty = $orderProduct->qty;
                                }

                                return $product;
                            })->filter(),
                        ];

                        $couponData = $applyCouponService->getCouponDiscountAmount($discount, $cartData, $sessionData);
                        $discountAmount = Arr::get($couponData, 'discount_amount', 0);

                        if ($discountAmount > 0) {
                            $order->discount_amount = $discountAmount;
                            $order->discount_description = $discount->description;
                            $order->amount = max($order->sub_total + $order->shipping_amount + $order->tax_amount + $order->payment_fee - $discountAmount, 0);
                            $order->save();

                            if ($order->payment_id) {
                                $payment = Payment::query()->find($order->payment_id);
                                if ($payment) {
                                    $payment->amount = $order->amount;
                                    $payment->save();
                                }
                            }
                        }
                    }
                }
            }

            // Defense-in-depth: never finalize an order without a shipping address.
            // The address is normally persisted earlier via the save-shipping-information
            // AJAX, but that step is session-bound and can be skipped (fast click, autofill,
            // broken JS, lost guest session, or a redirect/webhook payment flow that finalizes
            // by token in a context with no session). When that happens the order finalizes
            // "paid but address-less" (admin shows "Don't have an account yet", no shipping
            // block). This guard backfills a shipping row from any surviving local source
            // before is_finished is set. It is purely additive — it only creates a row when
            // none exists and never overwrites a populated one.
            $this->ensureShippingAddressBackfilled($order);

            event(new OrderPlacedEvent($order));

            if (EcommerceHelper::isOrderAutoConfirmedEnabled()) {
                $this->confirmOrder($order);
            }

            $order->is_finished = true;
            $order->save();

            $this->decreaseProductQuantity($order);
        }

        Cart::instance('cart')->destroy();

        if (auth('customer')->check()) {
            Cart::instance('cart')->deleteCustomerCart(auth('customer')->id());
        } else {
            $guestCartId = request()->cookie('guest_cart_id');
            if ($guestCartId) {
                Cart::instance('cart')->deleteGuestCart($guestCartId);
                cookie()->queue(cookie()->forget('guest_cart_id'));
            }
        }

        session()->forget('applied_coupon_code');

        session(['order_id' => Arr::first($orderIds)]);

        /**
         * @var Order $firstOrder
         */
        $firstOrder = $orders->first();

        if (is_plugin_active('marketplace')) {
            apply_filters(SEND_MAIL_AFTER_PROCESS_ORDER_MULTI_DATA, $orders);
        } else {
            if (
                ! is_plugin_active('payment')
                || in_array($order->payment->status, [PaymentStatusEnum::PENDING, PaymentStatusEnum::COMPLETED])
            ) {
                $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
                if ($mailer->templateEnabled('admin_new_order')) {
                    $mailer = $this->setEmailVariables($firstOrder, $mailer);
                    $mailer->sendUsingTemplate('admin_new_order', EcommerceHelper::getAdminNotificationEmails());
                }

                $this->sendOrderConfirmationEmail($firstOrder, true);
            }
        }

        session(['order_id' => $firstOrder->getKey()]);

        foreach ($orders as $order) {
            OrderHistory::query()->create([
                'action' => OrderHistoryActionEnum::CREATE_ORDER,
                'description' => trans('plugins/ecommerce::order.new_order_from', [
                    'order_id' => $order->code,
                    'customer' => BaseHelper::clean($order->user->name ?: $order->address->name),
                ]),
                'order_id' => $order->id,
            ]);

            if (
                (
                    is_plugin_active('payment')
                    && $order->amount
                    && $order->payment
                    && $order->payment->status == PaymentStatusEnum::COMPLETED
                )
                || $order->amount == 0
            ) {
                /**
                 * @var Order $order
                 */
                if (EcommerceHelperFacade::isEnabledSupportDigitalProducts()) {
                    $digitalProductsCount = EcommerceHelperFacade::countDigitalProducts($order->products);

                    if ($digitalProductsCount === $order->products->count() && EcommerceHelperFacade::isAutoCompleteDigitalOrdersAfterPayment()) {
                        $this->setOrderCompleted($order->getKey(), request(), Auth::id() ?? 0);
                    } elseif ($digitalProductsCount > 0) {
                        event(new OrderCompletedEvent($order));
                    }
                }
            }
        }

        if (FlashSale::isEnabled()) {
            $orders->loadMissing(['products.product.variationInfo.configurableProduct']);

            $productIds = [];
            $productQuantities = [];
            foreach ($orders as $order) {
                foreach ($order->products as $orderProduct) {
                    if (! $orderProduct->product || ! $orderProduct->product->id) {
                        continue;
                    }

                    $productId = $orderProduct->product->is_variation && $orderProduct->product->original_product
                        ? $orderProduct->product->original_product->id
                        : $orderProduct->product_id;

                    $productIds[] = $productId;
                    if (! isset($productQuantities[$productId])) {
                        $productQuantities[$productId] = 0;
                    }
                    $productQuantities[$productId] += $orderProduct->qty;
                }
            }

            if (! empty($productIds)) {
                $flashSaleProducts = DB::table('ec_flash_sale_products')
                    ->join('ec_flash_sales', 'ec_flash_sales.id', '=', 'ec_flash_sale_products.flash_sale_id')
                    ->whereIn('ec_flash_sale_products.product_id', array_unique($productIds))
                    ->where('ec_flash_sales.status', 'published')
                    ->where('ec_flash_sales.end_date', '>=', now())
                    ->select([
                        'ec_flash_sale_products.product_id',
                        'ec_flash_sale_products.flash_sale_id',
                        'ec_flash_sale_products.price',
                        'ec_flash_sale_products.quantity',
                        'ec_flash_sale_products.sold',
                    ])
                    ->latest('ec_flash_sales.end_date')
                    ->get()
                    ->keyBy('product_id');

                foreach ($flashSaleProducts as $productId => $flashSaleData) {
                    if (isset($productQuantities[$productId])) {
                        DB::table('ec_flash_sale_products')
                            ->where('flash_sale_id', $flashSaleData->flash_sale_id)
                            ->where('product_id', $productId)
                            ->update([
                                'sold' => (int) $flashSaleData->sold + $productQuantities[$productId],
                            ]);
                    }
                }
            }
        }

        return $orders;
    }

    public function validateAndReserveStock(array $cartItems): array
    {
        return DB::transaction(function () use ($cartItems) {
            $reservedItems = [];

            foreach ($cartItems as $item) {
                $product = Product::query()
                    ->where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                if ($product->isOutOfStock()) {
                    $this->restoreReservedStock($reservedItems);

                    return [
                        'success' => false,
                        'message' => __('Product :product is out of stock!', ['product' => $product->original_product->name]),
                        'product' => $product,
                    ];
                }

                if ($product->with_storehouse_management && ! $product->allow_checkout_when_out_of_stock) {
                    if ($product->quantity < $item['qty']) {
                        $this->restoreReservedStock($reservedItems);

                        return [
                            'success' => false,
                            'message' => __('Product :product only has :quantity item(s) left in stock, but you are trying to order :requested!', [
                                'product' => $product->original_product->name,
                                'quantity' => $product->quantity,
                                'requested' => $item['qty'],
                            ]),
                            'product' => $product,
                        ];
                    }

                    $product->quantity -= $item['qty'];
                    $product->save();

                    $reservedItems[] = [
                        'product_id' => $product->id,
                        'qty' => $item['qty'],
                    ];

                    event(new ProductQuantityUpdatedEvent($product));
                }

                if ($product->minimum_order_quantity > 0 && $item['qty'] < $product->minimum_order_quantity) {
                    $this->restoreReservedStock($reservedItems);

                    return [
                        'success' => false,
                        'message' => __('Minimum order quantity of product :product is :quantity, you need to buy more :more to place an order! ', [
                            'product' => BaseHelper::clean($product->original_product->name),
                            'quantity' => $product->minimum_order_quantity,
                            'more' => $product->minimum_order_quantity - $item['qty'],
                        ]),
                        'product' => $product,
                    ];
                }

                if ($product->maximum_order_quantity > 0 && $item['qty'] > $product->maximum_order_quantity) {
                    $this->restoreReservedStock($reservedItems);

                    return [
                        'success' => false,
                        'message' => __('Maximum order quantity of product :product is :quantity! ', [
                            'product' => $product->original_product->name,
                            'quantity' => $product->maximum_order_quantity,
                        ]),
                        'product' => $product,
                    ];
                }
            }

            return ['success' => true, 'message' => null, 'product' => null, 'reserved_items' => $reservedItems];
        });
    }

    public function restoreReservedStock(array $reservedItems): void
    {
        if (empty($reservedItems)) {
            return;
        }

        DB::transaction(function () use ($reservedItems): void {
            foreach ($reservedItems as $item) {
                $product = Product::query()
                    ->where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                $product->quantity += $item['qty'];
                $product->save();

                event(new ProductQuantityUpdatedEvent($product));
            }
        });
    }

    public function decreaseProductQuantity(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->products as $orderProduct) {
                /**
                 * @var Product $product
                 */
                $product = Product::query()
                    ->where('id', $orderProduct->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                if ($product->with_storehouse_management && $product->quantity >= $orderProduct->qty) {
                    $product->quantity = $product->quantity - $orderProduct->qty;
                    $product->save();

                    event(new ProductQuantityUpdatedEvent($product));
                }
            }

            return true;
        });
    }

    public function setEmailVariables(Order $order, ?EmailHandlerSupport $emailHandler = null): EmailHandlerSupport
    {
        $emailHandler = $emailHandler ?: EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

        return $emailHandler->setVariableValues($this->getEmailVariables($order));
    }

    public function getEmailVariables(Order $order): array
    {
        $paymentMethod = '&mdash;';

        if (is_plugin_active('payment')) {
            $paymentMethod = $order->payment->payment_channel->displayName();

            if ($order->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER && $order->payment->status == PaymentStatusEnum::PENDING) {
                $bankInfoDescription = BaseHelper::clean(get_payment_setting('description', $order->payment->payment_channel));

                if ($bankInfoDescription) {
                    $paymentMethod .= '<div>' . trans('plugins/ecommerce::order.payment_info') . ': <strong>' . $bankInfoDescription .
                    '</strong</div>';
                }
            }
        }

        $digitalProducts = [];

        if (EcommerceHelperFacade::isEnabledSupportDigitalProducts()) {
            foreach ($order->digitalProducts() as $digitalProduct) {
                $digitalProducts[] = [
                    'product_name' => $digitalProduct->product_name,
                    'product_image_url' => $digitalProduct->product_image_url,
                    'product_file_internal_count' => $digitalProduct->product_file_internal_count,
                    'download_hash_url' => $digitalProduct->download_hash_url,
                    'product_file_external_count' => $digitalProduct->product_file_external_count,
                    'download_external_url' => $digitalProduct->download_external_url,
                    'product_attributes_text' => Arr::get($digitalProduct->options, 'attributes'),
                    'product_options_text' => $digitalProduct->product_options_implode,
                    'product_options_array' => $digitalProduct->product_options_array,
                    'license_code' => $digitalProduct->license_code,
                ];
            }
        }

        return apply_filters('ecommerce_order_email_variables', [
            'store_address' => get_ecommerce_setting('store_address'),
            'store_phone' => get_ecommerce_setting('store_phone'),
            'order_id' => $order->code,
            'order_token' => $order->token,
            'order_note' => $order->description,
            'customer_name' => BaseHelper::clean($order->user->name ?: $order->address->name),
            'customer_email' => $order->user->email ?: $order->address->email,
            'customer_phone' => $order->user->phone ?: $order->address->phone,
            'customer_address' => $order->full_address,
            'product_list' => view('plugins/ecommerce::emails.partials.order-detail', compact('order'))
                ->render(),
            'digital_product_list' => EcommerceHelperFacade::isEnabledSupportDigitalProducts() ? $this->getDigitalProductListView($order) : null,
            'shipping_method' => $order->shipping_method_name,
            'payment_method' => $paymentMethod,
            'order_delivery_notes' => view(
                'plugins/ecommerce::emails.partials.order-delivery-notes',
                compact('order')
            )
                ->render(),
            'order' => [
                ...$order->toArray(),
                'created_at' => $order->created_at->toDateTimeString(),
                'updated_at' => $order->updated_at->toDateTimeString(),
            ],
            'shipment' => $order->shipment ? [
                ...$order->shipment->toArray(),
                'created_at' => $order->shipment->created_at?->toDateTimeString(),
                'updated_at' => $order->shipment->updated_at?->toDateTimeString(),
                'estimate_date_shipped' => $order->shipment->estimate_date_shipped?->toDateTimeString(),
                'date_shipped' => $order->shipment->date_shipped?->toDateTimeString(),
                'customer_delivered_confirmed_at' => $order->shipment->customer_delivered_confirmed_at?->toDateTimeString(),
            ] : [],
            'address' => $order->address->toArray(),
            'products' => $order->products->toArray(),
            'digital_products' => $digitalProducts,
            'cancellation_reason' => $order->cancellation_reason_message,
            'order_recover_url' => route('public.checkout.recover', ['token' => $order->token ?: $this->getOrderSessionToken()]),
        ], $order);
    }

    protected function getDigitalProductListView(Order $order): ?string
    {
        $hasDownloadableFiles = false;
        $hasLicenseCodesOnly = false;

        foreach ($order->products as $orderProduct) {
            if ($orderProduct->isTypeDigital()) {
                if ($orderProduct->hasFiles()) {
                    $hasDownloadableFiles = true;
                } elseif ($orderProduct->license_code && EcommerceHelperFacade::isEnabledLicenseCodesForDigitalProducts()) {
                    $hasLicenseCodesOnly = true;
                }
            }
        }

        if ($hasDownloadableFiles) {
            return view('plugins/ecommerce::emails.partials.digital-product-list', compact('order'))->render();
        } elseif ($hasLicenseCodesOnly) {
            return view('plugins/ecommerce::emails.partials.digital-product-license-codes', compact('order'))->render();
        }

        return null;
    }

    public function sendOrderConfirmationEmail(Order $order, bool $saveHistory = false, bool $force = false): bool
    {
        try {
            if (
                ! $force &&
                OrderHistory::query()
                    ->where([
                        'action' => OrderHistoryActionEnum::SEND_ORDER_CONFIRMATION_EMAIL,
                        'order_id' => $order->getKey(),
                    ])
                    ->exists()
            ) {
                return false;
            }

            if (
                is_plugin_active('payment')
                && ! in_array($order->payment->status, [PaymentStatusEnum::PENDING, PaymentStatusEnum::COMPLETED])
            ) {
                return false;
            }

            if ($this->sendOrderEmail($order, 'customer_new_order') && $saveHistory) {
                OrderHistory::query()->create([
                    'action' => OrderHistoryActionEnum::SEND_ORDER_CONFIRMATION_EMAIL,
                    'description' => trans('plugins/ecommerce::order.confirmation_email_was_sent_to_customer'),
                    'order_id' => $order->getKey(),
                ]);
            }

            return true;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return false;
    }

    public function sendOrderEmail(
        Order $order,
        string $template,
        string|array|null $email = null,
        array $additionalVariables = [],
        array $args = [],
        bool $debug = false
    ): bool {
        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

        if (! $mailer->templateEnabled($template)) {
            return false;
        }

        $locale = $order->getOrderMetadata('customer_locale');
        if (! $locale) {
            $locale = EmailHandler::getDefaultEmailLocale();
        }

        $customerCurrencyCode = $order->getCustomerCurrency();
        if ($customerCurrencyCode) {
            $customerCurrency = CurrencyModel::query()->where('title', $customerCurrencyCode)->first();
            if ($customerCurrency) {
                cms_currency()->forceCurrentCurrency($customerCurrency);
            }
        }

        try {
            $mailer = $this->setEmailVariables($order, $mailer);

            if (! empty($additionalVariables)) {
                $mailer = $mailer->setVariableValues($additionalVariables);
            }

            if (! $email) {
                $email = $order->user->email ?: $order->address->email;
            }

            return $mailer->sendUsingTemplateWithLocale($template, $email, $locale, $args, $debug);
        } finally {
            cms_currency()->clearForcedCurrency();
        }
    }

    public function sendEmailForDigitalProducts(Order $order): void
    {
        if (! EcommerceHelperFacade::isEnabledSupportDigitalProducts()) {
            return;
        }

        $digitalProductsCount = EcommerceHelperFacade::countDigitalProducts($order->products);

        if ($digitalProductsCount) {
            $hasDownloadableFiles = false;
            $hasLicenseCodesOnly = false;

            foreach ($order->products as $orderProduct) {
                if ($orderProduct->isTypeDigital()) {
                    if ($orderProduct->hasFiles()) {
                        $hasDownloadableFiles = true;
                    } elseif ($orderProduct->license_code && EcommerceHelperFacade::isEnabledLicenseCodesForDigitalProducts()) {
                        $hasLicenseCodesOnly = true;
                    }
                }
            }

            if ($hasDownloadableFiles) {
                $this->sendOrderEmail($order, 'download_digital_products');
            } elseif ($hasLicenseCodesOnly) {
                $this->sendOrderEmail($order, 'digital_product_license_codes');
            }
        }
    }

    public function setOrderCompleted(int|string $orderId, Request $request, int|string $userId = 0): Order
    {
        /**
         * @var Order $order
         */
        $order = Order::query()->findOrFail($orderId);

        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = Carbon::now();
        $order->save();

        event(new OrderCompletedEvent($order));

        do_action(ACTION_AFTER_ORDER_STATUS_COMPLETED_ECOMMERCE, $order, $request);

        OrderHistory::query()->create([
            'action' => OrderHistoryActionEnum::MARK_ORDER_AS_COMPLETED,
            'description' => trans('plugins/ecommerce::order.mark_as_completed.history', [
                'admin' => Auth::check() ? Auth::user()->name : 'system',
                'time' => $order->completed_at,
            ]),
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);

        return $order;
    }

    /**
     * @deprecated
     */
    public function makeInvoicePDF(Order $order): Pdf
    {
        return InvoiceHelperFacade::makeInvoicePDF($order->invoice);
    }

    /**
     * @deprecated
     */
    public function generateInvoice(Order $order): string
    {
        return InvoiceHelperFacade::generateInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function downloadInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::downloadInvoice($order->invoice);
    }

    /**
     * @deprecated
     */
    public function streamInvoice(Order $order): Response
    {
        return InvoiceHelperFacade::streamInvoice($order->invoice);
    }

    public function getShippingMethod(string $method, array|string|null $option = null): array|string|null
    {
        $name = null;

        if ($method == ShippingMethodEnum::DEFAULT) {
            if ($option) {
                $rule = ShippingRule::query()->find($option);
                $name = $rule?->name;
            }

            if (empty($name)) {
                $name = trans('plugins/ecommerce::order.default');
            }
        }

        if (! $name && ShippingMethodEnum::search($method)) {
            $name = ShippingMethodEnum::getLabel($method);
        }

        return $name ?: $method;
    }

    public function processHistoryVariables(OrderHistory|ShipmentHistory $history): ?string
    {
        $order = $history->order;
        $hasOrder = $order && $order->getKey();

        $orderIdVariable = $hasOrder
            ? Html::link(
                route('orders.edit', $order->getKey()),
                $order->code . ' ' . BaseHelper::renderIcon('ti ti-external-link'),
                ['target' => '_blank'],
                null,
                false
            )->toHtml()
            : ($order && $order->code ? BaseHelper::clean($order->code) : '&mdash;');

        $variables = [
            'order_id' => $orderIdVariable,
            'user_name' => $history->user_id === 0 ? trans('plugins/ecommerce::order.system') :
                BaseHelper::clean(
                    $history->user ? $history->user->name : (
                        $hasOrder && $order->user && $order->user->name
                            ? $order->user->name
                            : ($hasOrder && $order->address ? $order->address->name : '')
                    )
                ),
        ];

        $content = $history->description;

        foreach ($variables as $key => $value) {
            $content = str_replace('% ' . $key . ' %', $value, $content);
            $content = str_replace('%' . $key . '%', $value, $content);
            $content = str_replace('% ' . $key . '%', $value, $content);
            $content = str_replace('%' . $key . ' %', $value, $content);
        }

        return $content;
    }

    public function setOrderSessionData(?string $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_replace_recursive($this->getOrderSessionData($token), $data);

        $data = $this->cleanData($data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $data;
    }

    public function getOrderSessionToken(): string
    {
        if (session()->has('tracked_start_checkout')) {
            $token = session('tracked_start_checkout');
        } else {
            $token = md5(Str::random(40));
            session(['tracked_start_checkout' => $token]);
        }

        return $token;
    }

    public function getOrderSessionData(?string $token = null): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = [];
        $sessionKey = md5('checkout_address_information_' . $token);
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
        }

        return $this->cleanData($data);
    }

    public function cleanData(array $data): array
    {
        foreach ($data as $key => $item) {
            if (! is_string($item)) {
                continue;
            }

            $data[$key] = BaseHelper::clean($item);
        }

        return $data;
    }

    public function mergeOrderSessionData(?string $token, string|array $data): array
    {
        if (! $token) {
            $token = $this->getOrderSessionToken();
        }

        $data = array_merge($this->getOrderSessionData($token), $data);

        session([md5('checkout_address_information_' . $token) => $data]);

        return $this->cleanData($data);
    }

    public function clearSessions(?string $token): void
    {
        Cart::instance('cart')->destroy();

        if (auth('customer')->check()) {
            Cart::instance('cart')->deleteCustomerCart(auth('customer')->id());
        } else {
            $guestCartId = request()->cookie('guest_cart_id');
            if ($guestCartId) {
                Cart::instance('cart')->deleteGuestCart($guestCartId);
                cookie()->queue(cookie()->forget('guest_cart_id'));
            }
        }

        session()->forget('applied_coupon_code');
        session()->forget('order_id');
        session()->forget(md5('checkout_address_information_' . $token));
        session()->forget('tracked_start_checkout');
    }

    public function handleAddCart(Product $product, Request $request, bool $relativePath = true): array
    {
        if ($product->status != BaseStatusEnum::PUBLISHED) {
            throw new ProductIsNotActivatedYetException();
        }

        $parentProduct = $product->original_product;

        $options = [];
        if ($requestOption = $request->input('options')) {
            $options = $this->getProductOptionData($requestOption);
        }

        $taxClasses = DB::table('ec_tax_products')
            ->join('ec_taxes', 'ec_taxes.id', '=', 'ec_tax_products.tax_id')
            ->where('ec_tax_products.product_id', $parentProduct->id)
            ->select(['ec_taxes.id', 'ec_taxes.title', 'ec_taxes.percentage'])
            ->get()
            ->mapWithKeys(function ($tax) {
                return [$tax->title => $tax->percentage];
            })
            ->all();

        $taxRate = $parentProduct->total_taxes_percentage;

        if (! $taxClasses && $defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
            $tax = cache()->remember('default_tax_rate_' . $defaultTaxRate, 3600, function () use ($defaultTaxRate) {
                return Tax::query()->where('id', $defaultTaxRate)->first();
            });

            if ($tax) {
                $taxClasses = [$tax->title => $tax->percentage];
                $taxRate = $tax->percentage;
            }
        }

        $image = $product->image ?: $parentProduct->image;

        if (! $relativePath) {
            $image = RvMedia::getImageUrl($image);
        }

        $price = $product->price()->getPrice(false);

        Cart::instance('cart')->add(
            $product->getKey(),
            BaseHelper::clean($parentProduct->name ?: $product->name),
            $request->input('qty', 1),
            $price,
            [
                'image' => $image,
                'attributes' => $product->is_variation ? $this->getTranslatedVariationAttributes($product) : '',
                'taxRate' => $taxRate,
                'taxClasses' => $taxClasses,
                'options' => $options,
                'extras' => $request->input('extras', []),
                'sku' => $product->sku,
                'weight' => $product->weight,
                'price_includes_tax' => $parentProduct->price_includes_tax,
                'product_type' => $parentProduct->product_type,
            ]
        );

        return Cart::instance('cart')->content()->toArray();
    }

    protected function getTranslatedVariationAttributes(Product $product): string
    {
        $variation = ProductVariation::query()
            ->where('product_id', $product->getKey())
            ->first();

        if (! $variation) {
            return '';
        }

        return $variation
            ->productAttributes()
            ->with('productAttributeSet')
            ->get()
            ->sortBy([
                fn ($a) => $a->productAttributeSet?->order ?? 0,
                fn ($a) => $a->order ?? 0,
            ])
            ->map(fn ($attribute) => ($attribute->productAttributeSet?->title ?? '') . ': ' . $attribute->title)
            ->implode(', ');
    }

    public function getProductOptionData(array $data, int|string|null $productId = null): array
    {
        $result = [
            'optionCartValue' => [],
        ];

        $data = array_filter($data);

        if (empty($data)) {
            return $result;
        }

        foreach ($data as $key => $option) {
            if (empty($option) || ! is_array($option) || ! isset($option['values'])) {
                continue;
            }

            $optionValue = OptionValue::query()
                ->select(['option_value', 'affect_price', 'affect_type'])
                ->where('option_id', $key);

            if ($option['option_type'] != 'field') {
                if (is_array($option['values'])) {
                    $optionValue->whereIn('option_value', $option['values']);
                } else {
                    $optionValue->whereIn('option_value', [0 => $option['values']]);
                }
            }

            $result['optionCartValue'][$key] = $optionValue->get()->toArray();

            $optionModel = Option::query()->find($key);

            foreach ($result['optionCartValue'][$key] as &$item) {
                $item['option_type'] = $option['option_type'];
                $item['price_per_product'] = (bool) $optionModel?->price_per_product;
            }

            if (
                $option['option_type'] == 'field' &&
                count($result['optionCartValue']) > 0
            ) {
                $result['optionCartValue'][$key][0]['option_value'] = $option['values'];
            }
        }

        $result['optionInfo'] = Option::query()
            ->whereIn('id', array_keys($data))
            ->pluck('name', 'id')
            ->when($productId, function ($query) use ($productId): void {
                $query->where('product_id', $productId);
            })
            ->all();

        return $result;
    }

    public function processAddressOrder(int|string $currentUserId, array $sessionData, Request $request): array
    {
        $address = null;

        $sessionAddressId = Arr::get($sessionData, 'address_id');
        if ($currentUserId && ! $sessionAddressId) {
            $address = Address::query()
                ->where([
                    'customer_id' => $currentUserId,
                    'is_default' => true,
                ])
                ->first();

            if ($address) {
                $sessionData['address_id'] = $address->id;
            }
        } elseif ($request->input('address.address_id') && $request->input('address.address_id') !== 'new') {
            $address = Address::query()->find($request->input('address.address_id'));
            if (! empty($address)) {
                $sessionData['address_id'] = $address->getKey();
            }
        }

        if ($sessionAddressId && $sessionAddressId !== 'new') {
            $address = Address::query()->find($sessionAddressId);
        }

        if (! empty($address)) {
            $addressData = [
                'name' => $address->name,
                'phone' => $address->phone,
                'email' => $address->email,
                'country' => $address->country,
                'state' => $address->state,
                'city' => $address->city,
                'address' => $address->address,
                'zip_code' => $address->zip_code,
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        } elseif ((array) $request->input('address', [])) {
            $addressData = array_merge(
                ['order_id' => Arr::get($sessionData, 'created_order_id', 0)],
                (array) $request->input('address', [])
            );
        } else {
            $addressData = [
                'name' => Arr::get($sessionData, 'name'),
                'phone' => Arr::get($sessionData, 'phone'),
                'email' => Arr::get($sessionData, 'email'),
                'country' => Arr::get($sessionData, 'country'),
                'state' => Arr::get($sessionData, 'state'),
                'city' => Arr::get($sessionData, 'city'),
                'address' => Arr::get($sessionData, 'address'),
                'zip_code' => Arr::get($sessionData, 'zip_code'),
                'order_id' => Arr::get($sessionData, 'created_order_id', 0),
            ];
        }

        return $this->checkAndCreateOrderAddress($addressData, $sessionData);
    }

    /**
     * Ensure a finalized order always has a shipping address row.
     *
     * Only runs when the SHIPPING row is genuinely missing. Recovers contact/address
     * data from the first available local source, in priority order:
     *   1. The order's BILLING address row (often saved when shipping was skipped).
     *   2. The checkout session data keyed by the order token (survives same-request flows).
     *   3. The linked customer's default/earliest saved address (logged-in buyers).
     *   4. The linked payment record's metadata (gateways that persist contact there).
     *
     * Purely additive: creates the row only when none exists; never overwrites or deletes.
     */
    protected function ensureShippingAddressBackfilled(Order $order): void
    {
        $hasShippingAddress = OrderAddress::query()
            ->where('order_id', $order->getKey())
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->exists();

        if ($hasShippingAddress) {
            return;
        }

        $addressKeys = ['name', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip_code'];

        // Digital-only orders intentionally carry only minimal contact (name/email/phone)
        // and no physical address — see the digital branch in checkAndCreateOrderAddress().
        // Mirror that here so the backfill never adds a shipping address the checkout flow
        // would have omitted. An order needs shipping when digital products are unsupported
        // or it contains at least one non-digital product.
        $requiresShipping = ! EcommerceHelper::isEnabledSupportDigitalProducts()
            || $order->products()->where('product_type', '!=', ProductTypeEnum::DIGITAL)->exists();

        if (! $requiresShipping) {
            $addressKeys = ['name', 'phone', 'email'];
        }

        $data = [];

        // 1. Billing row on the same order.
        $billing = OrderAddress::query()
            ->where('order_id', $order->getKey())
            ->where('type', OrderAddressTypeEnum::BILLING)
            ->first();

        if ($billing) {
            $data = Arr::only($billing->toArray(), $addressKeys);
        }

        // 2. Checkout session data (still present in same-request payment flows).
        if (empty($data['name']) && $order->token) {
            $sessionData = $this->getOrderSessionData($order->token);
            foreach ($addressKeys as $key) {
                if (empty($data[$key]) && ! empty($sessionData[$key])) {
                    $data[$key] = $sessionData[$key];
                }
            }
        }

        // 3. Customer's saved address (logged-in buyers).
        if (empty($data['name']) && $order->user_id) {
            $customerAddress = Address::query()
                ->where('customer_id', $order->user_id)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->first();

            if ($customerAddress) {
                foreach ($addressKeys as $key) {
                    if (empty($data[$key]) && ! empty($customerAddress->{$key})) {
                        $data[$key] = $customerAddress->{$key};
                    }
                }
            }
        }

        // 4. Payment metadata (gateways that persist customer contact there).
        if (empty($data['name']) && $order->payment_id) {
            $payment = Payment::query()->find($order->payment_id);
            $metadata = $payment ? (array) $payment->metadata : [];
            $map = [
                'name' => ['customer_name', 'name'],
                'email' => ['customer_email', 'email'],
                'phone' => ['customer_phone', 'phone'],
            ];

            foreach ($map as $field => $candidates) {
                if (! empty($data[$field])) {
                    continue;
                }

                foreach ($candidates as $candidate) {
                    if (! empty($metadata[$candidate])) {
                        $data[$field] = $metadata[$candidate];

                        break;
                    }
                }
            }
        }

        $data = $this->cleanData(array_filter($data, fn ($value) => $value !== null && $value !== ''));

        // Need at least a name or email to make the row meaningful.
        if (empty($data['name']) && empty($data['email'])) {
            Log::warning('[ec_order_addresses] Finalizing order without a shipping address; no local source to backfill', [
                'order_id' => $order->getKey(),
                'order_code' => $order->code,
                'user_id' => $order->user_id,
                'payment_id' => $order->payment_id,
            ]);

            return;
        }

        $data['order_id'] = $order->getKey();
        $data['type'] = OrderAddressTypeEnum::SHIPPING;

        OrderAddress::query()->updateOrCreate(
            [
                'order_id' => $order->getKey(),
                'type' => OrderAddressTypeEnum::SHIPPING,
            ],
            $data
        );

        Log::warning('[ec_order_addresses] Backfilled missing shipping address at order finalize', [
            'order_id' => $order->getKey(),
            'order_code' => $order->code,
            'source_fields' => array_keys($data),
        ]);
    }

    public function checkAndCreateOrderAddress(array $addressData, array $sessionData): array
    {
        $addressData = $this->cleanData($addressData);

        $this->storeOrderBillingAddress($addressData, $sessionData);

        if (! Arr::get($sessionData, 'is_save_order_shipping_address', true)) {
            if ($createdOrderId = Arr::get($sessionData, 'created_order_id')) {
                // For digital-only orders, save minimal customer info (name, email)
                $minimalAddressData = Arr::only($addressData, ['name', 'email', 'phone']);
                $minimalAddressData['order_id'] = $createdOrderId;
                $minimalAddressData['type'] = OrderAddressTypeEnum::SHIPPING;

                if (! empty($minimalAddressData['name']) || ! empty($minimalAddressData['email'])) {
                    $createdOrderAddress = OrderAddress::query()
                        ->updateOrCreate(
                            [
                                'order_id' => $createdOrderId,
                                'type' => OrderAddressTypeEnum::SHIPPING,
                            ],
                            $minimalAddressData
                        );

                    $sessionData['created_order_address'] = true;
                    $sessionData['created_order_address_id'] = $createdOrderAddress->getKey();
                } else {
                    Log::warning('[ec_order_addresses] Deleting shipping address row: digital-only order has empty name and email', [
                        'order_id' => $createdOrderId,
                        'session_address_id' => Arr::get($sessionData, 'address_id'),
                    ]);

                    OrderAddress::query()
                        ->where([
                            'order_id' => $createdOrderId,
                            'type' => OrderAddressTypeEnum::SHIPPING,
                        ])
                        ->delete();
                    Arr::forget($sessionData, 'created_order_address');
                    Arr::forget($sessionData, 'created_order_address_id');
                }
            }
        } elseif ($addressData && ! empty($addressData['name'])) {
            $createdOrderAddress = $this->createOrderAddress($addressData, $sessionData);
            if ($createdOrderAddress) {
                $sessionData['created_order_address'] = true;
                $sessionData['created_order_address_id'] = $createdOrderAddress->getKey();
            }
        } elseif ($createdOrderId = Arr::get($sessionData, 'created_order_id')) {
            // Skip is silent in older versions — log so missing-address bugs are debuggable.
            // Only log when the order genuinely lacks a shipping row: the empty-addressData case
            // also fires on the FINAL postCheckout for normal flows (the address was already
            // created during the address-information step, so silently skipping is correct here).
            $hasShippingAddress = OrderAddress::query()
                ->where('order_id', $createdOrderId)
                ->where('type', OrderAddressTypeEnum::SHIPPING)
                ->exists();

            if (! $hasShippingAddress) {
                Log::warning('[ec_order_addresses] Skipping shipping address creation: empty name in addressData and no existing row', [
                    'order_id' => $createdOrderId,
                    'session_address_id' => Arr::get($sessionData, 'address_id'),
                    'has_address_data' => ! empty($addressData),
                    'address_keys' => $addressData ? array_keys($addressData) : [],
                ]);
            }
        }

        return $sessionData;
    }

    protected function storeOrderBillingAddress(array $data, array $sessionData = []): void
    {
        if (! EcommerceHelperFacade::isBillingAddressEnabled()) {
            return;
        }

        $orderId = Arr::get($data, 'order_id', Arr::get($data, 'created_order_id'));
        if ($orderId) {
            $billingAddressSameAsShippingAddress = Arr::get(
                $sessionData,
                'billing_address_same_as_shipping_address',
                '1'
            );

            if (
                ! $billingAddressSameAsShippingAddress ||
                ! Arr::get($sessionData, 'is_save_order_shipping_address', true)
            ) {
                $addressData = Arr::only(
                    $sessionData,
                    ['name', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip_code']
                );

                if ($billingAddressSameAsShippingAddress) {
                    $billingAddressData = $addressData;
                } else {
                    $billingAddressData = Arr::get($sessionData, 'billing_address', []);
                }

                $rules = EcommerceHelperFacade::getCustomerAddressValidationRules();
                $validator = Validator::make($billingAddressData, $rules);
                if ($validator->fails()) {
                    return;
                }

                $billingAddressData['order_id'] = $orderId;
                $billingAddressData['type'] = OrderAddressTypeEnum::BILLING;

                OrderAddress::query()
                    ->updateOrCreate(
                        [
                            'order_id' => $orderId,
                            'type' => OrderAddressTypeEnum::BILLING,
                        ],
                        $billingAddressData
                    );
            } else {
                OrderAddress::query()
                    ->where([
                        'order_id' => $orderId,
                        'type' => OrderAddressTypeEnum::BILLING,
                    ])
                    ->delete();
            }
        }
    }

    protected function createOrderAddress(array $data, ?array $sessionData = []): OrderAddress|bool
    {
        $data['type'] = OrderAddressTypeEnum::SHIPPING;

        if ($orderId = Arr::get($sessionData, 'created_order_id')) {
            /**
             * @var OrderAddress $orderAddress
             */
            $orderAddress = OrderAddress::query()
                ->updateOrCreate(
                    [
                        'order_id' => $orderId,
                        'type' => OrderAddressTypeEnum::SHIPPING,
                    ],
                    $data
                );

            return $orderAddress;
        }

        $rules = EcommerceHelperFacade::getCustomerAddressValidationRules();

        $products = Cart::instance('cart')->products();

        $countDigitalProducts = EcommerceHelperFacade::countDigitalProducts($products);
        if (! auth('customer')->check() && $countDigitalProducts) {
            $rules['email'] = 'required|max:60|min:6';
            if ($countDigitalProducts == $products->count()) {
                $keys = [
                    'country',
                    'state',
                    'city',
                    'address',
                    'phone',
                    'zip_code',
                ];
                $rules = (new CheckoutRequest())->removeRequired($rules, $keys);
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            // Silent failure in older versions — log so missing-address bugs are debuggable.
            Log::warning('[ec_order_addresses] Address validation failed; row not created', [
                'order_id' => Arr::get($data, 'order_id'),
                'errors' => $validator->errors()->toArray(),
                'data_keys' => array_keys($data),
            ]);

            return false;
        }

        $orderId = Arr::get($data, 'order_id');

        if ($orderId) {
            /**
             * @var OrderAddress $orderAddress
             */
            $orderAddress = OrderAddress::query()
                ->updateOrCreate(
                    [
                        'order_id' => $orderId,
                        'type' => OrderAddressTypeEnum::SHIPPING,
                    ],
                    $data
                );

            return $orderAddress;
        }

        /**
         * @var OrderAddress $orderAddress
         */
        $orderAddress = OrderAddress::query()->create($data);

        return $orderAddress;
    }

    public function processOrderProductData(array|Collection $products, array $sessionData): array
    {
        $createdOrderProduct = Arr::get($sessionData, 'created_order_product');

        if (is_string($createdOrderProduct) && $createdOrderProduct !== '') {
            $createdOrderProduct = Carbon::parse($createdOrderProduct);
        }

        $cartItems = $products['products']->pluck('cartItem');

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        if (! $createdOrderProduct instanceof CarbonInterface || ! $createdOrderProduct->eq($lastUpdatedAt)) {
            $orderProducts = OrderProduct::query()
                ->where('order_id', $sessionData['created_order_id'])
                ->get();
            $productIds = [];

            foreach ($cartItems as $cartItem) {
                $productByCartItem = $products['products']->firstWhere('id', $cartItem->id);

                $data = [
                    'order_id' => $sessionData['created_order_id'],
                    'product_id' => $cartItem->id,
                    'product_name' => $cartItem->name,
                    'product_image' => $cartItem->options['image'],
                    'qty' => $cartItem->qty,
                    'weight' => $productByCartItem->weight,
                    'price' => EcommerceHelper::roundPrice($cartItem->price),
                    'tax_amount' => $cartItem->taxTotal,
                    'options' => [],
                    'product_type' => $productByCartItem->product_type,
                ];

                if ($cartItem->options) {
                    $data['options'] = $cartItem->options;
                }

                if (isset($cartItem->options['options'])) {
                    $data['product_options'] = $cartItem->options['options'];
                }

                // Use product_id AND price to find matching order product
                // This handles cases where the same product exists in cart with different prices
                // (e.g., bundle vs non-bundle items)
                $orderProduct = $orderProducts->first(function ($op) use ($cartItem) {
                    return $op->product_id == $cartItem->id
                        && EcommerceHelper::roundPrice($op->price) == EcommerceHelper::roundPrice($cartItem->price);
                });

                if ($orderProduct) {
                    $orderProduct->fill($data);
                    $orderProduct->save();
                    // Remove from collection to prevent reuse in next iteration
                    $orderProducts = $orderProducts->reject(fn ($op) => $op->id === $orderProduct->id);
                } else {
                    /**
                     * @var OrderProduct $orderProduct
                     */
                    $orderProduct = OrderProduct::query()->create($data);

                    OrderProductCreatedEvent::dispatch($orderProduct);
                    do_action('ecommerce_after_each_order_product_created', $orderProduct);
                }

            }

            // Delete any remaining unmatched order products
            // (products that were removed from cart since order was created)
            foreach ($orderProducts as $orderProduct) {
                $orderProduct->delete();
            }

            $sessionData['created_order_product'] = $lastUpdatedAt;
        }

        return $sessionData;
    }

    /**
     * @param       $sessionData
     * @param       $request
     * @param       $cartItems
     * @param       $order
     * @param array $generalData
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function processOrderInCheckout(
        $sessionData,
        $request,
        $cartItems,
        $order,
        array $generalData
    ): array {
        $createdOrder = Arr::get($sessionData, 'created_order');
        $createdOrderId = Arr::get($sessionData, 'created_order_id');

        if (is_string($createdOrder) && $createdOrder !== '') {
            $createdOrder = Carbon::parse($createdOrder);
        }

        $lastUpdatedAt = Cart::instance('cart')->getLastUpdatedAt();

        $paymentFee = 0;
        $paymentMethod = $request->input('payment_method');
        if ($paymentMethod && is_plugin_active('payment')) {
            $orderAmount = Cart::instance('cart')->rawTotalByItems($cartItems);
            $paymentFee = PaymentFeeHelper::calculateFee($paymentMethod, $orderAmount);
        }

        $amount = Cart::instance('cart')->rawTotalByItems($cartItems) + $paymentFee;

        $data = array_merge([
            'amount' => $amount,
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'payment_fee' => $paymentFee,
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
        ], $generalData);

        if ($createdOrder && $createdOrderId) {
            if ($order && (! $createdOrder instanceof CarbonInterface || ! $createdOrder->eq($lastUpdatedAt))) {
                $order->fill($data);
            }
        }

        if (! $order) {
            $data = array_merge($data, [
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'is_finished' => false,
            ]);

            $order = Order::query()->create($data);

            $order->storeCustomerLocale();
            $order->storeCustomerCurrency();
        }

        $sessionData['created_order'] = $lastUpdatedAt;
        $sessionData['created_order_id'] = $order->id;

        return [$sessionData, $order];
    }

    public function createOrder(Request $request, int|string $currentUserId, string $token, array $cartItems)
    {
        $paymentFee = 0;
        $paymentMethod = $request->input('payment_method');
        if ($paymentMethod && is_plugin_active('payment')) {
            $orderAmount = Cart::instance('cart')->rawTotalByItems($cartItems);
            $paymentFee = PaymentFeeHelper::calculateFee($paymentMethod, $orderAmount);
        }

        $amount = Cart::instance('cart')->rawTotalByItems($cartItems) + $paymentFee;

        $request->merge([
            'amount' => $amount,
            'user_id' => $currentUserId,
            'shipping_method' => $request->input('shipping_method', ShippingMethodEnum::DEFAULT),
            'shipping_option' => $request->input('shipping_option'),
            'shipping_amount' => 0,
            'payment_fee' => $paymentFee,
            'tax_amount' => Cart::instance('cart')->rawTaxByItems($cartItems),
            'sub_total' => Cart::instance('cart')->rawSubTotalByItems($cartItems),
            'coupon_code' => session()->get('applied_coupon_code'),
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'is_finished' => false,
            'token' => $token,
        ]);

        $order = Order::query()->create($request->input());

        $order->storeCustomerLocale();
        $order->storeCustomerCurrency();

        return $order;
    }

    public function confirmPayment(Order $order, ?User $user = null): bool
    {
        if (! is_plugin_active('payment')) {
            return false;
        }

        $payment = $order->payment;

        if (! $payment) {
            return false;
        }

        $user = $user ?? auth()->user();

        $payment->status = PaymentStatusEnum::COMPLETED;
        $payment->amount = $payment->amount ?: 0;
        $payment->user_id = $user?->getKey() ?: 0;
        $payment->save();

        event(new OrderPaymentConfirmedEvent($order, $user));

        $this->sendOrderEmail($order, 'order_confirm_payment');

        OrderHistory::query()->create([
            'action' => OrderHistoryActionEnum::CONFIRM_PAYMENT,
            'description' => trans('plugins/ecommerce::order.payment_was_confirmed_by', [
                'money' => format_price($order->amount),
            ]),
            'order_id' => $order->getKey(),
            'user_id' => $user?->getKey(),
        ]);

        if (EcommerceHelperFacade::isEnabledSupportDigitalProducts()) {
            $digitalProductsCount = EcommerceHelperFacade::countDigitalProducts($order->products);

            if (
                $digitalProductsCount === $order->products->count()
                && EcommerceHelperFacade::isAutoCompleteDigitalOrdersAfterPayment()
            ) {
                $this->setOrderCompleted($order->getKey(), request(), $user?->getKey() ?? 0);
            } elseif ($digitalProductsCount > 0) {
                event(new OrderCompletedEvent($order));
            }
        }

        return true;
    }

    public function cancelOrder(Order $order, ?string $reason = null, ?string $reasonDescription = null): Order
    {
        $order->status = OrderStatusEnum::CANCELED;
        $order->is_confirmed = true;

        if ($reason) {
            $order->cancellation_reason = $reason;
            $order->cancellation_reason_description = $reasonDescription;
        }

        $order->save();

        if (
            is_plugin_active('payment')
            && $order->payment_id
            && $order->payment->id
            && $order->payment->status == PaymentStatusEnum::PENDING
        ) {
            $payment = $order->payment;
            $payment->status = PaymentStatusEnum::CANCELED;
            $payment->save();
        }

        event(new OrderCancelledEvent($order, $reason, $reasonDescription));

        $order->restockProductQuantities(updateRestockQuantity: true);

        if ($order->coupon_code && $order->user_id) {
            Discount::getFacadeRoot()->afterOrderCancelled($order->coupon_code, $order->user_id);
        }

        $this->sendOrderEmail($order, 'customer_cancel_order');

        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
        if ($mailer->templateEnabled('order_cancellation_to_admin')) {
            $mailer = $this->setEmailVariables($order, $mailer);
            $mailer->sendUsingTemplate('order_cancellation_to_admin', EcommerceHelper::getAdminNotificationEmails());
        }

        if (AdminHelper::isInAdmin() && Auth::check()) {
            $this->sendOrderEmail($order, 'admin_cancel_order');
        }

        return $order;
    }

    public function shippingStatusDelivered(Shipment $shipment, Request $request, int|string $userId = 0): Order
    {
        return $this->setOrderCompleted($shipment->order_id, $request, $userId);
    }

    public function getOrderBankInfo(Order|EloquentCollection $orders): ?string
    {
        if (! is_plugin_active('payment')) {
            return null;
        }

        try {
            if (! $orders instanceof EloquentCollection) {
                $collection = new EloquentCollection();
                $collection->add($orders);
                $orders = $collection;
            }

            $orders = $orders->filter(function ($item) {
                return $item->payment->payment_channel == PaymentMethodEnum::BANK_TRANSFER &&
                    $item->payment->status == PaymentStatusEnum::PENDING;
            });

            if ($orders->isEmpty()) {
                return null;
            }

            $bankInfo = get_payment_setting('description', $orders->first()->payment->payment_channel);

            $orderAmount = 0;
            $orderCode = '';

            foreach ($orders as $item) {
                $orderAmount += $item->amount;
                $orderCode .= $item->code . ', ';
            }

            $orderCode = rtrim(trim($orderCode), ',');

            $bankInfo = view(
                'plugins/ecommerce::orders.partials.bank-transfer-info',
                compact('bankInfo', 'orderAmount', 'orderCode', 'orders')
            )->render();

            return apply_filters('ecommerce_order_bank_info', $bankInfo, $orders);
        } catch (Throwable) {
            return null;
        }
    }

    public function confirmOrder(Order $order): void
    {
        if ($order->is_confirmed) {
            return;
        }

        $order->is_confirmed = 1;
        if ($order->status == OrderStatusEnum::PENDING) {
            $order->status = OrderStatusEnum::PROCESSING;
        }

        $order->save();

        if (is_plugin_active('payment')) {
            $payment = Payment::query()->where('order_id', $order->getKey())->first();

            if ($payment && Auth::check()) {
                $payment->user_id = Auth::id();
                $payment->save();
            }
        }

        event(
            new OrderConfirmedEvent($order, EcommerceHelperFacade::isOrderAutoConfirmedEnabled() ? null : Auth::user())
        );

        OrderHistory::query()->create([
            'action' => OrderHistoryActionEnum::CONFIRM_ORDER,
            'description' => trans('plugins/ecommerce::order.order_was_verified_by'),
            'order_id' => $order->getKey(),
            'user_id' => Auth::id() ?: 0,
        ]);

        $this->sendOrderEmail($order, 'order_confirm');
    }

    public function createOrUpdateIncompleteOrder(array $data, ?Order $order = null): Order|null|false
    {
        $data['is_finished'] = false;

        if ($order) {
            $order->fill($data);
            $order->save();
        } else {
            $order = Order::query()->create($data);

            $order->storeCustomerLocale();
            $order->storeCustomerCurrency();
        }

        /**
         * @var Order $order
         */

        $this->captureFootprints($order);

        do_action('ecommerce_create_order_from_data', $data, $order);

        return $order;
    }

    public function captureFootprints(Order $order): void
    {
        if ($order->referral()->exists()) {
            return;
        }

        $referrals = app(FootprinterInterface::class)->getFootprints();

        if ($referrals) {
            try {
                $order->referral()->create($referrals);
            } catch (Throwable) {
                $referrals = array_map(function (?string $item) {
                    return is_string($item) ? substr($item, 0, 190) : $item;
                }, $referrals);

                rescue(function () use ($order, $referrals): void {
                    $order->referral()->create($referrals);
                }, report: false);
            }
        }
    }
}
