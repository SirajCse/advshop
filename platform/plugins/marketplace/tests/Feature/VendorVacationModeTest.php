<?php

namespace Botble\Marketplace\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Providers\HookServiceProvider;
use Botble\Marketplace\Providers\OrderSupportServiceProvider;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class VendorVacationModeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createStore(bool $onVacation = false, ?string $message = null): Store
    {
        $customer = Customer::query()->create([
            'name' => 'Vacation Vendor',
            'email' => 'vacation-vendor-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_vendor' => true,
        ]);

        return Store::query()->create([
            'name' => 'Holiday Store',
            'email' => 'store-' . uniqid() . '@example.com',
            'customer_id' => $customer->id,
            'status' => StoreStatusEnum::PUBLISHED,
            'vacation_mode' => $onVacation,
            'vacation_message' => $message,
        ]);
    }

    protected function createProductForStore(Store $store): Product
    {
        $product = Product::query()->create([
            'name' => 'Holiday Product',
            'status' => BaseStatusEnum::PUBLISHED,
            'price' => 10,
        ]);

        // store_id is a marketplace-added column and is not in Product's $fillable,
        // so assign it directly (mass-assignment would silently drop it).
        $product->store_id = $store->id;
        $product->save();

        return $product;
    }

    protected function hookProvider(): HookServiceProvider
    {
        return new HookServiceProvider($this->app);
    }

    // ---------------------------------------------------------------------
    // Model
    // ---------------------------------------------------------------------

    public function test_is_on_vacation_returns_true_when_enabled(): void
    {
        $store = $this->createStore(onVacation: true);

        $this->assertTrue($store->isOnVacation());
    }

    public function test_is_on_vacation_returns_false_by_default(): void
    {
        $store = $this->createStore();

        $this->assertFalse($store->isOnVacation());
    }

    public function test_vacation_mode_is_cast_to_boolean(): void
    {
        $store = $this->createStore(onVacation: true);
        $store->refresh();

        $this->assertIsBool($store->vacation_mode);
        $this->assertTrue($store->vacation_mode);
    }

    // ---------------------------------------------------------------------
    // Add-to-cart guard
    // ---------------------------------------------------------------------

    public function test_add_to_cart_blocked_with_custom_message(): void
    {
        $store = $this->createStore(onVacation: true, message: 'Back on July 1st!');
        $product = $this->createProductForStore($store);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Back on July 1st!');

        $this->hookProvider()->blockAddToCartForVacationStore($product);
    }

    public function test_add_to_cart_blocked_with_default_message_when_no_custom_message(): void
    {
        $store = $this->createStore(onVacation: true);
        $product = $this->createProductForStore($store);

        $this->expectException(Exception::class);
        // Default notice interpolates the store name
        $this->expectExceptionMessage('Holiday Store');

        $this->hookProvider()->blockAddToCartForVacationStore($product);
    }

    public function test_add_to_cart_allowed_when_store_not_on_vacation(): void
    {
        $store = $this->createStore(onVacation: false);
        $product = $this->createProductForStore($store);

        // No exception expected
        $this->hookProvider()->blockAddToCartForVacationStore($product);

        $this->assertTrue(true);
    }

    // ---------------------------------------------------------------------
    // Checkout product validation filter (admin order creation)
    // ---------------------------------------------------------------------

    public function test_validate_stores_flags_error_when_a_store_is_on_vacation(): void
    {
        $store = $this->createStore(onVacation: true, message: 'On holiday');
        $stores = new EloquentCollection([$store]);

        $result = $this->hookProvider()->validateStoresNotOnVacation(
            ['isError' => false, 'message' => null],
            $stores
        );

        $this->assertTrue($result['isError']);
        $this->assertEquals('On holiday', $result['message']);
    }

    public function test_validate_stores_passes_when_no_store_on_vacation(): void
    {
        $store = $this->createStore(onVacation: false);
        $stores = new EloquentCollection([$store]);

        $input = ['isError' => false, 'message' => null];
        $result = $this->hookProvider()->validateStoresNotOnVacation($input, $stores);

        $this->assertFalse($result['isError']);
    }

    public function test_validate_stores_respects_prior_error(): void
    {
        $store = $this->createStore(onVacation: true, message: 'On holiday');
        $stores = new EloquentCollection([$store]);

        // An earlier validator already raised an error; it must be preserved unchanged.
        $input = ['isError' => true, 'message' => 'Products are from different vendors'];
        $result = $this->hookProvider()->validateStoresNotOnVacation($input, $stores);

        $this->assertTrue($result['isError']);
        $this->assertEquals('Products are from different vendors', $result['message']);
    }

    // ---------------------------------------------------------------------
    // Product detail notice
    // ---------------------------------------------------------------------

    public function test_product_detail_notice_appended_when_on_vacation(): void
    {
        $store = $this->createStore(onVacation: true, message: 'We are away until Monday');
        $product = $this->createProductForStore($store);

        $html = $this->hookProvider()->addVacationNoticeToProductDetail('<div>original</div>', $product);

        $this->assertStringContainsString('original', $html);
        $this->assertStringContainsString('We are away until Monday', $html);
        $this->assertGreaterThan(strlen('<div>original</div>'), strlen($html));
    }

    public function test_product_detail_notice_not_appended_when_not_on_vacation(): void
    {
        $store = $this->createStore(onVacation: false);
        $product = $this->createProductForStore($store);

        $html = $this->hookProvider()->addVacationNoticeToProductDetail('<div>original</div>', $product);

        $this->assertEquals('<div>original</div>', $html);
    }

    // ---------------------------------------------------------------------
    // Frontend checkout guard (processPostCheckoutOrder)
    // ---------------------------------------------------------------------

    public function test_checkout_is_blocked_when_cart_store_is_on_vacation(): void
    {
        $store = $this->createStore(onVacation: true, message: 'Closed for holidays');
        $product = $this->createProductForStore($store);

        $provider = new OrderSupportServiceProvider($this->app);

        $response = $provider->processPostCheckoutOrder(
            new EloquentCollection([$product]),
            new Request(),
            'test-token',
            [],
            new BaseHttpResponse()
        );

        $this->assertInstanceOf(BaseHttpResponse::class, $response);
        $this->assertTrue($response->isError());
        $this->assertEquals('Closed for holidays', $response->getMessage());
    }
}
