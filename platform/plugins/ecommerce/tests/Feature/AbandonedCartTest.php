<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Enums\StockStatusEnum;
use Botble\Ecommerce\Models\AbandonedCart;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\AbandonedCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AbandonedCartTest extends BaseTestCase
{
    use RefreshDatabase;

    protected AbandonedCartService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AbandonedCartService();
    }

    public function test_can_create_abandoned_cart(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-session-123',
            'cart_data' => [
                ['id' => 1, 'name' => 'Product 1', 'qty' => 2, 'price' => 100],
            ],
            'total_amount' => 200,
            'items_count' => 2,
            'email' => 'customer@example.com',
            'abandoned_at' => now(),
        ]);

        $this->assertDatabaseHas('ec_abandoned_carts', [
            'session_id' => 'test-session-123',
            'email' => 'customer@example.com',
            'total_amount' => 200,
        ]);
    }

    public function test_abandoned_cart_generates_tokens_on_creation(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-session',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now(),
        ]);

        $this->assertNotNull($cart->recovery_token);
        $this->assertNotNull($cart->unsubscribe_token);
        $this->assertEquals(64, strlen($cart->recovery_token));
        $this->assertEquals(64, strlen($cart->unsubscribe_token));
    }

    public function test_can_create_abandoned_cart_with_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $cart = AbandonedCart::query()->create([
            'customer_id' => $customer->id,
            'cart_data' => [],
            'total_amount' => 150,
            'items_count' => 3,
            'email' => $customer->email,
            'customer_name' => $customer->name,
            'abandoned_at' => now(),
        ]);

        $this->assertEquals($customer->id, $cart->customer_id);
        $this->assertEquals($customer->name, $cart->customer->name);
    }

    public function test_scope_abandoned_filters_correctly(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'abandoned-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'recovered-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => true,
            'abandoned_at' => now()->subHours(2),
        ]);

        $abandonedCarts = AbandonedCart::query()->abandoned()->get();

        $this->assertCount(1, $abandonedCarts);
        $this->assertEquals('abandoned-cart', $abandonedCarts->first()->session_id);
    }

    public function test_scope_needs_sequence_email(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'needs-email-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test1@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
            'last_email_sequence' => 0,
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'already-sent-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test2@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
            'last_email_sequence' => 1,
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'too-recent',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test3@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subMinutes(30),
            'last_email_sequence' => 0,
        ]);

        $cartsNeedingEmail1 = AbandonedCart::query()->needsSequenceEmail(1, 1)->get();

        $this->assertCount(1, $cartsNeedingEmail1);
        $this->assertEquals('needs-email-1', $cartsNeedingEmail1->first()->session_id);
    }

    public function test_mark_as_recovered(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
        ]);

        $order = Order::query()->create([
            'code' => 'TEST-001',
            'amount' => 100,
            'sub_total' => 100,
            'shipping_amount' => 0,
            'status' => 'pending',
        ]);

        $cart->markAsRecovered($order);
        $cart->refresh();

        $this->assertTrue($cart->is_recovered);
        $this->assertNotNull($cart->recovered_at);
        $this->assertEquals($order->id, $cart->recovered_order_id);
    }

    public function test_update_email_sequence(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subHours(2),
            'last_email_sequence' => 0,
            'reminders_sent' => 0,
        ]);

        $cart->updateEmailSequence(1);
        $cart->refresh();

        $this->assertEquals(1, $cart->last_email_sequence);
        $this->assertEquals(1, $cart->reminders_sent);
        $this->assertNotNull($cart->reminder_sent_at);
    }

    public function test_mark_as_clicked(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subHours(2),
        ]);

        $this->assertNull($cart->clicked_at);

        $cart->markAsClicked();
        $cart->refresh();

        $this->assertNotNull($cart->clicked_at);
    }

    public function test_mark_as_clicked_only_once(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subHours(2),
        ]);

        $cart->markAsClicked();
        $firstClickedAt = $cart->fresh()->clicked_at;

        sleep(1);

        $cart->markAsClicked();
        $secondClickedAt = $cart->fresh()->clicked_at;

        $this->assertEquals($firstClickedAt->timestamp, $secondClickedAt->timestamp);
    }

    public function test_is_expired(): void
    {
        $expiredCart = AbandonedCart::query()->create([
            'session_id' => 'expired-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subDays(31),
        ]);

        $validCart = AbandonedCart::query()->create([
            'session_id' => 'valid-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subDays(29),
        ]);

        $this->assertTrue($expiredCart->isExpired(30));
        $this->assertFalse($validCart->isExpired(30));
    }

    public function test_scope_not_expired(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'expired-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subDays(31),
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'valid-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now()->subDays(15),
        ]);

        $notExpired = AbandonedCart::query()->notExpired(30)->get();

        $this->assertCount(1, $notExpired);
        $this->assertEquals('valid-cart', $notExpired->first()->session_id);
    }

    public function test_get_cart_items(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [
                ['id' => $product->id, 'name' => 'Test Product', 'qty' => 3, 'price' => 50],
            ],
            'total_amount' => 150,
            'items_count' => 3,
            'abandoned_at' => now(),
        ]);

        $items = $cart->getCartItems();

        $this->assertCount(1, $items);
        $this->assertEquals($product->id, $items[0]['product']->id);
        $this->assertEquals(3, $items[0]['quantity']);
    }

    public function test_service_get_sequence_delay(): void
    {
        $this->assertEquals(1, $this->service->getSequenceDelay(1));
        $this->assertEquals(24, $this->service->getSequenceDelay(2));
        $this->assertEquals(72, $this->service->getSequenceDelay(3));
        $this->assertEquals(1, $this->service->getSequenceDelay(99));
    }

    public function test_service_cleanup_old_abandoned_carts(): void
    {
        $oldCart1 = AbandonedCart::query()->create([
            'session_id' => 'old-cart-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now()->subDays(45),
        ]);
        AbandonedCart::query()->where('id', $oldCart1->id)->update(['created_at' => now()->subDays(45)]);

        $oldCart2 = AbandonedCart::query()->create([
            'session_id' => 'old-cart-2',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now()->subDays(35),
        ]);
        AbandonedCart::query()->where('id', $oldCart2->id)->update(['created_at' => now()->subDays(35)]);

        $recentCart = AbandonedCart::query()->create([
            'session_id' => 'recent-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now()->subDays(10),
        ]);
        AbandonedCart::query()->where('id', $recentCart->id)->update(['created_at' => now()->subDays(10)]);

        $deleted = $this->service->cleanupOldAbandonedCarts(30);

        $this->assertEquals(2, $deleted);
        $this->assertDatabaseHas('ec_abandoned_carts', ['session_id' => 'recent-cart']);
        $this->assertDatabaseMissing('ec_abandoned_carts', ['session_id' => 'old-cart-1']);
        $this->assertDatabaseMissing('ec_abandoned_carts', ['session_id' => 'old-cart-2']);
    }

    public function test_service_unsubscribe(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test@example.com',
            'abandoned_at' => now(),
        ]);

        $result = $this->service->unsubscribe($cart->unsubscribe_token);

        $this->assertTrue($result);
        $this->assertNotNull($cart->fresh()->unsubscribed_at);
    }

    public function test_service_unsubscribe_invalid_token(): void
    {
        $result = $this->service->unsubscribe('invalid-token');

        $this->assertFalse($result);
    }

    public function test_service_is_opted_out(): void
    {
        $cart1 = AbandonedCart::query()->create([
            'session_id' => 'cart-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'opted-out@example.com',
            'abandoned_at' => now()->subDays(5),
            'unsubscribed_at' => now()->subDays(4),
        ]);

        $cart2 = AbandonedCart::query()->create([
            'session_id' => 'cart-2',
            'cart_data' => [],
            'total_amount' => 200,
            'items_count' => 2,
            'email' => 'opted-out@example.com',
            'abandoned_at' => now(),
        ]);

        $this->assertTrue($this->service->isOptedOut($cart2));
    }

    public function test_service_is_not_opted_out(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'cart-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'active@example.com',
            'abandoned_at' => now(),
        ]);

        $this->assertFalse($this->service->isOptedOut($cart));
    }

    public function test_service_recover_cart(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'quantity' => 10,
            'with_storehouse_management' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
        ]);

        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [
                ['id' => $product->id, 'name' => 'Test Product', 'qty' => 2, 'price' => 100],
            ],
            'total_amount' => 200,
            'items_count' => 2,
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
        ]);

        $recovered = $this->service->recoverCart($cart->recovery_token);

        $this->assertNotNull($recovered);
        $this->assertEquals($cart->id, $recovered->id);
        $this->assertNotNull($recovered->fresh()->clicked_at);
    }

    public function test_service_recover_cart_invalid_token(): void
    {
        $recovered = $this->service->recoverCart('invalid-token');

        $this->assertNull($recovered);
    }

    public function test_service_recover_cart_already_recovered(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => true,
            'abandoned_at' => now()->subHours(2),
        ]);

        $recovered = $this->service->recoverCart($cart->recovery_token);

        $this->assertNull($recovered);
    }

    public function test_service_get_abandonment_rate(): void
    {
        for ($i = 0; $i < 8; $i++) {
            AbandonedCart::query()->create([
                'session_id' => "abandoned-{$i}",
                'cart_data' => [],
                'total_amount' => 100,
                'items_count' => 1,
                'is_recovered' => false,
                'abandoned_at' => now()->subHours($i + 1),
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            AbandonedCart::query()->create([
                'session_id' => "recovered-{$i}",
                'cart_data' => [],
                'total_amount' => 100,
                'items_count' => 1,
                'is_recovered' => true,
                'abandoned_at' => now()->subHours($i + 1),
            ]);
        }

        $rate = $this->service->getAbandonmentRate();

        $this->assertEquals(80.0, $rate);
    }

    public function test_service_get_recovery_rate(): void
    {
        for ($i = 0; $i < 7; $i++) {
            AbandonedCart::query()->create([
                'session_id' => "abandoned-{$i}",
                'cart_data' => [],
                'total_amount' => 100,
                'items_count' => 1,
                'is_recovered' => false,
                'abandoned_at' => now()->subHours($i + 1),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            AbandonedCart::query()->create([
                'session_id' => "recovered-{$i}",
                'cart_data' => [],
                'total_amount' => 100,
                'items_count' => 1,
                'is_recovered' => true,
                'abandoned_at' => now()->subHours($i + 1),
            ]);
        }

        $rate = $this->service->getRecoveryRate();

        $this->assertEquals(30.0, $rate);
    }

    public function test_service_get_revenue_recovered(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'recovered-1',
            'cart_data' => [],
            'total_amount' => 150.50,
            'items_count' => 1,
            'is_recovered' => true,
            'abandoned_at' => now(),
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'recovered-2',
            'cart_data' => [],
            'total_amount' => 200.25,
            'items_count' => 1,
            'is_recovered' => true,
            'abandoned_at' => now(),
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'not-recovered',
            'cart_data' => [],
            'total_amount' => 500,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now(),
        ]);

        $revenue = $this->service->getRevenueRecovered();

        $this->assertEquals(350.75, $revenue);
    }

    public function test_service_get_click_rate(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'clicked-1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'reminders_sent' => 1,
            'clicked_at' => now(),
            'abandoned_at' => now()->subHours(2),
        ]);

        AbandonedCart::query()->create([
            'session_id' => 'clicked-2',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'reminders_sent' => 2,
            'clicked_at' => now(),
            'abandoned_at' => now()->subHours(2),
        ]);

        for ($i = 0; $i < 8; $i++) {
            AbandonedCart::query()->create([
                'session_id' => "not-clicked-{$i}",
                'cart_data' => [],
                'total_amount' => 100,
                'items_count' => 1,
                'reminders_sent' => 1,
                'clicked_at' => null,
                'abandoned_at' => now()->subHours(2),
            ]);
        }

        $rate = $this->service->getClickRate();

        $this->assertEquals(20.0, $rate);
    }

    public function test_abandoned_cart_nullifies_invalid_customer_id(): void
    {
        $cart = AbandonedCart::query()->create([
            'customer_id' => 99999,
            'session_id' => 'test-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'abandoned_at' => now(),
        ]);

        $this->assertNull($cart->customer_id);
    }

    public function test_scope_needs_sequence_email_prevents_cascade_in_single_run(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'old-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(73),
            'last_email_sequence' => 0,
        ]);

        $matchesSeq1 = AbandonedCart::query()->needsSequenceEmail(1, 1)->get();
        $this->assertCount(1, $matchesSeq1);

        $matchesSeq2 = AbandonedCart::query()->needsSequenceEmail(2, 24)->get();
        $this->assertCount(0, $matchesSeq2, 'Cart at sequence 0 should not match sequence 2');

        $matchesSeq3 = AbandonedCart::query()->needsSequenceEmail(3, 72)->get();
        $this->assertCount(0, $matchesSeq3, 'Cart at sequence 0 should not match sequence 3');
    }

    public function test_scope_needs_sequence_email_requires_timing_gap_for_next_sequence(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'just-sent-seq1',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(25),
            'last_email_sequence' => 1,
            'reminder_sent_at' => now(),
            'reminders_sent' => 1,
        ]);

        $matchesSeq2 = AbandonedCart::query()->needsSequenceEmail(2, 24)->get();
        $this->assertCount(0, $matchesSeq2, 'Cart with reminder just sent should not match next sequence');
    }

    public function test_scope_needs_sequence_email_allows_next_sequence_after_timing_gap(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'ready-for-seq2',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(25),
            'last_email_sequence' => 1,
            'reminder_sent_at' => now()->subHours(2),
            'reminders_sent' => 1,
        ]);

        $matchesSeq2 = AbandonedCart::query()->needsSequenceEmail(2, 24)->get();
        $this->assertCount(1, $matchesSeq2);
    }

    public function test_scope_needs_sequence_email_excludes_unsubscribed(): void
    {
        AbandonedCart::query()->create([
            'session_id' => 'unsubscribed-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'unsubscribed@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
            'last_email_sequence' => 0,
            'unsubscribed_at' => now()->subHour(),
        ]);

        $matches = AbandonedCart::query()->needsSequenceEmail(1, 1)->get();
        $this->assertCount(0, $matches);
    }

    public function test_sequence_email_full_progression(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => 'progression-cart',
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'email' => 'test@example.com',
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(73),
            'last_email_sequence' => 0,
        ]);

        $this->assertCount(1, AbandonedCart::query()->needsSequenceEmail(1, 1)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(2, 24)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(3, 72)->get());

        $cart->updateEmailSequence(1);
        AbandonedCart::query()->where('id', $cart->id)->update(['reminder_sent_at' => now()->subHours(24)]);

        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(1, 1)->get());
        $this->assertCount(1, AbandonedCart::query()->needsSequenceEmail(2, 24)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(3, 72)->get());

        $cart->refresh();
        $cart->updateEmailSequence(2);
        AbandonedCart::query()->where('id', $cart->id)->update(['reminder_sent_at' => now()->subHours(48)]);

        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(1, 1)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(2, 24)->get());
        $this->assertCount(1, AbandonedCart::query()->needsSequenceEmail(3, 72)->get());

        $cart->refresh();
        $cart->updateEmailSequence(3);

        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(1, 1)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(2, 24)->get());
        $this->assertCount(0, AbandonedCart::query()->needsSequenceEmail(3, 72)->get());
    }

    public function test_service_recover_cart_skips_deleted_products(): void
    {
        $product = Product::query()->create([
            'name' => 'Existing Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
            'quantity' => 10,
            'with_storehouse_management' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
        ]);

        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [
                ['id' => 99999, 'name' => 'Deleted Product', 'qty' => 1, 'price' => 100, 'options' => []],
                ['id' => $product->id, 'name' => 'Existing Product', 'qty' => 1, 'price' => 50, 'options' => []],
            ],
            'total_amount' => 150,
            'items_count' => 2,
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
        ]);

        $recovered = $this->service->recoverCart($cart->recovery_token);

        $this->assertNotNull($recovered);
        $this->assertNotNull($recovered->fresh()->clicked_at);
    }

    public function test_service_recover_cart_skips_out_of_stock_products(): void
    {
        $outOfStockProduct = Product::query()->create([
            'name' => 'Out of Stock Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'quantity' => 0,
            'with_storehouse_management' => true,
            'stock_status' => StockStatusEnum::OUT_OF_STOCK,
        ]);

        $inStockProduct = Product::query()->create([
            'name' => 'In Stock Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
            'quantity' => 10,
            'with_storehouse_management' => false,
            'stock_status' => StockStatusEnum::IN_STOCK,
        ]);

        $cart = AbandonedCart::query()->create([
            'session_id' => 'test-cart',
            'cart_data' => [
                ['id' => $outOfStockProduct->id, 'name' => 'Out of Stock', 'qty' => 1, 'price' => 100, 'options' => []],
                ['id' => $inStockProduct->id, 'name' => 'In Stock', 'qty' => 1, 'price' => 50, 'options' => []],
            ],
            'total_amount' => 150,
            'items_count' => 2,
            'is_recovered' => false,
            'abandoned_at' => now()->subHours(2),
        ]);

        $recovered = $this->service->recoverCart($cart->recovery_token);

        $this->assertNotNull($recovered);
    }

    public function test_service_update_customer_info(): void
    {
        $cart = AbandonedCart::query()->create([
            'session_id' => session()->getId(),
            'cart_data' => [],
            'total_amount' => 100,
            'items_count' => 1,
            'is_recovered' => false,
            'abandoned_at' => now(),
        ]);

        $this->service->updateCustomerInfo([
            'email' => 'updated@example.com',
            'phone' => '1234567890',
            'name' => 'Updated Name',
        ]);

        $cart->refresh();

        $this->assertEquals('updated@example.com', $cart->email);
        $this->assertEquals('1234567890', $cart->phone);
        $this->assertEquals('Updated Name', $cart->customer_name);
    }
}
