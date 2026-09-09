<?php

namespace Botble\Paystack\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Paystack\Services\Paystack;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PaystackController extends BaseController
{
    protected function logWebhook(array $data): void
    {
        PaymentHelper::log(PAYSTACK_PAYMENT_METHOD_NAME, [], $data);
    }

    public function getPaymentStatus(Request $request, BaseHttpResponse $response, Paystack $paystack)
    {
        do_action('payment_before_making_api_request', PAYSTACK_PAYMENT_METHOD_NAME, []);

        $result = $paystack->getPaymentData();

        do_action('payment_after_api_response', PAYSTACK_PAYMENT_METHOD_NAME, [], $result);

        if (! $result['status']) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage($result['message']);
        }

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $result['data']['amount'] / 100,
            'currency' => $result['data']['currency'],
            'charge_id' => $result['data']['reference'],
            'payment_channel' => PAYSTACK_PAYMENT_METHOD_NAME,
            'status' => PaymentStatusEnum::COMPLETED,
            'customer_id' => Arr::get($result['data']['metadata'], 'customer_id'),
            'customer_type' => Arr::get($result['data']['metadata'], 'customer_type'),
            'payment_type' => 'direct',
            'order_id' => (array) $result['data']['metadata']['order_id'],
        ], $request);

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL())
            ->setMessage(trans('plugins/payment::payment.checkout_success'));
    }

    public function webhook(Request $request)
    {
        $secretKey = get_payment_setting('webhook_secret', PAYSTACK_PAYMENT_METHOD_NAME)
            ?: get_payment_setting('secret', PAYSTACK_PAYMENT_METHOD_NAME);
        $signature = $request->header('X-Paystack-Signature');
        $content = $request->getContent();

        if (! $secretKey) {
            $this->logWebhook(['webhook_error' => 'Secret key not configured']);

            return response('Secret key not configured', 400);
        }

        if (! $signature) {
            $this->logWebhook(['webhook_error' => 'Missing Paystack signature header']);

            return response('Missing signature', 400);
        }

        if (! $content) {
            $this->logWebhook(['webhook_error' => 'Empty request body']);

            return response('Empty request body', 400);
        }

        $computedSignature = hash_hmac('sha512', $content, $secretKey);

        if (! hash_equals($computedSignature, $signature)) {
            $this->logWebhook(['webhook_error' => 'Invalid signature']);

            return response('Invalid signature', 403);
        }

        try {
            $payload = json_decode($content, true);
            $event = Arr::get($payload, 'event');

            $this->logWebhook([
                'webhook_event' => $event,
                'payload' => $payload,
            ]);

            match ($event) {
                'charge.success' => $this->handleChargeSuccess($payload),
                'refund.processed' => $this->handleRefundProcessed($payload),
                default => $this->logWebhook(['webhook_info' => 'Unhandled event type: ' . $event]),
            };

            return response('OK', 200);
        } catch (Exception $e) {
            $this->logWebhook([
                'webhook_error' => 'Unexpected error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            BaseHelper::logError($e);

            return response('Internal server error', 500);
        }
    }

    protected function handleChargeSuccess(array $payload): void
    {
        $data = Arr::get($payload, 'data', []);
        $reference = Arr::get($data, 'reference');

        if (! $reference) {
            $this->logWebhook(['webhook_error' => 'No reference in charge.success payload']);

            return;
        }

        $payment = Payment::query()
            ->where('charge_id', $reference)
            ->where('payment_channel', PAYSTACK_PAYMENT_METHOD_NAME)
            ->first();

        if ($payment) {
            if ($payment->status === PaymentStatusEnum::COMPLETED) {
                $this->logWebhook([
                    'webhook_info' => 'Payment already completed',
                    'charge_id' => $reference,
                ]);

                return;
            }

            $payment->status = PaymentStatusEnum::COMPLETED;
            $payment->save();

            $this->logWebhook([
                'webhook_success' => 'Payment status updated to completed',
                'charge_id' => $reference,
            ]);

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'charge_id' => $reference,
                'order_id' => $payment->order_id,
            ]);

            return;
        }

        $metadata = Arr::get($data, 'metadata', []);
        $orderId = Arr::get($metadata, 'order_id');

        if (! $orderId) {
            $this->logWebhook([
                'webhook_error' => 'No order_id in metadata',
                'charge_id' => $reference,
            ]);

            return;
        }

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => Arr::get($data, 'amount', 0) / 100,
            'currency' => Arr::get($data, 'currency'),
            'charge_id' => $reference,
            'payment_channel' => PAYSTACK_PAYMENT_METHOD_NAME,
            'status' => PaymentStatusEnum::COMPLETED,
            'customer_id' => Arr::get($metadata, 'customer_id'),
            'customer_type' => Arr::get($metadata, 'customer_type'),
            'payment_type' => 'direct',
            'order_id' => (array) $orderId,
        ]);

        $this->logWebhook([
            'webhook_success' => 'Payment processed via charge.success',
            'charge_id' => $reference,
            'order_id' => $orderId,
        ]);
    }

    protected function handleRefundProcessed(array $payload): void
    {
        $data = Arr::get($payload, 'data', []);
        $transactionReference = Arr::get($data, 'transaction.reference', Arr::get($data, 'transaction_reference'));

        if (! $transactionReference) {
            $this->logWebhook(['webhook_error' => 'No transaction reference in refund.processed payload']);

            return;
        }

        $payment = Payment::query()
            ->where('charge_id', $transactionReference)
            ->where('payment_channel', PAYSTACK_PAYMENT_METHOD_NAME)
            ->first();

        if (! $payment) {
            $this->logWebhook([
                'webhook_info' => 'Payment not found for refund',
                'charge_id' => $transactionReference,
            ]);

            return;
        }

        if ($payment->status == PaymentStatusEnum::REFUNDED) {
            $this->logWebhook([
                'webhook_info' => 'Payment already refunded',
                'charge_id' => $transactionReference,
            ]);

            return;
        }

        $payment->status = PaymentStatusEnum::REFUNDED;
        $payment->save();

        $this->logWebhook([
            'webhook_refund' => true,
            'charge_id' => $transactionReference,
            'new_status' => PaymentStatusEnum::REFUNDED,
        ]);

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'charge_id' => $transactionReference,
            'order_id' => $payment->order_id,
            'status' => PaymentStatusEnum::REFUNDED,
            'is_refund_update' => true,
        ]);
    }
}
