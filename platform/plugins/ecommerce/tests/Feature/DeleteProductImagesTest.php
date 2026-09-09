<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Models\MediaFile;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteProductImagesTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::forceSet('ecommerce_delete_product_images_when_deleting', '1')->save();
    }

    protected function createMediaFile(string $name, string $url): MediaFile
    {
        return MediaFile::query()->create([
            'name' => $name,
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'url' => $url,
            'user_id' => 0,
            'folder_id' => 0,
        ]);
    }

    public function test_product_images_are_deleted_when_setting_is_enabled(): void
    {
        $this->createMediaFile('test-image', 'products/test-image.jpg');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'image' => 'products/test-image.jpg',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('media_files', ['url' => 'products/test-image.jpg']);

        $product->delete();

        $this->assertDatabaseMissing('media_files', ['url' => 'products/test-image.jpg']);
    }

    public function test_product_gallery_images_are_deleted_when_setting_is_enabled(): void
    {
        $this->createMediaFile('gallery-image-1', 'products/gallery-1.jpg');
        $this->createMediaFile('gallery-image-2', 'products/gallery-2.jpg');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'images' => json_encode(['products/gallery-1.jpg', 'products/gallery-2.jpg']),
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('media_files', ['url' => 'products/gallery-1.jpg']);
        $this->assertDatabaseHas('media_files', ['url' => 'products/gallery-2.jpg']);

        $product->delete();

        $this->assertDatabaseMissing('media_files', ['url' => 'products/gallery-1.jpg']);
        $this->assertDatabaseMissing('media_files', ['url' => 'products/gallery-2.jpg']);
    }

    public function test_product_images_are_not_deleted_when_setting_is_disabled(): void
    {
        Setting::forceSet('ecommerce_delete_product_images_when_deleting', '0')->save();

        $this->createMediaFile('test-image', 'products/test-image.jpg');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'image' => 'products/test-image.jpg',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->delete();

        $this->assertDatabaseHas('media_files', ['url' => 'products/test-image.jpg']);
    }

    public function test_variation_images_are_deleted_with_parent_product(): void
    {
        $this->createMediaFile('parent-image', 'products/parent-image.jpg');
        $this->createMediaFile('variation-image', 'products/variation-image.jpg');

        $parentProduct = Product::query()->create([
            'name' => 'Parent Product',
            'price' => 100,
            'image' => 'products/parent-image.jpg',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $variationProduct = Product::query()->create([
            'name' => 'Parent Product',
            'price' => 100,
            'image' => 'products/variation-image.jpg',
            'is_variation' => true,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $parentProduct->variations()->create([
            'product_id' => $variationProduct->id,
            'configurable_product_id' => $parentProduct->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('media_files', ['url' => 'products/parent-image.jpg']);
        $this->assertDatabaseHas('media_files', ['url' => 'products/variation-image.jpg']);

        $parentProduct->delete();

        $this->assertDatabaseMissing('media_files', ['url' => 'products/parent-image.jpg']);
        $this->assertDatabaseMissing('media_files', ['url' => 'products/variation-image.jpg']);
    }

    public function test_deleting_product_without_images_does_not_throw_error(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product Without Images',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->delete();

        $this->assertDatabaseMissing('ec_products', ['id' => $product->id]);
    }

    public function test_deleting_product_with_non_existent_media_file_does_not_throw_error(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'image' => 'products/non-existent-image.jpg',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product->delete();

        $this->assertDatabaseMissing('ec_products', ['id' => $product->id]);
    }
}
