<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Services\HandleApplyCouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscountCouponServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HandleApplyCouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
        $this->couponService = app(HandleApplyCouponService::class);
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();
        session()->forget(['applied_coupon_code', 'auto_apply_coupon_code']);

        parent::tearDown();
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => false,
        ], $attributes));
    }

    protected function createCategory(array $attributes = []): ProductCategory
    {
        return ProductCategory::query()->create(array_merge([
            'name' => 'Test Category',
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    protected function createCollection(array $attributes = []): ProductCollection
    {
        return ProductCollection::query()->create(array_merge([
            'name' => 'Test Collection',
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        return Customer::query()->create(array_merge([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    protected function addProductToCart(Product $product, int $qty = 1): void
    {
        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            $qty,
            $product->price,
            ['product' => $product]
        );
    }

    // ========================================
    // COUPON: ALL ORDERS TARGET
    // ========================================

    public function test_coupon_fixed_amount_all_orders(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'FIXED10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('FIXED10');

        $this->assertFalse($result['error']);
        $this->assertEquals(10, $result['data']['discount_amount']);
    }

    public function test_coupon_percentage_all_orders(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'PERCENT20',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('PERCENT20');

        $this->assertFalse($result['error']);
        $this->assertEquals(40, $result['data']['discount_amount']);
    }

    public function test_coupon_free_shipping_all_orders(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'FREESHIP',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SHIPPING,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('FREESHIP', ['shipping_amount' => 10]);

        $this->assertFalse($result['error']);
        $this->assertTrue($result['data']['is_free_shipping']);
    }

    public function test_coupon_free_shipping_above_minimum_order_meets_threshold(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        Discount::query()->create([
            'code' => 'FREESHIP150',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SHIPPING,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 100,
            'min_order_price' => 150,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('FREESHIP150', ['shipping_amount' => 10]);

        $this->assertFalse($result['error']);
        $this->assertTrue($result['data']['is_free_shipping']);
    }

    public function test_coupon_free_shipping_above_minimum_order_below_threshold(): void
    {
        $product = $this->createProduct(['price' => 50]);
        $this->addProductToCart($product, 1);

        Discount::query()->create([
            'code' => 'FREESHIP200',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SHIPPING,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 100,
            'min_order_price' => 200,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('FREESHIP200', ['shipping_amount' => 10]);

        $this->assertTrue($result['error']);
        $this->assertEquals('MINIMUM_ORDER_AMOUNT_NOT_MET', $result['error_code']);
    }

    // ========================================
    // COUPON: MINIMUM ORDER AMOUNT TARGET
    // ========================================

    public function test_coupon_minimum_order_amount_meets_threshold(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'MIN150',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 20,
            'min_order_price' => 150,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('MIN150');

        $this->assertFalse($result['error']);
        $this->assertEquals(20, $result['data']['discount_amount']);
    }

    public function test_coupon_minimum_order_amount_below_threshold(): void
    {
        $product = $this->createProduct(['price' => 50]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'MIN100',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'value' => 10,
            'min_order_price' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('MIN100');

        $this->assertTrue($result['error']);
        $this->assertEquals('MINIMUM_ORDER_AMOUNT_NOT_MET', $result['error_code']);
    }

    // ========================================
    // COUPON: PRODUCT CATEGORY TARGET
    // ========================================

    public function test_coupon_category_fixed_amount(): void
    {
        $category = $this->createCategory(['name' => 'Electronics']);
        $product = $this->createProduct(['name' => 'Laptop', 'price' => 1000]);
        $product->categories()->attach($category->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'ELECTRONICS10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 100,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category->id);

        $result = $this->couponService->execute('ELECTRONICS10');

        $this->assertFalse($result['error']);
        $this->assertEquals(100, $result['data']['discount_amount']);
    }

    public function test_coupon_category_percentage(): void
    {
        $category = $this->createCategory(['name' => 'Clothing']);
        $product = $this->createProduct(['name' => 'T-Shirt', 'price' => 50]);
        $product->categories()->attach($category->id);

        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'CLOTHING20',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category->id);

        $result = $this->couponService->execute('CLOTHING20');

        $this->assertFalse($result['error']);
        $this->assertEquals(20, $result['data']['discount_amount']);
    }

    public function test_coupon_category_with_array_attachment(): void
    {
        $category1 = $this->createCategory(['name' => 'Category 1']);
        $category2 = $this->createCategory(['name' => 'Category 2']);

        $product = $this->createProduct(['name' => 'Product', 'price' => 100]);
        $product->categories()->attach($category1->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'MULTICATEGORY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach([$category1->id, $category2->id]);

        $result = $this->couponService->execute('MULTICATEGORY');

        $this->assertFalse($result['error']);
        $this->assertEquals(15, $result['data']['discount_amount']);
    }

    public function test_coupon_category_product_not_in_category(): void
    {
        $category = $this->createCategory(['name' => 'Electronics']);
        $otherCategory = $this->createCategory(['name' => 'Clothing']);
        $product = $this->createProduct(['name' => 'T-Shirt', 'price' => 50]);
        $product->categories()->attach($otherCategory->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'ELECTRONICS10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category->id);

        $result = $this->couponService->execute('ELECTRONICS10');

        // The cart product belongs to a different category than the coupon targets,
        // so the coupon must be rejected rather than applying a silent $0 discount.
        $this->assertTrue($result['error']);
        $this->assertEquals('COUPON_NOT_APPLICABLE', $result['error_code']);
    }

    public function test_coupon_category_per_every_item_discount(): void
    {
        $category = $this->createCategory(['name' => 'Books']);
        $product1 = $this->createProduct(['name' => 'Book 1', 'price' => 30]);
        $product2 = $this->createProduct(['name' => 'Book 2', 'price' => 40]);
        $product1->categories()->attach($category->id);
        $product2->categories()->attach($category->id);

        $this->addProductToCart($product1, 2);
        $this->addProductToCart($product2, 1);

        $discount = Discount::query()->create([
            'code' => 'BOOKS5',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 5,
            'discount_on' => 'per-every-item',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category->id);

        $result = $this->couponService->execute('BOOKS5');

        $this->assertFalse($result['error']);
        $this->assertEquals(15, $result['data']['discount_amount']);
    }

    // ========================================
    // COUPON: PRODUCT COLLECTION TARGET
    // ========================================

    public function test_coupon_collection_fixed_amount(): void
    {
        $collection = $this->createCollection(['name' => 'Summer Sale']);
        $product = $this->createProduct(['name' => 'Summer Dress', 'price' => 80]);
        $product->productCollections()->attach($collection->id);

        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'SUMMER15',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 15,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCollections()->attach($collection->id);

        $result = $this->couponService->execute('SUMMER15');

        $this->assertFalse($result['error']);
        $this->assertEquals(15, $result['data']['discount_amount']);
    }

    public function test_coupon_collection_percentage(): void
    {
        $collection = $this->createCollection(['name' => 'Featured']);
        $product = $this->createProduct(['name' => 'Featured Item', 'price' => 200]);
        $product->productCollections()->attach($collection->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'FEATURED25',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 25,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCollections()->attach($collection->id);

        $result = $this->couponService->execute('FEATURED25');

        $this->assertFalse($result['error']);
        $this->assertEquals(50, $result['data']['discount_amount']);
    }

    public function test_coupon_collection_with_array_attachment(): void
    {
        $collection1 = $this->createCollection(['name' => 'Collection 1']);
        $collection2 = $this->createCollection(['name' => 'Collection 2']);

        $product = $this->createProduct(['name' => 'Product', 'price' => 100]);
        $product->productCollections()->attach($collection1->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'MULTICOLLECTION',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 10,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCollections()->attach([$collection1->id, $collection2->id]);

        $result = $this->couponService->execute('MULTICOLLECTION');

        $this->assertFalse($result['error']);
        $this->assertEquals(10, $result['data']['discount_amount']);
    }

    // ========================================
    // COUPON: SPECIFIC PRODUCT TARGET
    // ========================================

    public function test_coupon_specific_product_fixed_amount(): void
    {
        $product = $this->createProduct(['name' => 'Special Product', 'price' => 150]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'SPECIAL20',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 20,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->products()->attach($product->id);

        $result = $this->couponService->execute('SPECIAL20');

        $this->assertFalse($result['error']);
        $this->assertEquals(20, $result['data']['discount_amount']);
    }

    public function test_coupon_specific_product_percentage(): void
    {
        $product = $this->createProduct(['name' => 'VIP Product', 'price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'VIP30',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 30,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->products()->attach($product->id);

        $result = $this->couponService->execute('VIP30');

        $this->assertFalse($result['error']);
        $this->assertEquals(30, $result['data']['discount_amount']);
    }

    public function test_coupon_specific_product_percentage_scales_with_quantity(): void
    {
        // The discount on a specific-product percentage coupon must track the line
        // total, so increasing the quantity scales the discount accordingly. This
        // guards the calculation that the checkout summary recomputes on each cart
        // change (instead of reusing the amount stored at apply time).
        $product = $this->createProduct(['name' => 'VIP Product', 'price' => 100]);
        $this->addProductToCart($product, 3);

        $discount = Discount::query()->create([
            'code' => 'VIP30QTY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 30,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->products()->attach($product->id);

        $result = $this->couponService->execute('VIP30QTY');

        $this->assertFalse($result['error']);
        // 30% of (100 x 3) = 90, not the single-unit 30.
        $this->assertEquals(90, $result['data']['discount_amount']);
    }

    public function test_coupon_specific_product_not_in_cart(): void
    {
        $product1 = $this->createProduct(['name' => 'Product 1', 'price' => 100]);
        $product2 = $this->createProduct(['name' => 'Product 2', 'price' => 80]);

        $this->addProductToCart($product2, 1);

        $discount = Discount::query()->create([
            'code' => 'PRODUCT1ONLY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 10,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->products()->attach($product1->id);

        $result = $this->couponService->execute('PRODUCT1ONLY');

        // A coupon restricted to a specific product must be rejected when that
        // product is not in the cart, instead of silently applying a $0 discount.
        $this->assertTrue($result['error']);
        $this->assertEquals('COUPON_NOT_APPLICABLE', $result['error_code']);
    }

    // ========================================
    // COUPON: SAME PRICE TYPE
    // ========================================

    public function test_coupon_same_price_specific_product(): void
    {
        $product = $this->createProduct(['name' => 'Priced Product', 'price' => 100]);
        $this->addProductToCart($product, 2);

        $discount = Discount::query()->create([
            'code' => 'SAMEPRICE50',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SAME_PRICE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->products()->attach($product->id);

        $result = $this->couponService->execute('SAMEPRICE50');

        $this->assertFalse($result['error']);
    }

    public function test_coupon_same_price_category(): void
    {
        $category = $this->createCategory(['name' => 'Deals']);
        $product = $this->createProduct(['name' => 'Deal Product', 'price' => 80]);
        $product->categories()->attach($category->id);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'DEALS40',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SAME_PRICE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 40,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category->id);

        $result = $this->couponService->execute('DEALS40');

        $this->assertFalse($result['error']);
    }

    // ========================================
    // COUPON: CUSTOMER-SPECIFIC TARGET
    // ========================================

    public function test_coupon_customer_specific_not_logged_in(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $customer = $this->createCustomer();

        $discount = Discount::query()->create([
            'code' => 'CUSTOMER10',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::CUSTOMER,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->customers()->attach($customer->id);

        $result = $this->couponService->execute('CUSTOMER10');

        $this->assertTrue($result['error']);
        $this->assertEquals('LOGIN_REQUIRED', $result['error_code']);
    }

    // ========================================
    // COUPON: ONCE PER CUSTOMER TARGET
    // ========================================

    public function test_coupon_once_per_customer_not_logged_in(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'ONCEONLY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ONCE_PER_CUSTOMER,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('ONCEONLY');

        $this->assertTrue($result['error']);
        $this->assertEquals('LOGIN_REQUIRED', $result['error_code']);
    }

    // ========================================
    // COUPON: VALIDATION ERRORS
    // ========================================

    public function test_coupon_invalid_code(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $result = $this->couponService->execute('INVALIDCODE');

        $this->assertTrue($result['error']);
        $this->assertEquals('INVALID_COUPON', $result['error_code']);
    }

    public function test_coupon_expired(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'EXPIRED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subWeek(),
            'end_date' => now()->subDay(),
        ]);

        $result = $this->couponService->execute('EXPIRED');

        $this->assertTrue($result['error']);
        $this->assertEquals('INVALID_COUPON', $result['error_code']);
    }

    public function test_coupon_not_started_yet(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'FUTURE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->addDay(),
            'end_date' => now()->addWeek(),
        ]);

        $result = $this->couponService->execute('FUTURE');

        $this->assertTrue($result['error']);
        $this->assertEquals('INVALID_COUPON', $result['error_code']);
    }

    public function test_coupon_quantity_exhausted(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'EXHAUSTED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'quantity' => 100,
            'total_used' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('EXHAUSTED');

        $this->assertTrue($result['error']);
        $this->assertEquals('INVALID_COUPON', $result['error_code']);
    }

    public function test_coupon_cannot_use_with_promotion(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'NOPROMO',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'can_use_with_promotion' => false,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('NOPROMO', ['promotion_discount_amount' => 50]);

        $this->assertTrue($result['error']);
        $this->assertEquals('CANNOT_USE_WITH_PROMOTION', $result['error_code']);
    }

    // ========================================
    // COUPON: DISCOUNT AMOUNT LIMITS
    // ========================================

    public function test_coupon_discount_amount_cannot_exceed_cart_total(): void
    {
        $product = $this->createProduct(['price' => 50]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'BIG100',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('BIG100');

        $this->assertFalse($result['error']);
        $this->assertEquals(50, $result['data']['discount_amount']);
    }

    // ========================================
    // COUPON: MIXED CART SCENARIOS
    // ========================================

    public function test_coupon_category_only_applies_to_matching_products(): void
    {
        $electronicsCategory = $this->createCategory(['name' => 'Electronics']);
        $clothingCategory = $this->createCategory(['name' => 'Clothing']);

        $laptop = $this->createProduct(['name' => 'Laptop', 'price' => 1000]);
        $laptop->categories()->attach($electronicsCategory->id);

        $tshirt = $this->createProduct(['name' => 'T-Shirt', 'price' => 30]);
        $tshirt->categories()->attach($clothingCategory->id);

        $this->addProductToCart($laptop, 1);
        $this->addProductToCart($tshirt, 1);

        $discount = Discount::query()->create([
            'code' => 'ELECTRONICS10PERCENT',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($electronicsCategory->id);

        $result = $this->couponService->execute('ELECTRONICS10PERCENT');

        $this->assertFalse($result['error']);
        $this->assertEquals(100, $result['data']['discount_amount']);
    }

    public function test_coupon_applies_to_products_in_multiple_categories(): void
    {
        $category1 = $this->createCategory(['name' => 'Category 1']);
        $category2 = $this->createCategory(['name' => 'Category 2']);

        $product = $this->createProduct(['name' => 'Multi-Category Product', 'price' => 100]);
        $product->categories()->attach([$category1->id, $category2->id]);

        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'CAT1ONLY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $discount->productCategories()->attach($category1->id);

        $result = $this->couponService->execute('CAT1ONLY');

        $this->assertFalse($result['error']);
        $this->assertEquals(10, $result['data']['discount_amount']);
    }

    // ========================================
    // PROMOTION: TESTS
    // ========================================

    public function test_promotion_is_not_retrieved_as_coupon(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $promotion = Discount::query()->create([
            'code' => '',
            'type' => DiscountTypeEnum::PROMOTION,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('');

        $this->assertTrue($result['error']);
        $this->assertEquals('INVALID_COUPON', $result['error_code']);
    }

    // ========================================
    // COUPON: CASE SENSITIVITY
    // ========================================

    public function test_coupon_code_exact_match(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'TESTCODE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('TESTCODE');

        $this->assertFalse($result['error']);
        $this->assertEquals(10, $result['data']['discount_amount']);
    }

    public function test_coupon_code_whitespace_trimmed(): void
    {
        $product = $this->createProduct(['price' => 100]);
        $this->addProductToCart($product, 1);

        $discount = Discount::query()->create([
            'code' => 'TRIMCODE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $result = $this->couponService->execute('  TRIMCODE  ');

        $this->assertFalse($result['error']);
        $this->assertEquals(10, $result['data']['discount_amount']);
    }
}
