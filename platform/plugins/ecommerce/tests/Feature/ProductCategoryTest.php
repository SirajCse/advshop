<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCategoryTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_category(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ec_product_categories', [
            'name' => 'Electronics',
        ]);
    }

    public function test_can_update_category(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $category->update(['name' => 'Consumer Electronics']);

        $this->assertDatabaseHas('ec_product_categories', [
            'id' => $category->id,
            'name' => 'Consumer Electronics',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $categoryId = $category->id;
        $category->delete();

        $this->assertDatabaseMissing('ec_product_categories', [
            'id' => $categoryId,
        ]);
    }

    public function test_category_status_published(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Published Category',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(BaseStatusEnum::PUBLISHED, $category->status);
    }

    public function test_category_status_pending(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Pending Category',
            'status' => BaseStatusEnum::PENDING,
        ]);

        $this->assertEquals(BaseStatusEnum::PENDING, $category->status);
    }

    public function test_category_status_draft(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Draft Category',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $this->assertEquals(BaseStatusEnum::DRAFT, $category->status);
    }

    public function test_category_with_parent(): void
    {
        $parentCategory = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $childCategory = ProductCategory::query()->create([
            'name' => 'Smartphones',
            'parent_id' => $parentCategory->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals($parentCategory->id, $childCategory->parent_id);
        $this->assertEquals('Electronics', $childCategory->parent->name);
    }

    public function test_category_has_children(): void
    {
        $parentCategory = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Smartphones',
            'parent_id' => $parentCategory->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Laptops',
            'parent_id' => $parentCategory->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $children = $parentCategory->children;

        $this->assertCount(2, $children);
    }

    public function test_category_with_description(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'description' => 'All electronic products',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('All electronic products', $category->description);
    }

    public function test_category_with_image(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'image' => 'categories/electronics.jpg',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('categories/electronics.jpg', $category->image);
    }

    public function test_category_is_featured(): void
    {
        $featuredCategory = ProductCategory::query()->create([
            'name' => 'Featured Category',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $normalCategory = ProductCategory::query()->create([
            'name' => 'Normal Category',
            'is_featured' => false,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($featuredCategory->is_featured);
        $this->assertFalse($normalCategory->is_featured);
    }

    public function test_category_with_order(): void
    {
        $category1 = ProductCategory::query()->create([
            'name' => 'Category 1',
            'order' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $category2 = ProductCategory::query()->create([
            'name' => 'Category 2',
            'order' => 2,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $category3 = ProductCategory::query()->create([
            'name' => 'Category 3',
            'order' => 3,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $categories = ProductCategory::query()->orderBy('order', 'asc')->get();

        $this->assertEquals('Category 1', $categories[0]->name);
        $this->assertEquals('Category 2', $categories[1]->name);
        $this->assertEquals('Category 3', $categories[2]->name);
    }

    public function test_category_with_icon(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'icon' => 'ti ti-device-laptop',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('ti ti-device-laptop', $category->icon);
    }

    public function test_category_with_icon_image(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'icon_image' => 'categories/icons/electronics.png',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('categories/icons/electronics.png', $category->icon_image);
    }

    public function test_can_filter_categories_by_status(): void
    {
        ProductCategory::query()->create([
            'name' => 'Published Category',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Pending Category',
            'status' => BaseStatusEnum::PENDING,
        ]);

        ProductCategory::query()->create([
            'name' => 'Draft Category',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $publishedCategories = ProductCategory::query()->where('status', BaseStatusEnum::PUBLISHED)->get();
        $pendingCategories = ProductCategory::query()->where('status', BaseStatusEnum::PENDING)->get();
        $draftCategories = ProductCategory::query()->where('status', BaseStatusEnum::DRAFT)->get();

        $this->assertCount(1, $publishedCategories);
        $this->assertCount(1, $pendingCategories);
        $this->assertCount(1, $draftCategories);
    }

    public function test_can_filter_featured_categories(): void
    {
        ProductCategory::query()->create([
            'name' => 'Featured Category 1',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Featured Category 2',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Normal Category',
            'is_featured' => false,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $featuredCategories = ProductCategory::query()->where('is_featured', true)->get();
        $normalCategories = ProductCategory::query()->where('is_featured', false)->get();

        $this->assertCount(2, $featuredCategories);
        $this->assertCount(1, $normalCategories);
    }

    public function test_can_get_root_categories(): void
    {
        ProductCategory::query()->create([
            'name' => 'Root Category 1',
            'parent_id' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Root Category 2',
            'parent_id' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $parent = ProductCategory::query()->create([
            'name' => 'Parent Category',
            'parent_id' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Child Category',
            'parent_id' => $parent->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rootCategories = ProductCategory::query()
            ->where(function ($query): void {
                $query->whereNull('parent_id')
                    ->orWhere('parent_id', 0);
            })
            ->get();

        $this->assertCount(3, $rootCategories);
    }

    public function test_can_search_category_by_name(): void
    {
        ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Fashion',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        ProductCategory::query()->create([
            'name' => 'Home & Garden',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $categories = ProductCategory::query()
            ->where('name', 'like', '%Fashion%')
            ->get();

        $this->assertCount(1, $categories);
        $this->assertEquals('Fashion', $categories->first()->name);
    }

    public function test_nested_category_hierarchy(): void
    {
        $level1 = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $level2 = ProductCategory::query()->create([
            'name' => 'Computers',
            'parent_id' => $level1->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $level3 = ProductCategory::query()->create([
            'name' => 'Laptops',
            'parent_id' => $level2->id,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals($level1->id, $level2->parent_id);
        $this->assertEquals($level2->id, $level3->parent_id);
        $this->assertEquals('Computers', $level3->parent->name);
        $this->assertEquals('Electronics', $level3->parent->parent->name);
    }

    public function test_category_order_is_integer(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Test Category',
            'order' => 5,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertIsInt($category->order);
        $this->assertEquals(5, $category->order);
    }

    public function test_category_is_featured_is_boolean(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Test Category',
            'is_featured' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertIsBool($category->is_featured);
    }

    public function test_multiple_categories(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            ProductCategory::query()->create([
                'name' => "Category {$i}",
                'status' => BaseStatusEnum::PUBLISHED,
            ]);
        }

        $categories = ProductCategory::query()->get();

        $this->assertCount(10, $categories);
    }
}
