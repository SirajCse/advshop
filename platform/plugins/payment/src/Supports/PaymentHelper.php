<?php

namespace Botble\Payment\Supports;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Helper;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Models\PaymentLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class PaymentHelper
{
    public static function getRedirectURL(?string $checkoutToken = null): string
    {
        return apply_filters(PAYMENT_FILTER_REDIRECT_URL, $checkoutToken, BaseHelper::getHomepageUrl());
    }

    public static function getCancelURL(?string $checkoutToken = null): string
    {
        return apply_filters(PAYMENT_FILTER_CANCEL_URL, $checkoutToken, BaseHelper::getHomepageUrl());
    }

    public static function storeLocalPayment(array $args = [])
    {
        $data = [
            'user_id' => Auth::id() ?: 0,
            ...$args,
        ];

        $orderIds = (array) $data['order_id'];

        $payment = Payment::query()
            ->where('charge_id', $data['charge_id'])
            ->whereIn('order_id', $orderIds)
            ->first();

        if ($payment) {
            $dirty = false;

            if ($payment->status != $data['status']) {
                $payment->status = $data['status'];
                $dirty = true;
            }

            // Reconcile amount/currency on existing rows. The webhook may create a row
            // before the callback runs `do_action(PAYMENT_ACTION_PAYMENT_PROCESSED)`,
            // and the gateway-reported amount can differ from the order total when the
            // cart was modified between Razorpay-order creation and capture. Without
            // this, payments.amount stays stale and the admin "Paid amount" diverges
            // from ec_orders.amount even though Razorpay charged the right amount.
            //
            // Note: refunded_amount is a DECIMAL column with no model cast, so Laravel
            // returns it as a string ("0.00" for unrefunded). Loose-compare to 0 — a
            // truthiness check (`! $payment->refunded_amount`) would always be false
            // for "0.00" and skip the reconciliation entirely.
            if ((float) $payment->refunded_amount <= 0) {
                $newAmount = Arr::get($data, 'amount');
                if ($newAmount !== null && (float) $payment->amount !== (float) $newAmount) {
                    $payment->amount = $newAmount;
                    $dirty = true;
                }

                $newCurrency = Arr::get($data, 'currency');
                if ($newCurrency && $payment->currency !== $newCurrency) {
                    $payment->currency = $newCurrency;
                    $dirty = true;
                }
            }

            if ($dirty) {
                $payment->save();
            }

            return false;
        }

        $paymentChannel = Arr::get($data, 'payment_channel', PaymentMethodEnum::COD);

        // Prefer the fee passed in (authoritative — set from ec_orders.payment_fee at checkout).
        // Recalculating from $data['amount'] would double-apply percentage fees because
        // $data['amount'] = $order->amount already includes the fee, producing a compounded
        // value that diverges from the Invoice PDF.
        if (Arr::has($data, 'payment_fee')) {
            $paymentFee = (float) $data['payment_fee'];
        } elseif ($paymentChannel) {
            $paymentFee = PaymentFeeHelper::calculateFee($paymentChannel, $data['amount']);
        } else {
            $paymentFee = 0;
        }

        return Payment::query()
            ->create([
                'amount' => $data['amount'],
                'payment_fee' => $paymentFee,
                'currency' => $data['currency'],
                'charge_id' => $data['charge_id'],
                'order_id' => Arr::first($orderIds),
                'customer_id' => Arr::get($data, 'customer_id'),
                'customer_type' => Arr::get($data, 'customer_type'),
                'payment_channel' => $paymentChannel,
                'status' => Arr::get($data, 'status', PaymentStatusEnum::PENDING),
            ]);
    }

    public static function formatLog(
        array $input,
        string|int $line = '',
        string $function = '',
        string $class = ''
    ): array {
        return [
            ...$input,
            'user_id' => Auth::id() ?: 0,
            'ip' => Request::ip(),
            'line' => $line,
            'function' => $function,
            'class' => $class,
            'userAgent' => Request::userAgent(),
        ];
    }

    public static function defaultPaymentMethod(): string
    {
        return setting('default_payment_method', PaymentMethodEnum::COD);
    }

    public static function getAvailableCountries(string $paymentMethod): array
    {
        $json = get_payment_setting('available_countries', $paymentMethod);

        $countries = Helper::countries();

        if ($json === null || $json === '[]') {
            return $countries;
        }

        $selectedCountries = json_decode($json, true);

        if (empty($selectedCountries)) {
            return $countries;
        }

        return array_intersect_key($countries, array_flip($selectedCountries));
    }

    public static function getPaymentMethodRules(string $paymentMethod): array
    {
        return [
            get_payment_setting_key('name', $paymentMethod) => ['required', 'string', 'max:255'],
            get_payment_setting_key('description', $paymentMethod) => ['required', 'string'],
            get_payment_setting_key('logo', $paymentMethod) => ['nullable', 'string'],
            get_payment_setting_key('available_countries', $paymentMethod) => ['nullable', 'array'],
            sprintf('%s.*', get_payment_setting_key('available_countries', $paymentMethod)) => ['nullable', 'string'],
        ];
    }

    public static function log(string $paymentMethod, array $request = [], array $response = []): void
    {
        PaymentLog::query()->create([
            'payment_method' => $paymentMethod,
            'request' => $request,
            'response' => $response,
            'ip_address' => request()->ip(),
        ]);
    }
}
