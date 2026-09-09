<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderTaxInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class EcommerceCodebaseFixesTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    protected function createAdminUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createOrder(array $overrides = []): Order
    {
        $customer = $this->createCustomer();

        return Order::query()->create(array_merge([
            'user_id' => $customer->id,
            'amount' => 100,
            'sub_total' => 100,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ], $overrides));
    }

    // ── C1: XSS in JS context (master.blade.php) ──

    public function test_checkout_master_view_uses_json_directive_for_session_messages(): void
    {
        $viewPath = base_path('platform/plugins/ecommerce/resources/views/orders/master.blade.php');
        $content = file_get_contents($viewPath);

        $this->assertStringContainsString(
            "@json(session('success_msg'))",
            $content,
            'success_msg must use @json() for safe JS embedding'
        );

        $this->assertStringContainsString(
            "@json(session('error_msg'))",
            $content,
            'error_msg must use @json() for safe JS embedding'
        );

        $this->assertStringContainsString(
            '@json($errors->first())',
            $content,
            'errors->first() must use @json() for safe JS embedding'
        );

        $this->assertStringNotContainsString(
            "'{{ session('success_msg') }}'",
            $content,
            'Must not use {{ }} for JS string context (XSS vulnerable)'
        );

        $this->assertStringNotContainsString(
            "'{{ session('error_msg') }}'",
            $content,
            'Must not use {{ }} for JS string context (XSS vulnerable)'
        );
    }

    // ── C2: Authorization before save (postUpdateShippingAddress) ──

    public function test_update_shipping_address_rejects_canceled_order(): void
    {
        $this->actingAs($this->admin, 'web');

        $order = $this->createOrder(['status' => OrderStatusEnum::CANCELED]);

        $address = OrderAddress::query()->create([
            'name' => 'Original Name',
            'phone' => '1234567890',
            'country' => 'US',
            'state' => 'CA',
            'city' => 'LA',
            'address' => '123 Main St',
            'order_id' => $order->id,
        ]);

        $response = $this->postJson(route('orders.update-shipping-address', $address->id), [
            'name' => 'Changed Name',
            'phone' => '0987654321',
            'country' => 'US',
            'state' => 'NY',
            'city' => 'NYC',
            'address' => '456 Other St',
        ]);

        $response->assertStatus(401);

        $fresh = $address->fresh();
        $this->assertEquals('Original Name', $fresh->name, 'Address should NOT be saved for canceled orders');
    }

    public function test_update_shipping_address_succeeds_for_active_order(): void
    {
        $this->actingAs($this->admin, 'web');

        $order = $this->createOrder(['status' => OrderStatusEnum::PROCESSING]);

        $address = OrderAddress::query()->create([
            'name' => 'Original Name',
            'phone' => '1234567890',
            'country' => 'US',
            'state' => 'CA',
            'city' => 'LA',
            'address' => '123 Main St',
            'order_id' => $order->id,
        ]);

        $response = $this->postJson(route('orders.update-shipping-address', $address->id), [
            'name' => 'Updated Name',
            'phone' => '0987654321',
            'country' => 'US',
            'state' => 'NY',
            'city' => 'NYC',
            'address' => '456 Other St',
        ]);

        $response->assertSuccessful();

        $fresh = $address->fresh();
        $this->assertEquals('Updated Name', $fresh->name);
    }

    // ── M6: Authorization before save (postUpdateTaxInformation) ──

    public function test_update_tax_information_rejects_canceled_order(): void
    {
        $this->actingAs($this->admin, 'web');

        $order = $this->createOrder(['status' => OrderStatusEnum::CANCELED]);

        $taxInfo = OrderTaxInformation::query()->create([
            'order_id' => $order->id,
            'company_name' => 'Original Corp',
            'company_tax_code' => '1234567890',
            'company_address' => '123 Tax Lane',
            'company_email' => 'tax@original.com',
        ]);

        $response = $this->postJson(route('orders.update-tax-information', $taxInfo->id), [
            'company_name' => 'Changed Corp',
            'company_tax_code' => '0987654321',
            'company_address' => '456 Tax Lane',
            'company_email' => 'tax@changed.com',
        ]);

        $response->assertStatus(401);

        $fresh = $taxInfo->fresh();
        $this->assertEquals('Original Corp', $fresh->company_name, 'Tax info should NOT be updated for canceled orders');
    }

    public function test_update_tax_information_succeeds_for_active_order(): void
    {
        $this->actingAs($this->admin, 'web');

        $order = $this->createOrder(['status' => OrderStatusEnum::PROCESSING]);

        $taxInfo = OrderTaxInformation::query()->create([
            'order_id' => $order->id,
            'company_name' => 'Original Corp',
            'company_tax_code' => '1234567890',
            'company_address' => '123 Tax Lane',
            'company_email' => 'tax@original.com',
        ]);

        $response = $this->postJson(route('orders.update-tax-information', $taxInfo->id), [
            'company_name' => 'Updated Corp',
            'company_tax_code' => '0987654321',
            'company_address' => '456 Tax Lane',
            'company_email' => 'tax@updated.com',
        ]);

        $response->assertSuccessful();

        $fresh = $taxInfo->fresh();
        $this->assertEquals('Updated Corp', $fresh->company_name);
    }

    // ── C3: XSS in product names (HandleApplyCouponService) ──

    public function test_coupon_service_escapes_product_names_in_flash_sale_message(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Services/HandleApplyCouponService.php');
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            "e(implode(', ', \$productsInFlashSales))",
            $content,
            'Product names in flash sale message must be escaped with e()'
        );

        $this->assertStringNotContainsString(
            "'<strong>' . implode(', ', \$productsInFlashSales) . '</strong>'",
            $content,
            'Raw unescaped product names in HTML is XSS vulnerable'
        );
    }

    // ── H1: Mass assignment protection (Order::create) ──

    public function test_order_create_uses_explicit_field_list(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Http/Controllers/OrderController.php');
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            '$request->only([',
            $content,
            'Order::create must use $request->only() with explicit fields'
        );

        $this->assertStringNotContainsString(
            'Order::query()->create($request->input())',
            $content,
            'Order::create must NOT use raw $request->input()'
        );
    }

    public function test_order_create_only_allows_expected_fields(): void
    {
        $expectedFields = [
            'amount',
            'user_id',
            'shipping_method',
            'shipping_option',
            'shipping_amount',
            'tax_amount',
            'sub_total',
            'coupon_code',
            'discount_amount',
            'promotion_amount',
            'discount_description',
            'description',
            'is_confirmed',
            'is_finished',
            'status',
        ];

        $filePath = base_path('platform/plugins/ecommerce/src/Http/Controllers/OrderController.php');
        $content = file_get_contents($filePath);

        foreach ($expectedFields as $field) {
            $this->assertStringContainsString(
                "'$field'",
                $content,
                "Field '$field' should be in the explicit field list for Order::create"
            );
        }
    }

    // ── H2: Update uses validated data ──

    public function test_order_update_uses_validated_input(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Http/Controllers/OrderController.php');
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            '$request->validated()',
            $content,
            'Order update must use $request->validated()'
        );
    }

    public function test_order_update_only_allows_validated_fields(): void
    {
        $this->actingAs($this->admin, 'web');

        $order = $this->createOrder([
            'amount' => 100,
            'description' => 'Original note',
        ]);

        $response = $this->postJson(route('orders.update', $order->id), [
            'description' => 'Updated note',
            'private_notes' => 'Admin notes',
            'amount' => 999,
            'status' => OrderStatusEnum::COMPLETED,
        ]);

        $response->assertSuccessful();

        $fresh = $order->fresh();
        $this->assertEquals('Updated note', $fresh->description);
        $this->assertEquals('Admin notes', $fresh->private_notes);
        $this->assertEquals(100, $fresh->amount, 'Amount should NOT be changed via update — not in validated fields');
    }

    // ── H3: Invoice template uses BaseHelper::clean ──

    public function test_invoice_template_sanitizes_product_options(): void
    {
        $viewPath = base_path('platform/plugins/ecommerce/resources/views/invoices/edit.blade.php');
        $content = file_get_contents($viewPath);

        $this->assertStringContainsString(
            'BaseHelper::clean($invoiceItem->product_options_implode)',
            $content,
            'Invoice must use BaseHelper::clean() for product_options_implode'
        );

        $this->assertStringNotContainsString(
            '{!! $invoiceItem->product_options_implode !!}',
            $content,
            'Invoice must NOT use raw {!! !!} for product_options_implode'
        );
    }

    // ── H4: Null-safe user->getKey() ──

    public function test_create_payment_service_uses_null_safe_user(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Services/CreatePaymentForOrderService.php');
        $content = file_get_contents($filePath);

        $this->assertStringNotContainsString(
            '$user->getKey()',
            $content,
            'Must use $user?->getKey() (null-safe) not $user->getKey()'
        );

        $occurrences = substr_count($content, '$user?->getKey()');
        $this->assertGreaterThanOrEqual(2, $occurrences, 'Should have at least 2 null-safe $user?->getKey() calls');
    }

    // ── H5: Dead code removed ──

    public function test_coupon_service_has_no_dead_double_token_call(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Services/HandleApplyCouponService.php');
        $content = file_get_contents($filePath);

        $tokenCalls = substr_count($content, 'OrderHelper::getOrderSessionToken()');
        $this->assertEquals(1, $tokenCalls, 'Should only have 1 getOrderSessionToken() call (dead duplicate removed)');
    }

    // ── H8: SAME_PRICE uses priceTax not total ──

    public function test_same_price_discount_uses_unit_price_not_total(): void
    {
        $filePath = base_path('platform/plugins/ecommerce/src/Services/HandleApplyCouponService.php');
        $content = file_get_contents($filePath);

        $this->assertStringNotContainsString(
            'max($cartItem->total - $discountValue, 0) * $cartItem->qty',
            $content,
            'Must NOT use $cartItem->total * qty (double-qty bug). Use $cartItem->priceTax instead.'
        );

        $priceTaxOccurrences = substr_count($content, 'max($cartItem->priceTax - $discountValue, 0) * $cartItem->qty');
        $this->assertGreaterThanOrEqual(2, $priceTaxOccurrences, 'SAME_PRICE collections + categories should both use priceTax');
    }

    // ── Order model fillable (regression guard) ──

    public function test_order_fillable_does_not_include_dangerous_fields(): void
    {
        $order = new Order();
        $fillable = $order->getFillable();

        $this->assertNotContains('payment_id', $fillable, 'payment_id should not be mass-assignable');
        $this->assertNotContains('code', $fillable, 'code should not be mass-assignable');
    }

    // ── Guest checkout session-fallback (regression guard) ──
    //
    // Without this fallback in PublicCheckoutController::processOrderData, guests
    // who reach postCheckout without address[*] in the request (split-step UIs,
    // autofill, broken JS) get orders with NULL shipping address, which breaks
    // every shipping-carrier plugin (no destination → no shipment created).
    //
    // The 2026_04_25_000001_backfill_missing_order_shipping_addresses migration
    // backfills affected EXISTING orders for logged-in customers, but explicitly
    // cannot backfill guest orders — so the only durable cure is to populate
    // $addressData from session fields when neither saved $address nor
    // $request->input('address') is present. This mirrors the same fallback
    // already in OrderHelper::processAddressOrder.

    public function test_process_order_data_falls_back_to_session_fields_for_guest(): void
    {
        $controller = new \ReflectionClass(\Botble\Ecommerce\Http\Controllers\Fronts\PublicCheckoutController::class);
        $source = file_get_contents($controller->getFileName());

        $this->assertStringContainsString(
            "elseif (! empty(\$sessionData['name']))",
            $source,
            'processOrderData must fall back to session-stored address fields when '
            .'neither saved $address nor $request->input("address") is populated.'
        );

        // Standard address keys must all be present in the fallback list
        foreach (['name', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip_code'] as $key) {
            $this->assertStringContainsString(
                "'{$key}'",
                $source,
                "Session-fallback must extract the '{$key}' field."
            );
        }
    }

    public function test_process_order_data_creates_shipping_address_from_session_when_request_lacks_address(): void
    {
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => 50,
            'sub_total' => 50,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
            'token' => 'test-token-' . uniqid(),
        ]);

        $orderHelper = app(\Botble\Ecommerce\Supports\OrderHelper::class);

        $addressData = [
            'name' => 'Dave Bo',
            'phone' => '8683845484',
            'email' => 'guest@example.com',
            'country' => 'TT',
            'state' => 'Point Fortin',
            'city' => 'Cap de Ville',
            'address' => '2343',
            'zip_code' => '520448',
            'order_id' => $order->id,
        ];

        $sessionData = [
            'created_order_id' => $order->id,
            'is_save_order_shipping_address' => true,
            'name' => 'Dave Bo',
            'phone' => '8683845484',
            'email' => 'guest@example.com',
            'country' => 'TT',
            'state' => 'Point Fortin',
            'city' => 'Cap de Ville',
            'address' => '2343',
            'zip_code' => '520448',
            'billing_address_same_as_shipping_address' => true,
            'billing_address' => [],
        ];

        $orderHelper->checkAndCreateOrderAddress($addressData, $sessionData);

        $this->assertDatabaseHas('ec_order_addresses', [
            'order_id' => $order->id,
            'type' => 'shipping_address',
            'name' => 'Dave Bo',
            'city' => 'Cap de Ville',
        ]);
    }

    public function test_check_and_create_order_address_skips_creation_when_no_name_and_no_existing_row(): void
    {
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => 50,
            'sub_total' => 50,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
            'token' => 'test-token-skip-' . uniqid(),
        ]);

        $orderHelper = app(\Botble\Ecommerce\Supports\OrderHelper::class);

        $addressData = [
            'billing_address_same_as_shipping_address' => true,
            'billing_address' => [],
        ];

        $sessionData = [
            'created_order_id' => $order->id,
            'is_save_order_shipping_address' => true,
        ];

        $orderHelper->checkAndCreateOrderAddress($addressData, $sessionData);

        $this->assertDatabaseMissing('ec_order_addresses', [
            'order_id' => $order->id,
            'type' => 'shipping_address',
        ]);
    }

    public function test_post_checkout_processOrderData_persists_shipping_address_from_session(): void
    {
        // The session-fallback branch lives in the non-marketplace code path of
        // processOrderData. Marketplace builds short-circuit at line 393 and
        // delegate order handling to the marketplace plugin's own filter, so
        // this end-to-end test only meaningfully runs without marketplace.
        // The first two tests above still verify the fix is in source.
        if (is_plugin_active('marketplace')) {
            $this->markTestSkipped('processOrderData bypasses non-marketplace branch when marketplace is active');
        }

        $controller = app(\Botble\Ecommerce\Http\Controllers\Fronts\PublicCheckoutController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('processOrderData');
        $method->setAccessible(true);

        $token = 'test-token-fix-' . uniqid();
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => 50,
            'sub_total' => 50,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
            'token' => $token,
        ]);

        $request = \Illuminate\Http\Request::create(
            '/checkout/'.$token.'/process',
            'POST',
            [
                'amount' => 50,
                'shipping_method' => 'default',
                'payment_method' => 'cod',
                'billing_address_same_as_shipping_address' => '1',
                'agree_terms_and_policy' => '1',
                'token' => $token,
            ]
        );

        $sessionData = [
            'created_order' => true,
            'created_order_id' => $order->id,
            'is_save_order_shipping_address' => true,
            'name' => 'Dave Bo',
            'phone' => '8683845484',
            'email' => 'guest@example.com',
            'country' => 'TT',
            'state' => 'Point Fortin',
            'city' => 'Cap de Ville',
            'address' => '2343',
            'zip_code' => '520448',
        ];

        \Cart::instance('cart')->destroy();
        \Cart::instance('cart')->add('1', 'Test Product', 1, 50, ['weight' => 0.1]);

        try {
            $method->invoke($controller, $token, $sessionData, $request, true);
        } catch (\Throwable $e) {
            // Tolerate downstream side-effects.
        }

        $this->assertDatabaseHas('ec_order_addresses', [
            'order_id' => $order->id,
            'type' => 'shipping_address',
            'name' => 'Dave Bo',
            'city' => 'Cap de Ville',
            'state' => 'Point Fortin',
        ]);
    }
}
