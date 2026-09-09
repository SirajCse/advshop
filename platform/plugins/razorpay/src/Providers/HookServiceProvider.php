<?php

namespace Botble\Razorpay\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Enums\OrderAddressTypeEnum;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Media\Facades\RvMedia;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Razorpay\Forms\RazorpayPaymentMethodForm;
use Botble\Razorpay\Services\Gateways\RazorpayPaymentService;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerRazorpayMethod'], 11, 2);
        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithRazorpay'], 11, 2);

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 93);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['RAZORPAY'] = RAZORPAY_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 20, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == RAZORPAY_PAYMENT_METHOD_NAME) {
                $value = 'Razorpay';
            }

            return $value;
        }, 20, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == RAZORPAY_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == RAZORPAY_PAYMENT_METHOD_NAME) {
                $data = RazorpayPaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == RAZORPAY_PAYMENT_METHOD_NAME) {
                $paymentService = new RazorpayPaymentService();
                $paymentDetail = $paymentService->getPaymentDetails($payment->charge_id);

                if ($paymentDetail) {
                    $data .= view('plugins/razorpay::detail', ['payment' => $paymentDetail, 'paymentModel' => $payment])->render();
                }
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_GET_REFUND_DETAIL, function ($data, $payment, $refundId) {
            if ($payment->payment_channel == RAZORPAY_PAYMENT_METHOD_NAME) {
                $refundDetail = (new RazorpayPaymentService())->getRefundDetails($refundId);
                if (! Arr::get($refundDetail, 'error')) {
                    $refunds = Arr::get($payment->metadata, 'refunds', []);
                    $refund = collect($refunds)->firstWhere('id', $refundId);
                    $refund = array_merge((array) $refund, Arr::get($refundDetail, 'data', []));

                    return array_merge($refundDetail, [
                        'view' => view('plugins/razorpay::refund-detail', ['refund' => $refund, 'paymentModel' => $payment])->render(),
                    ]);
                }

                return $refundDetail;
            }

            return $data;
        }, 20, 3);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . RazorpayPaymentMethodForm::create()->renderForm();
    }

    public function registerRazorpayMethod(?string $html, array $data): string
    {
        $apiKey = get_payment_setting('key', RAZORPAY_PAYMENT_METHOD_NAME);
        $apiSecret = get_payment_setting('secret', RAZORPAY_PAYMENT_METHOD_NAME);

        if (! $apiKey || ! $apiSecret) {
            return $html;
        }

        $data['errorMessage'] = null;
        $data['orderId'] = null;

        $paymentService = new RazorpayPaymentService();
        $minimumAmount = $paymentService->getMinimumOrderAmount();
        $data['minimumAmount'] = $minimumAmount;
        $data['orderAmount'] = $data['amount'] ?? 0;

        if (get_payment_setting(
            'payment_type',
            RAZORPAY_PAYMENT_METHOD_NAME,
            'hosted_checkout',
        ) == 'website_embedded' && (float) ($data['amount'] ?? 0) >= $minimumAmount) {
            try {
                $api = new Api($apiKey, $apiSecret);

                $receiptId = $data['checkout_token'] ?? Str::random(20);

                $amount = $data['amount'] * 100;

                $requestData = [
                    'receipt' => $receiptId,
                    'amount' => (int) round($amount),
                    'currency' => $data['currency'],
                ];

                do_action('payment_before_making_api_request', RAZORPAY_PAYMENT_METHOD_NAME, $requestData);

                // @phpstan-ignore-next-line
                $order = $api->order->create($requestData);

                do_action('payment_after_api_response', RAZORPAY_PAYMENT_METHOD_NAME, $requestData, $order->toArray());

                $data['orderId'] = $order['id'];
            } catch (Exception $exception) {
                $data['errorMessage'] = $exception->getMessage();
            }
        }

        PaymentMethods::method(RAZORPAY_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/razorpay::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithRazorpay(array $data, Request $request): array
    {
        if ($data['type'] !== RAZORPAY_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        $paymentService = new RazorpayPaymentService();
        $minimumAmount = $paymentService->getMinimumOrderAmount();

        if ((float) $paymentData['amount'] < $minimumAmount) {
            $data['error'] = true;
            $data['message'] = trans('plugins/razorpay::razorpay.minimum_amount_error', [
                'amount' => format_price($minimumAmount),
            ]);

            return $data;
        }

        $data['charge_id'] = $request->input('razorpay_payment_id');

        if (! $data['charge_id']) {
            $data['error'] = true;
            $data['message'] = trans('plugins/razorpay::razorpay.payment_failed');
        }

        $amount = (int) round($paymentData['amount'] * 100);

        $status = PaymentStatusEnum::PENDING;

        $apiKey = get_payment_setting('key', RAZORPAY_PAYMENT_METHOD_NAME);
        $apiSecret = get_payment_setting('secret', RAZORPAY_PAYMENT_METHOD_NAME);

        $api = new Api($apiKey, $apiSecret);

        if (get_payment_setting(
            'payment_type',
            RAZORPAY_PAYMENT_METHOD_NAME,
            'hosted_checkout',
        ) == 'hosted_checkout') {
            $receiptId = $data['checkout_token'] ?? Str::random(20);

            // Snapshot the buyer's shipping address into the Razorpay order notes so a
            // guest who pays but never returns (dead checkout session) can still have a
            // complete order rebuilt from the webhook/callback. Razorpay persists notes
            // server-side and returns them in both the order and payment payloads.
            $notes = array_merge([
                'order_id' => is_array($paymentData['order_id']) ? implode(',', $paymentData['order_id']) : $paymentData['order_id'],
                'order_token' => $paymentData['checkout_token'],
                'customer_name' => $paymentData['address']['name'],
                'customer_email' => $paymentData['address']['email'],
                'customer_phone' => $paymentData['address']['phone'],
            ], $this->getShippingAddressNotes($paymentData['order_id']));

            $requestData = [
                'receipt' => $receiptId,
                'amount' => $amount,
                'currency' => $data['currency'],
                'notes' => $notes,
            ];

            do_action('payment_before_making_api_request', RAZORPAY_PAYMENT_METHOD_NAME, $requestData);

            // @phpstan-ignore-next-line
            $order = $api->order->create($requestData);

            do_action('payment_after_api_response', RAZORPAY_PAYMENT_METHOD_NAME, $requestData, $order->toArray());

            $paymentService = new RazorpayPaymentService();

            $checkoutData = [
                'key_id' => $apiKey,
                'amount' => $amount,
                'currency' => $data['currency'],
                'order_id' => $order['id'],
                'name' => Theme::getSiteTitle(),
                'description' => $paymentData['description'],
                'image' => Theme::getLogo() ? RvMedia::getImageUrl(Theme::getLogo()) : null,
                'callback_url' => route('payments.razorpay.callback', [
                    'token' => $paymentData['checkout_token'],
                    'customer_id' => $paymentData['customer_id'],
                    'customer_type' => $paymentData['customer_type'],
                    'order_id' => $paymentData['order_id'],
                ]),
                'cancel_url' => $paymentData['return_url'],
                'prefill[name]' => $paymentData['address']['name'],
                'prefill[email]' => $paymentData['address']['email'],
                'prefill[contact]' => $paymentData['address']['phone'],
            ];

            // Mirror the same notes onto the checkout payload so the shipping snapshot is
            // also attached to the payment entity (read by the callback recovery path).
            foreach ($notes as $noteKey => $noteValue) {
                $checkoutData["notes[$noteKey]"] = $noteValue;
            }

            $paymentService->redirectToCheckoutPage($checkoutData);
        } else {
            try {
                $orderId = $request->input('razorpay_order_id');

                $signature = $request->input('razorpay_signature');

                if ($orderId && $signature) {
                    // @phpstan-ignore-next-line
                    $api->utility->verifyPaymentSignature([
                        'razorpay_signature' => $signature,
                        'razorpay_payment_id' => $data['charge_id'],
                        'razorpay_order_id' => $orderId,
                    ]);

                    do_action('payment_before_making_api_request', RAZORPAY_PAYMENT_METHOD_NAME, ['order_id' => $orderId]);

                    // @phpstan-ignore-next-line
                    $order = $api->order->fetch($orderId);

                    $order = $order->toArray();

                    do_action('payment_after_api_response', RAZORPAY_PAYMENT_METHOD_NAME, ['order_id' => $orderId], $order);

                    $amount = $order['amount_paid'] / 100;

                    $status = $order['status'] === 'paid' ? PaymentStatusEnum::COMPLETED : $status;
                }
            } catch (SignatureVerificationError $exception) {
                BaseHelper::logError($exception);

                $data['message'] = $exception->getMessage();
                $data['error'] = true;
            }

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'amount' => $amount,
                'currency' => $paymentData['currency'],
                'charge_id' => $data['charge_id'],
                'payment_channel' => RAZORPAY_PAYMENT_METHOD_NAME,
                'status' => $status,
                'order_id' => $paymentData['order_id'],
                'customer_id' => $paymentData['customer_id'],
                'customer_type' => $paymentData['customer_type'],
            ]);
        }

        return $data;
    }

    /**
     * Build the shipping-address slice of the Razorpay order notes.
     *
     * Carries the RAW stored values (country code, state/city as stored), NOT the
     * display names, so the webhook/callback can rebuild a byte-identical address
     * row whose accessors resolve exactly like the original. Each value is capped
     * to Razorpay's 256-char-per-note limit; blanks are dropped.
     *
     * @return array<string, string>
     */
    protected function getShippingAddressNotes(array|string|null $orderId): array
    {
        if (empty($orderId) || ! class_exists(OrderAddress::class)) {
            return [];
        }

        $firstOrderId = is_array($orderId) ? Arr::first($orderId) : $orderId;

        $address = OrderAddress::query()
            ->where('order_id', $firstOrderId)
            ->where('type', OrderAddressTypeEnum::SHIPPING)
            ->first();

        if (! $address) {
            return [];
        }

        return array_filter([
            'shipping_address' => Str::limit((string) $address->address, 256, ''),
            'shipping_city' => Str::limit((string) $address->city, 256, ''),
            'shipping_state' => Str::limit((string) $address->state, 256, ''),
            'shipping_country' => Str::limit((string) $address->country, 256, ''),
            'shipping_zip' => Str::limit((string) $address->zip_code, 256, ''),
        ], fn ($value) => $value !== '');
    }
}
