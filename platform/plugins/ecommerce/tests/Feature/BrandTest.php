<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_brand(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Test Brand',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ec_brands', [
            'name' => 'Test Brand',
        ]);
    }

    public function test_can_update_brand(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Test Brand',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brand->update(['name' => 'Updated Brand']);

        $this->assertDatabaseHas('ec_brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand',
        ]);
    }

    public function test_can_delete_brand(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Test Brand',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brandId = $brand->id;
        $brand->delete();

        $this->assertDatabaseMissing('ec_brands', [
            'id' => $brandId,
        ]);
    }

    public function test_brand_status_published(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Published Brand',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(BaseStatusEnum::PUBLISHED, $brand->status);
    }

    public function test_brand_status_pending(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Pending Brand',
            'status' => BaseStatusEnum::PENDING,
        ]);

        $this->assertEquals(BaseStatusEnum::PENDING, $brand->status);
    }

    public function test_brand_status_draft(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Draft Brand',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $this->assertEquals(BaseStatusEnum::DRAFT, $brand->status);
    }

    public function test_brand_with_website(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Brand with Website',
            'website' => 'https://www.example.com',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('https://www.example.com', $brand->website);
    }

    public function test_brand_with_description(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Descriptive Brand',
            'description' => 'This is a test brand description.',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('This is a test brand description.', $brand->description);
    }

    public function test_brand_with_logo(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Brand with Logo',
            'logo' => 'brands/logo.png',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('brands/logo.png', $brand->logo);
    }

    public function test_brand_is_featured(): void
    {
        $featuredBrand = Brand::query()->create([
            'name' => 'Featured Brand',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $normalBrand = Brand::query()->create([
            'name' => 'Normal Brand',
            'is_featured' => false,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($featuredBrand->is_featured);
        $this->assertFalse($normalBrand->is_featured);
    }

    public function test_brand_with_order(): void
    {
        $brand1 = Brand::query()->create([
            'name' => 'Brand 1',
            'order' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brand2 = Brand::query()->create([
            'name' => 'Brand 2',
            'order' => 2,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brand3 = Brand::query()->create([
            'name' => 'Brand 3',
            'order' => 3,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brands = Brand::query()->orderBy('order', 'asc')->get();

        $this->assertEquals('Brand 1', $brands[0]->name);
        $this->assertEquals('Brand 2', $brands[1]->name);
        $this->assertEquals('Brand 3', $brands[2]->name);
    }

    public function test_can_filter_brands_by_status(): void
    {
        Brand::query()->create([
            'name' => 'Published Brand',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Brand::query()->create([
            'name' => 'Pending Brand',
            'status' => BaseStatusEnum::PENDING,
        ]);

        Brand::query()->create([
            'name' => 'Draft Brand',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $publishedBrands = Brand::query()->where('status', BaseStatusEnum::PUBLISHED)->get();
        $pendingBrands = Brand::query()->where('status', BaseStatusEnum::PENDING)->get();
        $draftBrands = Brand::query()->where('status', BaseStatusEnum::DRAFT)->get();

        $this->assertCount(1, $publishedBrands);
        $this->assertCount(1, $pendingBrands);
        $this->assertCount(1, $draftBrands);
    }

    public function test_can_filter_featured_brands(): void
    {
        Brand::query()->create([
            'name' => 'Featured Brand 1',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Brand::query()->create([
            'name' => 'Featured Brand 2',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Brand::query()->create([
            'name' => 'Normal Brand',
            'is_featured' => false,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $featuredBrands = Brand::query()->where('is_featured', true)->get();
        $normalBrands = Brand::query()->where('is_featured', false)->get();

        $this->assertCount(2, $featuredBrands);
        $this->assertCount(1, $normalBrands);
    }

    public function test_can_search_brand_by_name(): void
    {
        Brand::query()->create([
            'name' => 'Apple',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Brand::query()->create([
            'name' => 'Samsung',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Brand::query()->create([
            'name' => 'Sony',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $brands = Brand::query()
            ->where('name', 'like', '%Sam%')
            ->get();

        $this->assertCount(1, $brands);
        $this->assertEquals('Samsung', $brands->first()->name);
    }

    public function test_multiple_brands(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Brand::query()->create([
                'name' => "Brand {$i}",
                'status' => BaseStatusEnum::PUBLISHED,
            ]);
        }

        $brands = Brand::query()->get();

        $this->assertCount(10, $brands);
    }

    public function test_brand_order_is_integer(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Test Brand',
            'order' => 5,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertIsInt($brand->order);
        $this->assertEquals(5, $brand->order);
    }

    public function test_brand_is_featured_is_boolean(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Test Brand',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertIsBool($brand->is_featured);
    }
}
