<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\DiscountTargetEnum;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Enums\DiscountTypeOptionEnum;
use Botble\Ecommerce\Http\Controllers\DiscountController;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscountControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected DiscountController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = app(DiscountController::class);
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

    protected function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => false,
        ], $attributes));
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        return Customer::query()->create(array_merge([
            'name' => 'Test Customer',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    // ========================================
    // DISCOUNT MODEL: CATEGORY RELATIONSHIP
    // ========================================

    public function test_discount_can_attach_single_category(): void
    {
        $category = $this->createCategory(['name' => 'Electronics']);

        $discount = Discount::query()->create([
            'code' => 'CATEGORY1',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCategories()->attach($category->id);

        $this->assertCount(1, $discount->fresh()->productCategories);
        $this->assertEquals($category->id, $discount->productCategories->first()->id);
    }

    public function test_discount_can_attach_multiple_categories_as_array(): void
    {
        $category1 = $this->createCategory(['name' => 'Electronics']);
        $category2 = $this->createCategory(['name' => 'Clothing']);
        $category3 = $this->createCategory(['name' => 'Books']);

        $discount = Discount::query()->create([
            'code' => 'MULTICATEGORY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $categoryIds = [$category1->id, $category2->id, $category3->id];
        $discount->productCategories()->attach($categoryIds);

        $this->assertCount(3, $discount->fresh()->productCategories);

        $attachedIds = $discount->productCategories->pluck('id')->toArray();
        $this->assertContains($category1->id, $attachedIds);
        $this->assertContains($category2->id, $attachedIds);
        $this->assertContains($category3->id, $attachedIds);
    }

    public function test_discount_can_sync_categories(): void
    {
        $category1 = $this->createCategory(['name' => 'Category 1']);
        $category2 = $this->createCategory(['name' => 'Category 2']);
        $category3 = $this->createCategory(['name' => 'Category 3']);

        $discount = Discount::query()->create([
            'code' => 'SYNCCATEGORY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCategories()->attach([$category1->id, $category2->id]);
        $this->assertCount(2, $discount->fresh()->productCategories);

        $discount->productCategories()->sync([$category2->id, $category3->id]);
        $this->assertCount(2, $discount->fresh()->productCategories);

        $syncedIds = $discount->fresh()->productCategories->pluck('id')->toArray();
        $this->assertNotContains($category1->id, $syncedIds);
        $this->assertContains($category2->id, $syncedIds);
        $this->assertContains($category3->id, $syncedIds);
    }

    public function test_discount_can_detach_categories(): void
    {
        $category1 = $this->createCategory(['name' => 'Category 1']);
        $category2 = $this->createCategory(['name' => 'Category 2']);

        $discount = Discount::query()->create([
            'code' => 'DETACHCATEGORY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCategories()->attach([$category1->id, $category2->id]);
        $this->assertCount(2, $discount->fresh()->productCategories);

        $discount->productCategories()->detach();
        $this->assertCount(0, $discount->fresh()->productCategories);
    }

    // ========================================
    // DISCOUNT MODEL: COLLECTION RELATIONSHIP
    // ========================================

    public function test_discount_can_attach_single_collection(): void
    {
        $collection = $this->createCollection(['name' => 'Summer Sale']);

        $discount = Discount::query()->create([
            'code' => 'COLLECTION1',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCollections()->attach($collection->id);

        $this->assertCount(1, $discount->fresh()->productCollections);
        $this->assertEquals($collection->id, $discount->productCollections->first()->id);
    }

    public function test_discount_can_attach_multiple_collections_as_array(): void
    {
        $collection1 = $this->createCollection(['name' => 'Summer Sale']);
        $collection2 = $this->createCollection(['name' => 'Winter Sale']);
        $collection3 = $this->createCollection(['name' => 'Featured']);

        $discount = Discount::query()->create([
            'code' => 'MULTICOLLECTION',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $collectionIds = [$collection1->id, $collection2->id, $collection3->id];
        $discount->productCollections()->attach($collectionIds);

        $this->assertCount(3, $discount->fresh()->productCollections);

        $attachedIds = $discount->productCollections->pluck('id')->toArray();
        $this->assertContains($collection1->id, $attachedIds);
        $this->assertContains($collection2->id, $attachedIds);
        $this->assertContains($collection3->id, $attachedIds);
    }

    public function test_discount_can_sync_collections(): void
    {
        $collection1 = $this->createCollection(['name' => 'Collection 1']);
        $collection2 = $this->createCollection(['name' => 'Collection 2']);
        $collection3 = $this->createCollection(['name' => 'Collection 3']);

        $discount = Discount::query()->create([
            'code' => 'SYNCCOLLECTION',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCollections()->attach([$collection1->id, $collection2->id]);
        $this->assertCount(2, $discount->fresh()->productCollections);

        $discount->productCollections()->sync([$collection2->id, $collection3->id]);
        $this->assertCount(2, $discount->fresh()->productCollections);

        $syncedIds = $discount->fresh()->productCollections->pluck('id')->toArray();
        $this->assertNotContains($collection1->id, $syncedIds);
        $this->assertContains($collection2->id, $syncedIds);
        $this->assertContains($collection3->id, $syncedIds);
    }

    public function test_discount_can_detach_collections(): void
    {
        $collection1 = $this->createCollection(['name' => 'Collection 1']);
        $collection2 = $this->createCollection(['name' => 'Collection 2']);

        $discount = Discount::query()->create([
            'code' => 'DETACHCOLLECTION',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCollections()->attach([$collection1->id, $collection2->id]);
        $this->assertCount(2, $discount->fresh()->productCollections);

        $discount->productCollections()->detach();
        $this->assertCount(0, $discount->fresh()->productCollections);
    }

    // ========================================
    // DISCOUNT MODEL: PRODUCT RELATIONSHIP
    // ========================================

    public function test_discount_can_attach_products(): void
    {
        $product1 = $this->createProduct(['name' => 'Product 1']);
        $product2 = $this->createProduct(['name' => 'Product 2']);

        $discount = Discount::query()->create([
            'code' => 'PRODUCTS',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->products()->attach([$product1->id, $product2->id]);

        $this->assertCount(2, $discount->fresh()->products);
    }

    public function test_discount_can_sync_products(): void
    {
        $product1 = $this->createProduct(['name' => 'Product 1']);
        $product2 = $this->createProduct(['name' => 'Product 2']);
        $product3 = $this->createProduct(['name' => 'Product 3']);

        $discount = Discount::query()->create([
            'code' => 'SYNCPRODUCTS',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->products()->attach([$product1->id, $product2->id]);
        $this->assertCount(2, $discount->fresh()->products);

        $discount->products()->sync([$product3->id]);
        $this->assertCount(1, $discount->fresh()->products);
        $this->assertEquals($product3->id, $discount->fresh()->products->first()->id);
    }

    // ========================================
    // DISCOUNT MODEL: CUSTOMER RELATIONSHIP
    // ========================================

    public function test_discount_can_attach_customers(): void
    {
        $customer1 = $this->createCustomer(['name' => 'Customer 1']);
        $customer2 = $this->createCustomer(['name' => 'Customer 2']);

        $discount = Discount::query()->create([
            'code' => 'CUSTOMERS',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::CUSTOMER,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->customers()->attach([$customer1->id, $customer2->id]);

        $this->assertCount(2, $discount->fresh()->customers);
    }

    // ========================================
    // DISCOUNT: SCOPES
    // ========================================

    public function test_discount_active_scope(): void
    {
        $activeDiscount = Discount::query()->create([
            'code' => 'ACTIVE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $expiredDiscount = Discount::query()->create([
            'code' => 'EXPIRED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subWeek(),
            'end_date' => now()->subDay(),
        ]);

        $futureDiscount = Discount::query()->create([
            'code' => 'FUTURE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->addDay(),
            'end_date' => now()->addWeek(),
        ]);

        $activeDiscounts = Discount::query()->active()->get();

        $this->assertCount(1, $activeDiscounts);
        $this->assertEquals('ACTIVE', $activeDiscounts->first()->code);
    }

    public function test_discount_available_scope(): void
    {
        $availableDiscount = Discount::query()->create([
            'code' => 'AVAILABLE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => 100,
            'total_used' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $exhaustedDiscount = Discount::query()->create([
            'code' => 'EXHAUSTED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => 100,
            'total_used' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $unlimitedDiscount = Discount::query()->create([
            'code' => 'UNLIMITED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => null,
            'total_used' => 1000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $availableDiscounts = Discount::query()->available()->get();

        $this->assertCount(2, $availableDiscounts);
        $codes = $availableDiscounts->pluck('code')->toArray();
        $this->assertContains('AVAILABLE', $codes);
        $this->assertContains('UNLIMITED', $codes);
        $this->assertNotContains('EXHAUSTED', $codes);
    }

    // ========================================
    // DISCOUNT: ATTRIBUTES
    // ========================================

    public function test_discount_left_quantity_attribute(): void
    {
        $discount = Discount::query()->create([
            'code' => 'LIMITED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'quantity' => 100,
            'total_used' => 25,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(75, $discount->left_quantity);
    }

    public function test_discount_is_expired_method(): void
    {
        $expiredDiscount = Discount::query()->create([
            'code' => 'EXPIRED',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subWeek(),
            'end_date' => now()->subDay(),
        ]);

        $activeDiscount = Discount::query()->create([
            'code' => 'ACTIVE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($expiredDiscount->isExpired());
        $this->assertFalse($activeDiscount->isExpired());
    }

    // ========================================
    // DISCOUNT: DELETION CASCADE
    // ========================================

    public function test_discount_deletion_detaches_all_relationships(): void
    {
        $category = $this->createCategory();
        $collection = $this->createCollection();
        $product = $this->createProduct();
        $customer = $this->createCustomer();

        $discount = Discount::query()->create([
            'code' => 'DELETEME',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $discount->productCategories()->attach($category->id);
        $discount->productCollections()->attach($collection->id);
        $discount->products()->attach($product->id);
        $discount->customers()->attach($customer->id);

        $discountId = $discount->id;

        $this->assertDatabaseHas('ec_discount_product_categories', ['discount_id' => $discountId]);
        $this->assertDatabaseHas('ec_discount_product_collections', ['discount_id' => $discountId]);
        $this->assertDatabaseHas('ec_discount_products', ['discount_id' => $discountId]);
        $this->assertDatabaseHas('ec_discount_customers', ['discount_id' => $discountId]);

        $discount->delete();

        $this->assertDatabaseMissing('ec_discount_product_categories', ['discount_id' => $discountId]);
        $this->assertDatabaseMissing('ec_discount_product_collections', ['discount_id' => $discountId]);
        $this->assertDatabaseMissing('ec_discount_products', ['discount_id' => $discountId]);
        $this->assertDatabaseMissing('ec_discount_customers', ['discount_id' => $discountId]);
    }

    // ========================================
    // DISCOUNT: ALL TYPE OPTIONS
    // ========================================

    public function test_discount_type_option_amount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'AMOUNT',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'value' => 50,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeOptionEnum::AMOUNT, $discount->type_option);
        $this->assertEquals(50, $discount->value);
    }

    public function test_discount_type_option_percentage(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PERCENTAGE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 25,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeOptionEnum::PERCENTAGE, $discount->type_option);
        $this->assertEquals(25, $discount->value);
    }

    public function test_discount_type_option_shipping(): void
    {
        $discount = Discount::query()->create([
            'code' => 'SHIPPING',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SHIPPING,
            'value' => 100,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeOptionEnum::SHIPPING, $discount->type_option);
    }

    public function test_discount_type_option_same_price(): void
    {
        $discount = Discount::query()->create([
            'code' => 'SAMEPRICE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::SAME_PRICE,
            'value' => 99,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeOptionEnum::SAME_PRICE, $discount->type_option);
        $this->assertEquals(99, $discount->value);
    }

    // ========================================
    // DISCOUNT: ALL TARGET OPTIONS
    // ========================================

    public function test_discount_target_all_orders(): void
    {
        $discount = Discount::query()->create([
            'code' => 'ALLORDERS',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ALL_ORDERS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::ALL_ORDERS, $discount->target);
    }

    public function test_discount_target_customer(): void
    {
        $discount = Discount::query()->create([
            'code' => 'CUSTOMERONLY',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::CUSTOMER,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::CUSTOMER, $discount->target);
    }

    public function test_discount_target_minimum_order_amount(): void
    {
        $discount = Discount::query()->create([
            'code' => 'MINORDER',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::MINIMUM_ORDER_AMOUNT,
            'min_order_price' => 100,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::MINIMUM_ORDER_AMOUNT, $discount->target);
        $this->assertEquals(100, $discount->min_order_price);
    }

    public function test_discount_target_once_per_customer(): void
    {
        $discount = Discount::query()->create([
            'code' => 'ONCEPERCUSTOMER',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::ONCE_PER_CUSTOMER,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::ONCE_PER_CUSTOMER, $discount->target);
    }

    public function test_discount_target_specific_product(): void
    {
        $discount = Discount::query()->create([
            'code' => 'SPECIFICPRODUCT',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::SPECIFIC_PRODUCT, $discount->target);
    }

    public function test_discount_target_product_variant(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PRODUCTVARIANT',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_VARIANT,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::PRODUCT_VARIANT, $discount->target);
    }

    public function test_discount_target_product_collections(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PRODUCTCOLLECTIONS',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_COLLECTIONS,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::PRODUCT_COLLECTIONS, $discount->target);
    }

    public function test_discount_target_product_categories(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PRODUCTCATEGORIES',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'target' => DiscountTargetEnum::PRODUCT_CATEGORIES,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTargetEnum::PRODUCT_CATEGORIES, $discount->target);
    }

    // ========================================
    // DISCOUNT TYPE: COUPON VS PROMOTION
    // ========================================

    public function test_discount_type_coupon(): void
    {
        $discount = Discount::query()->create([
            'code' => 'COUPON123',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeEnum::COUPON, $discount->type);
    }

    public function test_discount_type_promotion(): void
    {
        $discount = Discount::query()->create([
            'title' => 'Summer Promotion',
            'type' => DiscountTypeEnum::PROMOTION,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 20,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals(DiscountTypeEnum::PROMOTION, $discount->type);
    }

    // ========================================
    // DISCOUNT: ADDITIONAL OPTIONS
    // ========================================

    public function test_discount_can_use_with_promotion(): void
    {
        $discount = Discount::query()->create([
            'code' => 'WITHPROMO',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'can_use_with_promotion' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($discount->can_use_with_promotion);
    }

    public function test_discount_cannot_use_with_promotion(): void
    {
        $discount = Discount::query()->create([
            'code' => 'NOPROMO',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'can_use_with_promotion' => false,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertFalse($discount->can_use_with_promotion);
    }

    public function test_discount_can_use_with_flash_sale(): void
    {
        $discount = Discount::query()->create([
            'code' => 'WITHFLASH',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'can_use_with_flash_sale' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($discount->can_use_with_flash_sale);
    }

    public function test_discount_display_at_checkout(): void
    {
        $discount = Discount::query()->create([
            'code' => 'SHOWATCHECKOUT',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'display_at_checkout' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($discount->display_at_checkout);
    }

    public function test_discount_apply_via_url(): void
    {
        $discount = Discount::query()->create([
            'code' => 'URLCODE',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::PERCENTAGE,
            'value' => 10,
            'apply_via_url' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertTrue($discount->apply_via_url);
    }

    // ========================================
    // DISCOUNT: DISCOUNT_ON OPTIONS
    // ========================================

    public function test_discount_on_per_order(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PERORDER',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 10,
            'discount_on' => 'per-order',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals('per-order', $discount->discount_on);
    }

    public function test_discount_on_per_every_item(): void
    {
        $discount = Discount::query()->create([
            'code' => 'PEREVERYITEM',
            'type' => DiscountTypeEnum::COUPON,
            'type_option' => DiscountTypeOptionEnum::AMOUNT,
            'target' => DiscountTargetEnum::SPECIFIC_PRODUCT,
            'value' => 5,
            'discount_on' => 'per-every-item',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        $this->assertEquals('per-every-item', $discount->discount_on);
    }
}
