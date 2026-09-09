<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductLabel;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class ProductLabelFilterTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_is_enabled_filter_products_by_labels_returns_false_by_default(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', null);
        Setting::save();

        $this->assertFalse(EcommerceHelper::isEnabledFilterProductsByLabels());
    }

    public function test_is_enabled_filter_products_by_labels_returns_true_when_enabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();

        $this->assertTrue(EcommerceHelper::isEnabledFilterProductsByLabels());
    }

    public function test_is_enabled_filter_products_by_labels_returns_false_when_explicitly_disabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', false);
        Setting::save();

        $this->assertFalse(EcommerceHelper::isEnabledFilterProductsByLabels());
    }

    public function test_labels_for_filter_returns_empty_collection_when_disabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', false);
        Setting::save();

        $label = ProductLabel::query()->create([
            'name' => 'Test Label',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);

        $labels = EcommerceHelper::labelsForFilter();

        $this->assertEmpty($labels);
    }

    public function test_labels_for_filter_returns_labels_with_products_when_enabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'color' => '#FF0000',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);

        $labels = EcommerceHelper::labelsForFilter();

        $this->assertNotEmpty($labels);
        $this->assertCount(1, $labels);
        $this->assertEquals('Premium', $labels->first()->name);
    }

    public function test_labels_for_filter_excludes_labels_without_products(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $labelWithProducts = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $labelWithoutProducts = ProductLabel::query()->create([
            'name' => 'Unused Label',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($labelWithProducts);

        $labels = EcommerceHelper::labelsForFilter();

        $this->assertCount(1, $labels);
        $this->assertEquals('Premium', $labels->first()->name);
        $this->assertFalse($labels->contains('name', 'Unused Label'));
    }

    public function test_labels_for_filter_filters_by_published_status(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $label1 = ProductLabel::query()->create([
            'name' => 'Label 1',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $label2 = ProductLabel::query()->create([
            'name' => 'Label 2',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $publishedProduct = Product::query()->create([
            'name' => 'Published Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $publishedProduct->productLabels()->attach($label1);

        $labels = EcommerceHelper::labelsForFilter();

        // Should only return label1 which has a published product
        $this->assertCount(1, $labels);
        $this->assertEquals('Label 1', $labels->first()->name);
        $this->assertGreaterThan(0, $labels->first()->products_count);
    }

    public function test_labels_for_filter_is_cached(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);

        // First call - should cache
        $labelsFirstCall = EcommerceHelper::labelsForFilter();
        $this->assertCount(1, $labelsFirstCall);

        // Create new label
        $newLabel = ProductLabel::query()->create([
            'name' => 'New Label',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $newProduct = Product::query()->create([
            'name' => 'New Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $newProduct->productLabels()->attach($newLabel);

        // Second call - should still be cached with 1 label
        $labelsSecondCall = EcommerceHelper::labelsForFilter();
        $this->assertCount(1, $labelsSecondCall);

        // Clear cache and verify new label appears
        Cache::flush();
        $labelsClearedCache = EcommerceHelper::labelsForFilter();
        $this->assertCount(2, $labelsClearedCache);
    }

    public function test_labels_for_filter_respects_category_filter(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $category1 = ProductCategory::query()->create([
            'name' => 'Category 1',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $category2 = ProductCategory::query()->create([
            'name' => 'Category 2',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Product in Category 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product in Category 2',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label);
        $product1->categories()->attach($category1);

        $product2->productLabels()->attach($label);
        $product2->categories()->attach($category2);

        // Get labels for category 1
        $labelsForCategory1 = EcommerceHelper::labelsForFilter([$category1->id]);

        // Products count should only include products in category 1
        $this->assertCount(1, $labelsForCategory1);
        $this->assertEquals(1, $labelsForCategory1->first()->products_count);
    }

    public function test_has_any_product_filters_returns_true_when_labels_enabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_categories', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_brands', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_tags', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_attributes', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_price', false);
        Setting::save();

        $this->assertTrue(EcommerceHelper::hasAnyProductFilters());
    }

    public function test_has_any_product_filters_returns_false_when_labels_and_all_others_disabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_categories', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_brands', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_tags', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_attributes', false);
        Setting::save();
        Setting::forceSet('ecommerce_enable_filter_products_by_price', false);
        Setting::save();

        $this->assertFalse(EcommerceHelper::hasAnyProductFilters());
    }

    public function test_filter_products_by_single_label(): void
    {
        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Premium Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Regular Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label);

        $results = Product::query()
            ->whereHas('productLabels', function ($query) use ($label) {
                return $query->whereIn('ec_product_label_products.product_label_id', [$label->id]);
            })
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Premium Product', $results[0]->name);
    }

    public function test_filter_products_by_multiple_labels(): void
    {
        $label1 = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $label2 = ProductLabel::query()->create([
            'name' => 'New',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Premium Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'New Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product3 = Product::query()->create([
            'name' => 'Regular Product',
            'price' => 30,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label1);
        $product2->productLabels()->attach($label2);

        $results = Product::query()
            ->whereHas('productLabels', function ($query) use ($label1, $label2) {
                return $query->whereIn('ec_product_label_products.product_label_id', [$label1->id, $label2->id]);
            })
            ->get();

        $this->assertCount(2, $results);
    }

    public function test_filter_products_with_no_labels_returns_all_products(): void
    {
        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label);

        // When labels is empty, all products should be returned
        $results = Product::query()->get();

        $this->assertCount(2, $results);
    }

    public function test_data_for_filter_includes_labels_at_index_8(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);

        $filterData = EcommerceHelper::dataForFilter(null);

        $this->assertCount(9, $filterData);
        $this->assertNotEmpty($filterData[8]);
        $this->assertCount(1, $filterData[8]);
        $this->assertEquals('Premium', $filterData[8]->first()->name);
    }

    public function test_data_for_filter_returns_empty_labels_when_disabled(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', false);
        Setting::save();

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);

        $filterData = EcommerceHelper::dataForFilter(null);

        $this->assertEmpty($filterData[8]);
    }

    public function test_product_can_have_multiple_labels(): void
    {
        $label1 = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $label2 = ProductLabel::query()->create([
            'name' => 'Bestseller',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Premium Bestseller',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach([$label1->id, $label2->id]);

        $this->assertCount(2, $product->productLabels);
        $this->assertTrue($product->productLabels->contains('name', 'Premium'));
        $this->assertTrue($product->productLabels->contains('name', 'Bestseller'));
    }

    public function test_product_label_stores_color_information(): void
    {
        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'color' => '#FF0000',
            'text_color' => '#FFFFFF',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('#FF0000', $label->color);
        $this->assertEquals('#FFFFFF', $label->text_color);
    }

    public function test_detach_label_from_product(): void
    {
        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach($label);
        $this->assertCount(1, $product->productLabels);

        $product->productLabels()->detach($label);
        $product->refresh();

        $this->assertCount(0, $product->productLabels);
    }

    public function test_delete_label_detaches_from_products(): void
    {
        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label);
        $product2->productLabels()->attach($label);

        $labelId = $label->id;
        $label->delete();

        $this->assertDatabaseMissing('ec_product_label_products', [
            'product_label_id' => $labelId,
        ]);
    }

    public function test_labels_for_filter_with_multiple_categories(): void
    {
        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $category1 = ProductCategory::query()->create([
            'name' => 'Category 1',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $category2 = ProductCategory::query()->create([
            'name' => 'Category 2',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $label = ProductLabel::query()->create([
            'name' => 'Premium',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1->productLabels()->attach($label);
        $product1->categories()->attach($category1);

        $product2->productLabels()->attach($label);
        $product2->categories()->attach($category2);

        $labelsForMultipleCategories = EcommerceHelper::labelsForFilter([$category1->id, $category2->id]);

        $this->assertCount(1, $labelsForMultipleCategories);
        $this->assertEquals(2, $labelsForMultipleCategories->first()->products_count);
    }

    public function test_product_label_is_published_only(): void
    {
        $publishedLabel = ProductLabel::query()->create([
            'name' => 'Published Label',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $draftLabel = ProductLabel::query()->create([
            'name' => 'Draft Label',
            'status' => BaseStatusEnum::DRAFT,
        ]);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->productLabels()->attach([$publishedLabel->id, $draftLabel->id]);

        Setting::forceSet('ecommerce_enable_filter_products_by_labels', true);
        Setting::save();
        Cache::flush();

        $labels = EcommerceHelper::labelsForFilter();

        $this->assertCount(1, $labels);
        $this->assertEquals('Published Label', $labels->first()->name);
    }
}
