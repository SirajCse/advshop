<?php

namespace Botble\Stripe\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Stripe\Http\Requests\StripePaymentCallbackRequest;
use Botble\Stripe\Services\Gateways\StripePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripeController extends BaseController
{
    protected function logWebhook(array $data): void
    {
        PaymentHelper::log(STRIPE_PAYMENT_METHOD_NAME, [], $data);
    }

    public function webhook(Request $request, StripePaymentService $stripePaymentService)
    {
        $webhookSecret = get_payment_setting('webhook_secret', 'stripe');
        $signature = $request->server('HTTP_STRIPE_SIGNATURE');
        $content = $request->getContent();

        if (! $webhookSecret) {
            $this->logWebhook(['webhook_error' => 'Webhook secret not configured']);

            return response('Webhook secret not configured', 400);
        }

        if (! $signature) {
            $this->logWebhook(['webhook_error' => 'Missing Stripe signature header']);

            return response('Missing signature', 400);
        }

        if (! $content) {
            $this->logWebhook(['webhook_error' => 'Empty request body']);

            return response('Empty request body', 400);
        }

        try {
            do_action('payment_before_making_api_request', STRIPE_PAYMENT_METHOD_NAME, ['content' => $content]);

            $event = Webhook::constructEvent($content, $signature, $webhookSecret);

            do_action('payment_after_api_response', STRIPE_PAYMENT_METHOD_NAME, ['content' => $content], $event->toArray());

            $this->logWebhook([
                'webhook_event' => $event->type,
                'event_id' => $event->id,
            ]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object, $stripePaymentService); // @phpstan-ignore argument.type

                    break;

                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object, $stripePaymentService); // @phpstan-ignore argument.type

                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object); // @phpstan-ignore argument.type

                    break;

                case 'charge.refunded':
                    $this->handleChargeRefunded($event->data->object); // @phpstan-ignore argument.type

                    break;

                default:
                    $this->logWebhook(['webhook_info' => 'Unhandled event type: ' . $event->type]);
            }

            return response('OK', 200);
        } catch (SignatureVerificationException $e) {
            $this->logWebhook([
                'webhook_error' => 'Signature verification failed',
                'message' => $e->getMessage(),
            ]);
            BaseHelper::logError($e);

            return response('Invalid signature', 403);
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

    protected function handleCheckoutSessionCompleted(Session $session, StripePaymentService $stripePaymentService): void
    {
        if ($session->payment_status !== 'paid') {
            $this->logWebhook([
                'webhook_info' => 'Checkout session not paid yet',
                'session_id' => $session->id,
                'payment_status' => $session->payment_status,
            ]);

            return;
        }

        $stripePaymentService->setClient();

        $paymentIntentId = $session->payment_intent;

        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
        $chargeId = $paymentIntent->latest_charge;

        if (! $chargeId) {
            $this->logWebhook([
                'webhook_error' => 'No charge found for payment intent',
                'payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        $payment = Payment::query()->where('charge_id', $chargeId)->first();

        if ($payment) {
            if ($payment->status === PaymentStatusEnum::COMPLETED) {
                $this->logWebhook([
                    'webhook_info' => 'Payment already completed',
                    'charge_id' => $chargeId,
                ]);

                return;
            }

            $payment->status = PaymentStatusEnum::COMPLETED;
            $payment->save();

            $this->logWebhook([
                'webhook_success' => 'Payment status updated to completed',
                'charge_id' => $chargeId,
            ]);

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'charge_id' => $chargeId,
                'order_id' => $payment->order_id,
            ]);

            return;
        }

        $metadata = $session->metadata ? $session->metadata->toArray() : [];

        if (empty($metadata['order_id'])) {
            $this->logWebhook([
                'webhook_error' => 'No order_id in session metadata',
                'session_id' => $session->id,
            ]);

            return;
        }

        $orderIds = json_decode($metadata['order_id'], true);

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => Arr::get($metadata, 'amount'),
            'currency' => strtoupper($session->currency),
            'charge_id' => $chargeId,
            'order_id' => $orderIds,
            'customer_id' => Arr::get($metadata, 'customer_id'),
            'customer_type' => Arr::get($metadata, 'customer_type'),
            'payment_channel' => STRIPE_PAYMENT_METHOD_NAME,
            'status' => PaymentStatusEnum::COMPLETED,
            'payment_fee' => Arr::get($metadata, 'payment_fee', 0),
        ]);

        $this->logWebhook([
            'webhook_success' => 'Payment processed via checkout.session.completed',
            'charge_id' => $chargeId,
            'order_ids' => $orderIds,
        ]);
    }

    protected function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent, StripePaymentService $stripePaymentService): void
    {
        $stripePaymentService->setClient();

        $chargeId = $paymentIntent->latest_charge;

        if (! $chargeId) {
            $this->logWebhook([
                'webhook_info' => 'No charge found for payment intent (might be handled by checkout.session.completed)',
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return;
        }

        $payment = Payment::query()->where('charge_id', $chargeId)->first();

        if (! $payment) {
            $this->logWebhook([
                'webhook_info' => 'Payment not found (might be processed via checkout.session.completed)',
                'charge_id' => $chargeId,
            ]);

            return;
        }

        if ($payment->status === PaymentStatusEnum::COMPLETED) {
            $this->logWebhook([
                'webhook_info' => 'Payment already completed',
                'charge_id' => $chargeId,
            ]);

            return;
        }

        $payment->status = PaymentStatusEnum::COMPLETED;
        $payment->save();

        $this->logWebhook([
            'webhook_success' => 'Payment status updated via payment_intent.succeeded',
            'charge_id' => $chargeId,
        ]);

        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'charge_id' => $chargeId,
            'order_id' => $payment->order_id,
        ]);
    }

    protected function handlePaymentIntentFailed(PaymentIntent $paymentIntent): void
    {
        $chargeId = $paymentIntent->latest_charge;

        $this->logWebhook([
            'webhook_payment_failed' => true,
            'payment_intent_id' => $paymentIntent->id,
            'charge_id' => $chargeId,
            'failure_message' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
        ]);

        if ($chargeId) {
            $payment = Payment::query()->where('charge_id', $chargeId)->first();

            if ($payment && $payment->status !== PaymentStatusEnum::FAILED) {
                $payment->status = PaymentStatusEnum::FAILED;
                $payment->save();

                do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                    'charge_id' => $chargeId,
                    'order_id' => $payment->order_id,
                    'status' => PaymentStatusEnum::FAILED,
                ]);
            }
        }
    }

    protected function handleChargeRefunded(Charge $charge): void
    {
        $chargeId = $charge->id;

        $payment = Payment::query()->where('charge_id', $chargeId)->first();

        if (! $payment) {
            $this->logWebhook([
                'webhook_info' => 'Payment not found for refund',
                'charge_id' => $chargeId,
            ]);

            return;
        }

        $isFullyRefunded = $charge->refunded;
        $newStatus = $isFullyRefunded ? PaymentStatusEnum::REFUNDED : PaymentStatusEnum::REFUNDING;

        if ($payment->status != $newStatus) {
            $payment->status = $newStatus;
            $payment->save();

            $this->logWebhook([
                'webhook_refund' => true,
                'charge_id' => $chargeId,
                'is_fully_refunded' => $isFullyRefunded,
                'new_status' => $newStatus,
            ]);

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'charge_id' => $chargeId,
                'order_id' => $payment->order_id,
                'status' => $newStatus,
                'is_refund_update' => true,
            ]);
        }
    }

    public function success(
        StripePaymentCallbackRequest $request,
        StripePaymentService $stripePaymentService,
        BaseHttpResponse $response
    ) {
        try {
            $stripePaymentService->setClient();

            $sessionId = $request->input('session_id');

            do_action('payment_before_making_api_request', STRIPE_PAYMENT_METHOD_NAME, ['id' => $sessionId]);

            $session = Session::retrieve($sessionId);

            do_action('payment_after_api_response', STRIPE_PAYMENT_METHOD_NAME, ['id' => $sessionId], $session->toArray());

            if ($session->payment_status == 'paid') {
                $metadata = $session->metadata->toArray();

                $orderIds = json_decode($metadata['order_id'], true);

                $charge = PaymentIntent::retrieve($session->payment_intent);

                if (! $charge->latest_charge) {
                    return $response
                        ->setError()
                        ->setNextUrl(PaymentHelper::getCancelURL())
                        ->setMessage(trans('plugins/stripe::stripe.no_payment_charge'));
                }

                $chargeId = $charge->latest_charge;

                do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                    'amount' => $metadata['amount'],
                    'currency' => strtoupper($session->currency),
                    'charge_id' => $chargeId,
                    'order_id' => $orderIds,
                    'customer_id' => Arr::get($metadata, 'customer_id'),
                    'customer_type' => Arr::get($metadata, 'customer_type'),
                    'payment_channel' => STRIPE_PAYMENT_METHOD_NAME,
                    'status' => PaymentStatusEnum::COMPLETED,
                    'payment_fee' => Arr::get($metadata, 'payment_fee', 0),
                ]);

                return $response
                    ->setNextUrl(PaymentHelper::getRedirectURL() . '?charge_id=' . $chargeId)
                    ->setMessage(trans('plugins/payment::payment.checkout_success'));
            }

            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage(trans('plugins/stripe::stripe.payment_failed'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage($exception->getMessage() ?: trans('plugins/stripe::stripe.payment_failed'));
        }
    }

    public function error(BaseHttpResponse $response)
    {
        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL())
            ->withInput()
            ->setMessage(trans('plugins/stripe::stripe.payment_failed'));
    }
}
