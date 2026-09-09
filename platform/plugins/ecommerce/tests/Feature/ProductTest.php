<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ec_products', [
            'name' => 'Test Product',
            'price' => 100,
        ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->update(['price' => 150]);

        $this->assertDatabaseHas('ec_products', [
            'id' => $product->id,
            'price' => 150,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productId = $product->id;
        $product->delete();

        $this->assertDatabaseMissing('ec_products', [
            'id' => $productId,
        ]);
    }

    public function test_product_has_sale_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'sale_price' => 80,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(80, $product->sale_price);
        $this->assertEquals(100, $product->price);
    }

    public function test_product_with_quantity(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'quantity' => 50,
            'with_storehouse_management' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(50, $product->quantity);
        $this->assertTrue($product->with_storehouse_management);
    }

    public function test_product_stock_quantity(): void
    {
        $inStockProduct = Product::query()->create([
            'name' => 'In Stock Product',
            'price' => 100,
            'quantity' => 10,
            'with_storehouse_management' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $outOfStockProduct = Product::query()->create([
            'name' => 'Out of Stock Product',
            'price' => 100,
            'quantity' => 0,
            'with_storehouse_management' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(10, $inStockProduct->quantity);
        $this->assertEquals(0, $outOfStockProduct->quantity);
    }

    public function test_can_create_product_with_sku(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('TEST-SKU-001', $product->sku);
    }

    public function test_can_filter_products_by_price_range(): void
    {
        Product::query()->create([
            'name' => 'Cheap Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Product::query()->create([
            'name' => 'Medium Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Product::query()->create([
            'name' => 'Expensive Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $products = Product::query()
            ->where('price', '>=', 75)
            ->where('price', '<=', 150)
            ->get();

        $this->assertCount(1, $products);
        $this->assertEquals('Medium Product', $products->first()->name);
    }

    public function test_product_weight(): void
    {
        $product = Product::query()->create([
            'name' => 'Heavy Product',
            'price' => 100,
            'weight' => 5.5,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(5.5, $product->weight);
    }

    public function test_product_dimensions(): void
    {
        $product = Product::query()->create([
            'name' => 'Box Product',
            'price' => 100,
            'length' => 10,
            'wide' => 20,
            'height' => 30,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(10, $product->length);
        $this->assertEquals(20, $product->wide);
        $this->assertEquals(30, $product->height);
    }

    public function test_product_is_featured(): void
    {
        $featuredProduct = Product::query()->create([
            'name' => 'Featured Product',
            'price' => 100,
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $normalProduct = Product::query()->create([
            'name' => 'Normal Product',
            'price' => 100,
            'is_featured' => false,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($featuredProduct->is_featured);
        $this->assertFalse($normalProduct->is_featured);
    }

    public function test_can_order_products_by_price(): void
    {
        Product::query()->create([
            'name' => 'Product A',
            'price' => 150,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Product::query()->create([
            'name' => 'Product B',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Product::query()->create([
            'name' => 'Product C',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productsAsc = Product::query()->orderBy('price', 'asc')->get();
        $productsDesc = Product::query()->orderBy('price', 'desc')->get();

        $this->assertEquals('Product B', $productsAsc->first()->name);
        $this->assertEquals('Product A', $productsDesc->first()->name);
    }

    public function test_product_barcode(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'barcode' => '1234567890123',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('1234567890123', $product->barcode);
    }

    public function test_product_cost_per_item(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'cost_per_item' => 60,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(60, $product->cost_per_item);
    }

    public function test_can_search_product_by_name(): void
    {
        Product::query()->create([
            'name' => 'Apple iPhone',
            'price' => 999,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Product::query()->create([
            'name' => 'Samsung Galaxy',
            'price' => 899,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $products = Product::query()
            ->where('name', 'like', '%iPhone%')
            ->get();

        $this->assertCount(1, $products);
        $this->assertEquals('Apple iPhone', $products->first()->name);
    }

    public function test_product_views_count(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'views' => 150,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(150, $product->views);
    }
}
