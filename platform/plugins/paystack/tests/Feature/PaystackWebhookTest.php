<?php

namespace Botble\Paystack\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaystackWebhookTest extends BaseTestCase
{
    use RefreshDatabase;

    protected string $secretKey = 'sk_test_abc123secret';

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forceSet('payment_paystack_secret', $this->secretKey);
        Setting::save();
    }

    protected function webhookUrl(): string
    {
        return route('paystack.webhook');
    }

    protected function signPayload(string $payload): string
    {
        return hash_hmac('sha512', $payload, $this->secretKey);
    }

    protected function buildChargeSuccessPayload(
        string $reference,
        int $amountInKobo = 500000,
        string $currency = 'NGN',
        array $metadata = [],
    ): array {
        return [
            'event' => 'charge.success',
            'data' => [
                'id' => rand(100000, 999999),
                'reference' => $reference,
                'amount' => $amountInKobo,
                'currency' => $currency,
                'status' => 'success',
                'metadata' => $metadata,
            ],
        ];
    }

    protected function buildRefundProcessedPayload(string $transactionReference): array
    {
        return [
            'event' => 'refund.processed',
            'data' => [
                'id' => rand(100000, 999999),
                'status' => 'processed',
                'transaction' => [
                    'reference' => $transactionReference,
                ],
            ],
        ];
    }

    protected function createPaystackPayment(
        string $chargeId,
        string $status = PaymentStatusEnum::PENDING,
        float $amount = 5000,
        string $currency = 'NGN',
    ): Payment {
        return Payment::query()->create([
            'charge_id' => $chargeId,
            'payment_channel' => 'paystack',
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status,
            'order_id' => 1,
        ]);
    }

    public function test_webhook_rejects_missing_signature(): void
    {
        $payload = json_encode($this->buildChargeSuccessPayload('ref_123'));

        $response = $this->postJson($this->webhookUrl(), json_decode($payload, true));

        $response->assertStatus(400);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode($this->buildChargeSuccessPayload('ref_123'));

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => 'invalid_signature', 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(403);
    }

    public function test_webhook_accepts_valid_signature(): void
    {
        $payload = json_encode($this->buildChargeSuccessPayload('ref_valid', metadata: [
            'order_id' => [1],
            'customer_id' => 1,
            'customer_type' => 'Botble\Ecommerce\Models\Customer',
        ]));

        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);
    }

    public function test_charge_success_updates_existing_pending_payment(): void
    {
        $reference = 'ref_existing_' . uniqid();
        $payment = $this->createPaystackPayment($reference, PaymentStatusEnum::PENDING);

        $payload = json_encode($this->buildChargeSuccessPayload($reference));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertTrue($payment->status == PaymentStatusEnum::COMPLETED);
    }

    public function test_charge_success_skips_already_completed_payment(): void
    {
        $reference = 'ref_completed_' . uniqid();
        $payment = $this->createPaystackPayment($reference, PaymentStatusEnum::COMPLETED);

        $payload = json_encode($this->buildChargeSuccessPayload($reference));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertTrue($payment->status == PaymentStatusEnum::COMPLETED);
    }

    public function test_charge_success_creates_payment_when_not_exists(): void
    {
        $reference = 'ref_new_' . uniqid();

        $payload = json_encode($this->buildChargeSuccessPayload($reference, 250000, 'NGN', [
            'order_id' => [99],
            'customer_id' => 5,
            'customer_type' => 'Botble\Ecommerce\Models\Customer',
        ]));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);
    }

    public function test_charge_success_without_order_id_returns_ok(): void
    {
        $reference = 'ref_no_order_' . uniqid();

        $payload = json_encode($this->buildChargeSuccessPayload($reference, 100000, 'NGN', []));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);
    }

    public function test_refund_processed_updates_payment_to_refunded(): void
    {
        $reference = 'ref_refund_' . uniqid();
        $payment = $this->createPaystackPayment($reference, PaymentStatusEnum::COMPLETED);

        $payload = json_encode($this->buildRefundProcessedPayload($reference));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertTrue($payment->status == PaymentStatusEnum::REFUNDED);
    }

    public function test_refund_processed_skips_already_refunded(): void
    {
        $reference = 'ref_already_refunded_' . uniqid();
        $payment = $this->createPaystackPayment($reference, PaymentStatusEnum::REFUNDED);

        $payload = json_encode($this->buildRefundProcessedPayload($reference));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertTrue($payment->status == PaymentStatusEnum::REFUNDED);
    }

    public function test_refund_processed_payment_not_found(): void
    {
        $payload = json_encode($this->buildRefundProcessedPayload('ref_nonexistent_' . uniqid()));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);
    }

    public function test_unhandled_event_returns_ok(): void
    {
        $payload = json_encode([
            'event' => 'transfer.success',
            'data' => ['id' => 12345],
        ]);
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(200);
    }

    public function test_webhook_rejects_empty_body(): void
    {
        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => 'some_signature', 'CONTENT_TYPE' => 'application/json'],
            '',
        );

        $response->assertStatus(400);
    }

    public function test_webhook_rejects_when_secret_not_configured(): void
    {
        Setting::forceSet('payment_paystack_secret', '');
        Setting::save();

        $payload = json_encode($this->buildChargeSuccessPayload('ref_no_key'));
        $signature = $this->signPayload($payload);

        $response = $this->call(
            'POST',
            $this->webhookUrl(),
            [],
            [],
            [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );

        $response->assertStatus(400);
    }
}
