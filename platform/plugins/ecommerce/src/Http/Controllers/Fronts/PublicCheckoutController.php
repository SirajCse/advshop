<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Rules\EmailRule;
use Botble\Ecommerce\AdsTracking\FacebookPixel;
use Botble\Ecommerce\AdsTracking\GoogleTagManager;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\OrderHistoryActionEnum;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderProductCreatedEvent;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Forms\Fronts\CheckoutForm;
use Botble\Ecommerce\Http\Requests\ApplyCouponRequest;
use Botble\Ecommerce\Http\Requests\CheckoutRequest;
use Botble\Ecommerce\Http\Requests\SaveCheckoutInformationRequest;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount as DiscountModel;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Botble\Ecommerce\Services\HandleApplyPromotionsService;
use Botble\Ecommerce\Services\HandleCheckoutOrderData;
use Botble\Ecommerce\Services\HandleRemoveCouponService;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Botble\Ecommerce\Services\HandleTaxService;
use Botble\Optimize\Facades\OptimizerHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentFeeHelper;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PublicCheckoutController extends BaseController
{
    public function __construct()
    {
        if (class_exists(OptimizerHelper::class)) {
            OptimizerHelper::disable();
        }
    }

    public function getCheckout(
        string $token,
        Request $request,
        HandleTaxService $handleTaxService,
        HandleCheckoutOrderData $handleCheckoutOrderData,
    ) {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.login') . '?redirect=' . urlencode($request->fullUrl()));
        }

        if ($token !== session('tracked_start_checkout')) {
            $order = Order::query()->where(['token' => $token, 'is_finished' => false])->first();

            if (! $order) {
                return $this
                    ->httpResponse()
                    ->setNextUrl(BaseHelper::getHomepageUrl());
            }
        }

        if (
            ! $request->session()->has('error_msg') &&
            $request->input('error') == 1 &&
            $request->input('error_type') == 'payment'
        ) {
            $message = $request->input('error_message') ?: __('Payment failed! Something wrong with your payment. Please try again.');

            $request->session()->flash('error_msg', $message);

            return redirect()->to(route('public.checkout.information', $token))->with('error_msg', $message);
        }

        $sessionCheckoutData = OrderHelper::getOrderSessionData($token);

        if (! auth('customer')->check()) {
            $sessionCheckoutData = $this->prefillAddressFromCookie($sessionCheckoutData);
            OrderHelper::setOrderSessionData($token, $sessionCheckoutData);
        }

        /**
         * @var Collection $products
         */
        $products = Cart::instance('cart')->products();
        if ($products->isEmpty()) {
            return $this
                ->httpResponse()
                ->setNextUrl(route('public.cart'));
        }

        foreach ($products as $product) {
            /**
             * @var Product $product
             */
            if ($product->isOutOfStock()) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setNextUrl(route('public.cart'))
                    ->setMessage(
                        trans('plugins/ecommerce::products.cart.out_of_stock', ['product' => $product->original_product->name])
                    );
            }
        }

        if (
            EcommerceHelper::isEnabledSupportDigitalProducts()
            && ! EcommerceHelper::canCheckoutForDigitalProducts($products)
        ) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('customer.login') . '?redirect=' . urlencode($request->fullUrl()))
                ->setMessage(__('Your shopping cart has digital product(s), so you need to sign in to continue!'));
        }

        $handleTaxService->execute($products, $sessionCheckoutData);

        $sessionCheckoutData = $this->processOrderData($token, $sessionCheckoutData, $request);

        $isShowAddressForm = EcommerceHelper::isSaveOrderShippingAddress($products);

        $checkoutOrderData = $handleCheckoutOrderData->execute(
            $request,
            $products,
            $token,
            $sessionCheckoutData
        );

        $shipping = $checkoutOrderData->shipping;
        $defaultShippingMethod = $checkoutOrderData->defaultShippingMethod;
        $defaultShippingOption = $checkoutOrderData->defaultShippingOption;
        $promotionDiscountAmount = $checkoutOrderData->promotionDiscountAmount;
        $couponDiscountAmount = $checkoutOrderData->couponDiscountAmount;
        $shippingAmount = $checkoutOrderData->shippingAmount;
        $paymentFee = $checkoutOrderData->paymentFee;
        $shippingTaxAmount = $checkoutOrderData->shippingTaxAmount;

        $data = compact(
            'token',
            'shipping',
            'defaultShippingMethod',
            'defaultShippingOption',
            'shippingAmount',
            'promotionDiscountAmount',
            'couponDiscountAmount',
            'sessionCheckoutData',
            'products',
            'isShowAddressForm',
            'paymentFee',
            'shippingTaxAmount',
        );

        if (auth('customer')->check()) {
            $addresses = auth('customer')->user()->addresses;
            $isAvailableAddress = ! $addresses->isEmpty();

            if (Arr::get($sessionCheckoutData, 'is_new_address')) {
                $sessionAddressId = 'new';
            } else {
                $sessionAddressId = Arr::get(
                    $sessionCheckoutData,
                    'address_id',
                    $isAvailableAddress ? $addresses->first()->id : null
                );
                if (! $sessionAddressId && $isAvailableAddress) {
                    $address = $addresses->firstWhere('is_default') ?: $addresses->first();
                    $sessionAddressId = $address->id;
                }
            }

            $data = array_merge($data, compact('addresses', 'isAvailableAddress', 'sessionAddressId'));
        }

        // @phpstan-ignore-next-line
        $discountsQuery = DiscountModel::query()
            ->where('type', DiscountTypeEnum::COUPON)
            ->where('display_at_checkout', true)
            ->active()
            ->available();

        $discounts = apply_filters('ecommerce_checkout_discounts_query', $discountsQuery, $products)->get();

        $rawTotal = $checkoutOrderData->rawTotal;
        $orderAmount = $checkoutOrderData->orderAmount;

        $data = [...$data, 'discounts' => $discounts, 'rawTotal' => $rawTotal, 'orderAmount' => $orderAmount];

        $productsArray = $products->all();

        app(GoogleTagManager::class)->beginCheckout($productsArray, $orderAmount);
        app(FacebookPixel::class)->checkout($productsArray, $orderAmount);

        $checkoutView = Theme::getThemeNamespace('views.ecommerce.orders.checkout');

        if (view()->exists($checkoutView)) {
            return view($checkoutView, $data);
        }

        add_filter('payment_order_total_amount', function () use ($orderAmount, $paymentFee) {
            return $orderAmount - $paymentFee;
        }, 120);

        return view(
            'plugins/ecommerce::orders.checkout',
            ['orderAmount' => $orderAmount, 'checkoutForm' => CheckoutForm::createFromArray($data)]
        );
    }

    protected function processOrderData(
        string $token,
        array $sessionData,
        Request $request,
        bool $finished = false
    ): array {
        if ($request->has('billing_address_same_as_shipping_address')) {
            $sessionData['billing_address_same_as_shipping_address'] = $request->boolean(
                'billing_address_same_as_shipping_address'
            );
        }

        if ($request->has('billing_address')) {
            $sessionData['billing_address'] = $request->input('billing_address');
        }

        if ($request->has('address.address_id')) {
            $sessionData['is_new_address'] = $request->input('address.address_id') == 'new';
        }

        if ($request->input('address', [])) {
            if (! isset($sessionData['created_account']) && $request->input('create_account') == 1) {
                $validator = Validator::make($request->input(), [
                    'password' => ['required', 'min:6'],
                    'password_confirmation' => ['required', 'same:password'],
                    'address.email' => ['required', new EmailRule(), Rule::unique((new Customer())->getTable(), 'email')],
                    'address.name' => ['required', 'min:3', 'max:120'],
                ]);

                if ($validator->passes()) {
                    $customerId = null;

                    try {
                        /**
                         * @var Customer $customer
                         */
                        $customer = Customer::query()->create([
                            'name' => $request->input('address.name'),
                            'email' => $request->input('address.email'),
                            'phone' => $request->input('address.phone'),
                            'password' => Hash::make($request->input('password')),
                        ]);

                        $customerId = $customer->getKey();

                        auth('customer')->loginUsingId($customer->getKey(), true);

                        event(new Registered($customer));

                        $sessionData['created_account'] = true;
                    } catch (Throwable $exception) {
                        BaseHelper::logError($exception);
                    }

                    if (! $customerId && auth('customer')->check()) {
                        $customerId = auth('customer')->id();
                    }

                    if ($customerId) {
                        $address = Address::query()
                            ->create(
                                array_merge($request->input('address'), [
                                    'customer_id' => $customerId,
                                    'is_default' => true,
                                ])
                            );

                        $request->merge(['address.address_id' => $address->getKey()]);
                        $sessionData['address_id'] = $address->getKey();
                    }
                }
            }

            if ($finished && auth('customer')->check()) {
                $customer = auth('customer')->user();
                if ($customer->addresses->count() == 0 || $request->input('address.address_id') == 'new') {
                    $address = Address::query()
                        ->create(
                            array_merge($request->input('address', []), [
                                'customer_id' => auth('customer')->id(),
                                'is_default' => $customer->addresses->count() == 0,
                            ])
                        );

                    $request->merge(['address.address_id' => $address->id]);
                    $sessionData['address_id'] = $address->id;
                }
            }
        }

        $address = null;

        if (($addressId = $request->input('address.address_id')) && $addressId !== 'new') {
            $address = Address::query()->find($addressId);
            if ($address) {
                $sessionData['address_id'] = $address->getKey();
            }
        } elseif (auth('customer')->check() && ! Arr::get($sessionData, 'address_id')) {
            $address = Address::query()->where([
                'customer_id' => auth('customer')->id(),
                'is_default' => true,
            ])->first();

            if ($address) {
                $sessionData['address_id'] = $address->id;
            }
        }

        $addressData = [
            'billing_address_same_as_shipping_address' => Arr::get(
                $sessionData,
                'billing_address_same_as_shipping_address',
                true
            ),
            'billing_address' => Arr::get($sessionData, 'billing_address', []),
        ];

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
                'address_id' => $address->id,
            ];
        } elseif ($addressFromInput = (array) $request->input('address', [])) {
            $addressData = $addressFromInput;
        } elseif (! empty($sessionData['name'])) {
            // Fallback: previous postSaveInformation merged address fields into
            // sessionData top-level. Mirrors the same fallback in
            // OrderHelper::processAddressOrder. Without this, guests whose final
            // postCheckout request lacks address[*] fields (split-step UIs, autofill,
            // broken JS) get orders with NULL shipping address — see backfill
            // migration 2026_04_25_000001_backfill_missing_order_shipping_addresses.
            $addressData = Arr::only($sessionData, [
                'name', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip_code',
            ]);
        }

        // Safety net: when any address field is still empty after the 3-branch
        // chain above, fill it from the next available source. Catches cases
        // where postSaveInformation never fired (broken JS / fast click / autofill)
        // AND $request->input('address') arrives empty for whatever upstream
        // reason — without this, $addressData stays at the initial billing-only
        // shape, checkAndCreateOrderAddress warns + skips, and every shipping-
        // carrier plugin loses its destination.
        //
        // Field list respects admin settings: zip_code is opt-in (default off);
        // any field in EcommerceHelper::getHiddenFieldsAtCheckout() is excluded
        // so we never re-introduce a value the admin has chosen to hide.
        $safetyNetFields = ['name', 'phone', 'email', 'country', 'state', 'city', 'address'];
        if (EcommerceHelper::isZipCodeEnabled()) {
            $safetyNetFields[] = 'zip_code';
        }
        $safetyNetFields = array_diff(
            $safetyNetFields,
            (array) EcommerceHelper::getHiddenFieldsAtCheckout()
        );

        foreach ($safetyNetFields as $safetyNetField) {
            if (! empty($addressData[$safetyNetField])) {
                continue;
            }

            $value = $request->input("address.$safetyNetField")
                ?: Arr::get($sessionData, $safetyNetField)
                ?: $request->input($safetyNetField);

            if (! empty($value)) {
                $addressData[$safetyNetField] = $value;
            }
        }

        $addressData = OrderHelper::cleanData($addressData);

        $sessionData = array_merge($sessionData, $addressData);

        Cart::instance('cart')->refresh();

        $products = Cart::instance('cart')->products();

        $shippingMethod = $request->input('shipping_method', Arr::get($sessionData, 'shipping_method'));
        $shippingOption = $request->input('shipping_option', Arr::get($sessionData, 'shipping_option'));

        if (is_array($shippingMethod)) {
            $shippingMethod = $shippingMethod[0] ?? ShippingMethodEnum::DEFAULT;
        }

        if (is_array($shippingOption)) {
            $shippingOption = $shippingOption[0] ?? null;
        }

        $request->merge([
            'shipping_method' => $shippingMethod ?? ShippingMethodEnum::DEFAULT,
            'shipping_option' => $shippingOption,
        ]);

        if (is_plugin_active('marketplace')) {
            $sessionData = apply_filters(
                HANDLE_PROCESS_ORDER_DATA_ECOMMERCE,
                $products,
                $token,
                $sessionData,
                $request
            );

            OrderHelper::setOrderSessionData($token, $sessionData);

            return $sessionData;
        }

        if (! isset($sessionData['created_order'])) {
            $currentUserId = 0;
            if (auth('customer')->check()) {
                $currentUserId = auth('customer')->id();
            }

            /**
             * @var Order $order
             */
            $existingOrder = Order::query()->where(compact('token'))->first();

            if (! $finished) {
                $shippingAmountToUse = $existingOrder && $existingOrder->shipping_amount > 0
                    ? $existingOrder->shipping_amount
                    : Arr::get($sessionData, 'shipping_amount', 0);

                $shippingTaxAmountToUse = $existingOrder && $existingOrder->shipping_tax_amount > 0
                    ? $existingOrder->shipping_tax_amount
                    : Arr::get($sessionData, 'shipping_tax_amount', 0);

                $paymentFeeToUse = $existingOrder && $existingOrder->payment_fee > 0
                    ? $existingOrder->payment_fee
                    : Arr::get($sessionData, 'payment_fee', 0);

                $amountToUse = Cart::instance('cart')->rawTotal() + $shippingAmountToUse + $shippingTaxAmountToUse + $paymentFeeToUse;

                $shippingMethodToUse = $shippingMethod ?? ShippingMethodEnum::DEFAULT;
                $shippingOptionToUse = $shippingOption;
            } else {
                $shippingAmountToUse = $existingOrder ? $existingOrder->shipping_amount : 0;
                $shippingTaxAmountToUse = $existingOrder ? $existingOrder->shipping_tax_amount : 0;
                $paymentFeeToUse = $existingOrder ? $existingOrder->payment_fee : 0;
                $amountToUse = $existingOrder ? $existingOrder->amount : Cart::instance('cart')->rawTotal();
                $shippingMethodToUse = $existingOrder ? $existingOrder->shipping_method : ($shippingMethod ?? ShippingMethodEnum::DEFAULT);
                $shippingOptionToUse = $existingOrder ? $existingOrder->shipping_option : $shippingOption;
            }

            $request->merge([
                'amount' => $amountToUse,
                'user_id' => $currentUserId,
                'shipping_method' => $shippingMethodToUse,
                'shipping_option' => $shippingOptionToUse,
                'shipping_amount' => $shippingAmountToUse,
                'shipping_tax_amount' => $shippingTaxAmountToUse,
                'payment_fee' => $paymentFeeToUse,
                'tax_amount' => Cart::instance('cart')->rawTax(),
                'sub_total' => Cart::instance('cart')->rawSubTotal(),
                'coupon_code' => session('applied_coupon_code'),
                'discount_amount' => 0,
                'status' => OrderStatusEnum::PENDING,
                'is_finished' => false,
                'token' => $token,
            ]);

            $order = $this->createOrderFromData($request->input(), $existingOrder);

            $sessionData['created_order'] = true;
            $sessionData['created_order_id'] = $order->getKey();
        }

        if (! empty($address)) {
            $addressData['order_id'] = $sessionData['created_order_id'];
        } elseif ((array) $request->input('address', [])) {
            $addressData = array_merge(
                ['order_id' => $sessionData['created_order_id']],
                (array) $request->input('address', [])
            );
        } elseif (! empty($addressData['name'])) {
            // Session-fallback path (set above): keep $addressData but stamp order_id
            // so checkAndCreateOrderAddress can persist the row.
            $addressData['order_id'] = $sessionData['created_order_id'];
        }

        $sessionData['is_save_order_shipping_address'] = EcommerceHelper::isSaveOrderShippingAddress($products);

        $sessionData = OrderHelper::checkAndCreateOrderAddress($addressData, $sessionData);

        if (! isset($sessionData['created_order_product'])) {
            $weight = Cart::instance('cart')->weight();

            OrderProduct::query()->where(['order_id' => $sessionData['created_order_id']])->delete();

            foreach (Cart::instance('cart')->content() as $cartItem) {
                $product = Product::query()->find($cartItem->id);

                if (! $product) {
                    continue;
                }

                $data = [
                    'order_id' => $sessionData['created_order_id'],
                    'product_id' => $cartItem->id,
                    'product_name' => $cartItem->name,
                    'product_image' => $cartItem->options['image'],
                    'qty' => $cartItem->qty,
                    'weight' => Arr::get($cartItem->options, 'weight', 0),
                    'price' => $cartItem->price,
                    'tax_amount' => $cartItem->taxTotal,
                    'options' => $cartItem->options,
                    'product_type' => $product->product_type,
                ];

                if (isset($cartItem->options['options'])) {
                    $data['product_options'] = $cartItem->options['options'];
                }

                OrderProduct::query()->create($data);
            }

            $sessionData['created_order_product'] = Cart::instance('cart')->getLastUpdatedAt();
        }

        OrderHelper::setOrderSessionData($token, $sessionData);

        return $sessionData;
    }

    public function postSaveInformation(
        string $token,
        SaveCheckoutInformationRequest $request,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService
    ) {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        if ($token !== session('tracked_start_checkout')) {
            $order = Order::query()->where(['token' => $token, 'is_finished' => false])->first();

            if (! $order) {
                return $this
                    ->httpResponse()
                    ->setNextUrl(BaseHelper::getHomepageUrl());
            }
        }

        if ($paymentMethod = $request->input('payment_method')) {
            session()->put('selected_payment_method', $paymentMethod);
        }

        if (is_plugin_active('marketplace')) {
            $sessionData = array_merge(OrderHelper::getOrderSessionData($token), $request->input('address'));

            $sessionData = apply_filters(
                PROCESS_POST_SAVE_INFORMATION_CHECKOUT_ECOMMERCE,
                $sessionData,
                $request,
                $token
            );

            foreach ($sessionData['marketplace'] as $storeData) {
                if (! empty($storeData['created_order_id'])) {
                    $order = Order::query()
                        ->where('id', $storeData['created_order_id'])
                        ->first();

                    if ($order) {
                        $storeShippingAmount = Arr::get($storeData, 'shipping_amount', 0);
                        $shippingOption = Arr::get($storeData, 'shipping_option');
                        $storeShippingTaxAmount = EcommerceHelper::calculateShippingTax($storeShippingAmount);
                        $newAmount = $order->sub_total - $order->discount_amount + $order->tax_amount + $storeShippingAmount + $storeShippingTaxAmount + ($order->payment_fee ?? 0);

                        if ($order->shipping_amount != $storeShippingAmount || $order->amount != $newAmount || $order->shipping_option != $shippingOption) {
                            $order->update([
                                'shipping_amount' => $storeShippingAmount,
                                'shipping_tax_amount' => $storeShippingTaxAmount,
                                'shipping_option' => $shippingOption,
                                'amount' => $newAmount,
                            ]);
                        }
                    }
                }
            }
        } else {
            $sessionData = array_merge(OrderHelper::getOrderSessionData($token), $request->input('address'));
            OrderHelper::setOrderSessionData($token, $sessionData);
            if (session()->has('applied_coupon_code')) {
                $discount = $applyCouponService->getCouponData(session('applied_coupon_code'), $sessionData);
                if (! $discount) {
                    $removeCouponService->execute();
                }
            }

            if (! empty($sessionData['created_order_id'])) {
                $order = Order::query()
                    ->where('id', $sessionData['created_order_id'])
                    ->first();

                if ($order) {
                    $shippingAmount = Arr::get($sessionData, 'shipping_amount', 0);
                    $shippingOption = Arr::get($sessionData, 'shipping_option');
                    $orderShippingTaxAmount = EcommerceHelper::calculateShippingTax($shippingAmount);
                    $newAmount = $order->sub_total - $order->discount_amount + $order->tax_amount + $shippingAmount + $orderShippingTaxAmount + ($order->payment_fee ?? 0);

                    if ($order->shipping_amount != $shippingAmount || $order->amount != $newAmount || $order->shipping_option != $shippingOption) {
                        $order->update([
                            'shipping_amount' => $shippingAmount,
                            'shipping_tax_amount' => $orderShippingTaxAmount,
                            'shipping_option' => $shippingOption,
                            'amount' => $newAmount,
                        ]);
                    }
                }
            }
        }

        $sessionData = $this->processOrderData($token, $sessionData, $request);

        $this->storeCustomerAddressInCookie($sessionData);

        return $this
            ->httpResponse()
            ->setData($sessionData);
    }

    public function postCheckout(
        string $token,
        CheckoutRequest $request,
        HandleShippingFeeService $shippingFeeService,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService,
        HandleApplyPromotionsService $handleApplyPromotionsService
    ) {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.login') . '?redirect=' . urlencode(route('public.checkout.information', $token)));
        }

        if (Cart::instance('cart')->isEmpty()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(__('No products in cart'));
        }

        $products = Cart::instance('cart')->products();

        if (
            EcommerceHelper::isEnabledSupportDigitalProducts() &&
            ! EcommerceHelper::canCheckoutForDigitalProducts($products)
        ) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(route('customer.login') . '?redirect=' . urlencode(route('public.checkout.information', $token)))
                ->setMessage(__('Your shopping cart has digital product(s), so you need to sign in to continue!'));
        }

        $totalQuality = Cart::instance('cart')->rawTotalQuantity();

        if (($minimumQuantity = EcommerceHelper::getMinimumOrderQuantity()) > 0
            && $totalQuality < $minimumQuantity) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(
                    __('Minimum order quantity is :qty, you need to buy more :more to place an order!', [
                        'qty' => $totalQuality,
                        'more' => $minimumQuantity - $totalQuality,
                    ])
                );
        }

        if (
            ($maximumQuantity = EcommerceHelper::getMaximumOrderQuantity()) > 0
            && $totalQuality > $maximumQuantity
        ) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(
                    __('Maximum order quantity is :qty, please check your cart and retry again!', [
                        'qty' => $maximumQuantity,
                    ])
                );
        }

        if (EcommerceHelper::getMinimumOrderAmount() > Cart::instance('cart')->rawSubTotal()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(
                    __('Minimum order amount is :amount, you need to buy more :more to place an order!', [
                        'amount' => format_price(EcommerceHelper::getMinimumOrderAmount()),
                        'more' => format_price(
                            EcommerceHelper::getMinimumOrderAmount() - Cart::instance('cart')->rawSubTotal()
                        ),
                    ])
                );
        }

        $sessionData = OrderHelper::getOrderSessionData($token);

        $sessionData = $this->processOrderData($token, $sessionData, $request, true);

        $cartItems = [];
        foreach (Cart::instance('cart')->content() as $cartItem) {
            $cartItems[] = [
                'product_id' => $cartItem->id,
                'qty' => $cartItem->qty,
            ];
        }

        $stockValidation = OrderHelper::validateAndReserveStock($cartItems);

        if (! $stockValidation['success']) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($stockValidation['message']);
        }

        $reservedItems = $stockValidation['reserved_items'] ?? [];

        try {
            return $this->processCheckoutAfterStockReserved(
                $request,
                $token,
                $sessionData,
                $products,
                $handleApplyPromotionsService,
                $shippingFeeService,
                $applyCouponService,
                $removeCouponService,
            );
        } catch (Exception $e) {
            OrderHelper::restoreReservedStock($reservedItems);

            throw $e;
        }
    }

    protected function processCheckoutAfterStockReserved(
        CheckoutRequest $request,
        string $token,
        array $sessionData,
        Collection $products,
        HandleApplyPromotionsService $handleApplyPromotionsService,
        HandleShippingFeeService $shippingFeeService,
        HandleApplyCouponService $applyCouponService,
        HandleRemoveCouponService $removeCouponService,
    ) {
        $paymentMethod = $request->input('payment_method', session('selected_payment_method'));

        if ($paymentMethod) {
            session()->put('selected_payment_method', $paymentMethod);
        }

        do_action('ecommerce_post_checkout', $products, $request, $token, $sessionData);

        if (is_plugin_active('marketplace')) {
            return apply_filters(
                HANDLE_PROCESS_POST_CHECKOUT_ORDER_DATA_ECOMMERCE,
                $products,
                $request,
                $token,
                $sessionData,
                $this->httpResponse()
            );
        }

        $promotionDiscountAmount = $handleApplyPromotionsService->execute($token);
        $couponDiscountAmount = Arr::get($sessionData, 'coupon_discount_amount');
        $totalDiscountAmount = $promotionDiscountAmount + $couponDiscountAmount;
        $rawTotal = Cart::instance('cart')->rawTotal();
        $subTotal = Cart::instance('cart')->rawSubTotal();
        $taxAmount = Cart::instance('cart')->rawTax();
        $orderAmount = max($rawTotal - $totalDiscountAmount, 0);

        $isAvailableShipping = EcommerceHelper::isAvailableShipping($products);

        $existingOrder = Order::query()->where(compact('token'))->first();

        $shippingMethodInput = $request->input('shipping_method', Arr::get($sessionData, 'shipping_method', $existingOrder?->shipping_method ?? ShippingMethodEnum::DEFAULT));
        $shippingOption = $request->input('shipping_option', Arr::get($sessionData, 'shipping_option', $existingOrder?->shipping_option));

        if (is_array($shippingMethodInput)) {
            $shippingMethodInput = $shippingMethodInput[0] ?? ShippingMethodEnum::DEFAULT;
        }

        if (is_array($shippingOption)) {
            $shippingOption = $shippingOption[0] ?? null;
        }

        $request->merge([
            'shipping_method' => $shippingMethodInput,
            'shipping_option' => $shippingOption,
        ]);

        $shippingAmount = 0;
        $shippingData = [];
        if ($isAvailableShipping) {
            $origin = EcommerceHelper::getOriginAddress();
            $shippingData = EcommerceHelper::getShippingData(
                $products,
                $sessionData,
                $origin,
                $orderAmount,
                $paymentMethod
            );

            $shippingMethodData = $shippingFeeService->execute(
                $shippingData,
                $shippingMethodInput,
                $shippingOption
            );

            $shippingMethod = Arr::first($shippingMethodData);
            if (! $shippingMethod) {
                throw ValidationException::withMessages([
                    'shipping_method' => trans(
                        'validation.exists',
                        ['attribute' => trans('plugins/ecommerce::shipping.shipping_method')]
                    ),
                ]);
            }

            $shippingAmount = Arr::get($shippingMethod, 'price', 0);

            if (get_shipping_setting('free_ship', $shippingMethodInput)) {
                $shippingAmount = 0;
            }
        }

        if (session()->has('applied_coupon_code')) {
            $discount = $applyCouponService->getCouponData(session('applied_coupon_code'), $sessionData);
            if (empty($discount)) {
                $removeCouponService->execute();
            } else {
                $shippingAmount = Arr::get($sessionData, 'is_free_shipping') ? 0 : $shippingAmount;
            }
        }

        $currentUserId = 0;
        if (auth('customer')->check()) {
            $currentUserId = auth('customer')->id();
        }

        $orderAmount += (float) $shippingAmount;

        $shippingTaxAmount = EcommerceHelper::calculateShippingTax($shippingAmount);
        $orderAmount += $shippingTaxAmount;

        $paymentFee = 0;
        if ($paymentMethod && is_plugin_active('payment')) {
            $paymentFee = PaymentFeeHelper::calculateFee($paymentMethod, $orderAmount);
            $orderAmount += $paymentFee;
        }

        $request->merge(['payment_fee' => $paymentFee]);

        $request->merge([
            'amount' => $orderAmount ?: 0,
            'currency' => $request->input('currency', get_application_currency()->title),
            'user_id' => $currentUserId,
            'shipping_method' => $isAvailableShipping ? $shippingMethodInput : '',
            'shipping_option' => $isAvailableShipping ? $shippingOption : null,
            'shipping_amount' => (float) $shippingAmount,
            'shipping_tax_amount' => $shippingTaxAmount,
            'payment_fee' => (float) $paymentFee,
            'tax_amount' => $taxAmount,
            'sub_total' => $subTotal,
            'coupon_code' => session('applied_coupon_code'),
            'discount_amount' => $totalDiscountAmount,
            'status' => OrderStatusEnum::PENDING,
            'token' => $token,
        ]);

        /**
         * @var Order $order
         */
        $order = $this->createOrderFromData($request->input(), $existingOrder);

        OrderHistory::query()->create([
            'action' => OrderHistoryActionEnum::CREATE_ORDER_FROM_PAYMENT_PAGE,
            'description' => __('Order was created from checkout page'),
            'order_id' => $order->getKey(),
        ]);

        if ($isAvailableShipping && ! Shipment::query()->where(['order_id' => $order->getKey()])->exists()) {
            Shipment::query()->create([
                'order_id' => $order->getKey(),
                'user_id' => 0,
                'weight' => $shippingData ? Arr::get($shippingData, 'weight') : 0,
                'cod_amount' => (is_plugin_active(
                    'payment'
                ) && $order->payment->id && $order->payment->status != PaymentStatusEnum::COMPLETED) ? $order->amount : 0,
                'cod_status' => ShippingCodStatusEnum::PENDING,
                'type' => $order->shipping_method,
                'status' => ShippingStatusEnum::PENDING,
                'price' => $order->shipping_amount,
                'rate_id' => $shippingData ? Arr::get($shippingMethod, 'id', '') : '',
                'shipment_id' => $shippingData ? Arr::get($shippingMethod, 'shipment_id', '') : '',
                'shipping_company_name' => $shippingData ? Arr::get($shippingMethod, 'company_name', '') : '',
            ]);
        }

        if (
            EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage() &&
            $request->boolean('with_tax_information')
        ) {
            $order->taxInformation()->create($request->input('tax_information'));
        }

        OrderProduct::query()->where(['order_id' => $order->getKey()])->delete();

        foreach (Cart::instance('cart')->content() as $cartItem) {
            $product = Product::query()->find($cartItem->id);

            if (! $product) {
                continue;
            }

            $data = [
                'order_id' => $order->getKey(),
                'product_id' => $cartItem->id,
                'product_name' => $cartItem->name,
                'product_image' => $cartItem->options['image'],
                'qty' => $cartItem->qty,
                'weight' => Arr::get($cartItem->options, 'weight', 0),
                'price' => $cartItem->price,
                'tax_amount' => $cartItem->taxTotal,
                'options' => $cartItem->options,
                'product_type' => $product->product_type,
            ];

            if (isset($cartItem->options['options'])) {
                $data['product_options'] = $cartItem->options['options'];
            }

            /**
             * @var OrderProduct $orderProduct
             */
            $orderProduct = OrderProduct::query()->create($data);

            OrderProductCreatedEvent::dispatch($orderProduct);
            do_action('ecommerce_after_each_order_product_created', $orderProduct);
        }

        $request->merge([
            'order_id' => $order->getKey(),
        ]);

        do_action('ecommerce_before_processing_payment', $products, $request, $token, $sessionData);

        $this->storeCustomerAddressInCookie($sessionData);

        if (! is_plugin_active('payment') || ! $orderAmount) {
            OrderHelper::processOrder($order->getKey());

            return redirect()->to(route('public.checkout.success', OrderHelper::getOrderSessionToken()));
        }

        $paymentData = [
            'error' => false,
            'message' => false,
            'amount' => (float) format_price($order->amount, null, true),
            'currency' => get_application_currency()->title,
            'type' => $request->input('payment_method'),
            'charge_id' => null,
        ];

        $paymentData = apply_filters(FILTER_ECOMMERCE_PROCESS_PAYMENT, $paymentData, $request);

        if ($checkoutUrl = Arr::get($paymentData, 'checkoutUrl')) {
            return $this
                ->httpResponse()
                ->setError($paymentData['error'])
                ->setNextUrl($checkoutUrl)
                ->setData(['checkoutUrl' => $checkoutUrl])
                ->withInput()
                ->setMessage($paymentData['message']);
        }

        if ($paymentData['error'] || ! $paymentData['charge_id']) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($token))
                ->withInput()
                ->setMessage($paymentData['message'] ?: __('Checkout error!'));
        }

        return $this
            ->httpResponse()
            ->setNextUrl(PaymentHelper::getRedirectURL($token))
            ->setMessage(trans('plugins/ecommerce::order.checkout_successfully'));
    }

    public function getCheckoutSuccess(string $token)
    {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        /**
         * @var Order $order
         */
        $order = Order::query()
            ->where('token', $token)
            ->with(['address', 'products', 'taxInformation'])
            ->latest('id')
            ->firstOrFail();

        if (session('tracked_start_checkout')) {
            app(GoogleTagManager::class)->purchase($order);
            app(FacebookPixel::class)->purchase($order);
        }

        if (is_plugin_active('marketplace')) {
            return apply_filters(PROCESS_GET_CHECKOUT_SUCCESS_IN_ORDER, $token, $this->httpResponse());
        }

        $products = $order->getOrderProducts();

        OrderHelper::clearSessions($token);

        $thankYouView = Theme::getThemeNamespace('views.ecommerce.orders.thank-you');

        if (view()->exists($thankYouView)) {
            return view($thankYouView, compact('order', 'products'));
        }

        return view('plugins/ecommerce::orders.thank-you', compact('order', 'products'));
    }

    public function postApplyCoupon(ApplyCouponRequest $request, HandleApplyCouponService $handleApplyCouponService)
    {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        $result = [
            'error' => false,
            'message' => '',
        ];

        if (is_plugin_active('marketplace')) {
            $result = apply_filters(HANDLE_POST_APPLY_COUPON_CODE_ECOMMERCE, $result, $request);
        } else {
            $result = $handleApplyCouponService->execute($request->input('coupon_code'));
        }

        if ($result['error']) {
            return $this
                ->httpResponse()
                ->setError()
                ->withInput()
                ->setMessage($result['message']);
        }

        $couponCode = $request->input('coupon_code');

        return $this
            ->httpResponse()
            ->setMessage(__('Applied coupon ":code" successfully!', ['code' => $couponCode]));
    }

    public function postRemoveCoupon(Request $request, HandleRemoveCouponService $removeCouponService)
    {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        if (is_plugin_active('marketplace')) {
            $products = Cart::instance('cart')->products();
            $result = apply_filters(HANDLE_POST_REMOVE_COUPON_CODE_ECOMMERCE, $products, $request);
        } else {
            $result = $removeCouponService->execute();
        }

        if ($result['error']) {
            if ($request->ajax()) {
                return $result;
            }

            return $this
                ->httpResponse()
                ->setError()
                ->setData($result)
                ->setMessage($result['message']);
        }

        return $this
            ->httpResponse()
            ->setMessage(__('Removed coupon :code successfully!', ['code' => session('applied_coupon_code')]));
    }

    public function getCheckoutRecover(string $token, Request $request)
    {
        abort_unless(EcommerceHelper::isCartEnabled(), 404);

        if (! EcommerceHelper::isEnabledGuestCheckout() && ! auth('customer')->check()) {
            return $this
                ->httpResponse()
                ->setNextUrl(route('customer.login') . '?redirect=' . urlencode(route('public.checkout.information', $token)));
        }

        if (is_plugin_active('marketplace')) {
            return apply_filters(PROCESS_GET_CHECKOUT_RECOVER_ECOMMERCE, $token, $request);
        }

        $order = Order::query()
            ->where([
                'token' => $token,
                'is_finished' => false,
            ])
            ->with(['products', 'address'])
            ->firstOrFail();

        if (session()->has('tracked_start_checkout') && session('tracked_start_checkout') == $token) {
            $sessionCheckoutData = OrderHelper::getOrderSessionData($token);
        } else {
            session(['tracked_start_checkout' => $token]);
            $sessionCheckoutData = [
                'name' => $order->address->name,
                'email' => $order->address->email,
                'phone' => $order->address->phone,
                'address' => $order->address->address,
                'country' => $order->address->country,
                'state' => $order->address->state,
                'city' => $order->address->city,
                'zip_code' => $order->address->zip_code,
                'shipping_method' => $order->shipping_method,
                'shipping_option' => $order->shipping_option,
                'shipping_amount' => $order->shipping_amount,
            ];
        }

        Cart::instance('cart')->destroy();
        foreach ($order->products as $orderProduct) {
            $request->merge(['qty' => $orderProduct->qty]);

            /**
             * @var Product $product
             */
            $product = Product::query()->find($orderProduct->product_id);

            if ($product) {
                OrderHelper::handleAddCart($product, $request);
            }
        }

        OrderHelper::setOrderSessionData($token, $sessionCheckoutData);

        return $this
            ->httpResponse()
            ->setNextUrl(route('public.checkout.information', $token))
            ->setMessage(__('You have recovered from previous orders!'));
    }

    protected function createOrderFromData(array $data, ?Order $order): Order|null|false
    {
        return OrderHelper::createOrUpdateIncompleteOrder($data, $order);
    }

    protected function prefillAddressFromCookie(array $sessionData): array
    {
        $cookieData = Cookie::get('customer_checkout_address');

        if (! $cookieData) {
            return $sessionData;
        }

        try {
            $addressData = json_decode($cookieData, true);

            if (! is_array($addressData)) {
                return $sessionData;
            }

            $addressFields = [
                'name',
                'email',
                'phone',
                'country',
                'state',
                'city',
                'address',
                'zip_code',
            ];

            foreach ($addressFields as $field) {
                if (empty($sessionData[$field]) && ! empty($addressData[$field])) {
                    $sessionData[$field] = $addressData[$field];
                }
            }

            if (EcommerceHelper::isBillingAddressEnabled()) {
                $billingSource = ! empty($addressData['billing_address']) ? $addressData['billing_address'] : $addressData;

                foreach ($addressFields as $field) {
                    $billingField = "billing_address.$field";
                    if (empty(Arr::get($sessionData, $billingField)) && ! empty($billingSource[$field])) {
                        Arr::set($sessionData, $billingField, $billingSource[$field]);
                    }
                }
            }
        } catch (Throwable $exception) {
            BaseHelper::logError($exception);
        }

        return $sessionData;
    }

    protected function storeCustomerAddressInCookie(array $sessionData): void
    {
        if (auth('customer')->check()) {
            return;
        }

        $addressFields = [
            'name',
            'email',
            'phone',
            'country',
            'state',
            'city',
            'address',
            'zip_code',
        ];

        $addressData = [];
        foreach ($addressFields as $field) {
            if ($value = Arr::get($sessionData, $field)) {
                $addressData[$field] = $value;
            }
        }

        if (EcommerceHelper::isBillingAddressEnabled()) {
            $billingAddressData = [];
            foreach ($addressFields as $field) {
                if ($value = Arr::get($sessionData, "billing_address.$field")) {
                    $billingAddressData[$field] = $value;
                }
            }

            if (! empty($billingAddressData)) {
                $addressData['billing_address'] = $billingAddressData;
            }
        }

        if (! empty($addressData)) {
            Cookie::queue('customer_checkout_address', json_encode($addressData), 525600);
        }
    }
}
