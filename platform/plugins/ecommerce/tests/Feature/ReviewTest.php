<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createProduct(): Product
    {
        return Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_can_create_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great product!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ec_reviews', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
        ]);
    }

    public function test_review_star_rating(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 4,
            'comment' => 'Good product',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(4, $review->star);
    }

    public function test_review_belongs_to_product(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Excellent!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals($product->id, $review->product->id);
        $this->assertEquals('Test Product', $review->product->name);
    }

    public function test_review_belongs_to_customer(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Love it!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals($customer->id, $review->user->id);
        $this->assertEquals('Test Customer', $review->user->name);
    }

    public function test_review_status_published(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals(BaseStatusEnum::PUBLISHED, $review->status);
        $this->assertTrue($review->is_approved);
    }

    public function test_review_status_pending(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Awaiting approval',
            'status' => BaseStatusEnum::PENDING,
        ]);

        $this->assertEquals(BaseStatusEnum::PENDING, $review->status);
        $this->assertFalse($review->is_approved);
    }

    public function test_has_user_reviewed_product(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $this->assertFalse(Review::hasUserReviewed($customer->id, $product->id));

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue(Review::hasUserReviewed($customer->id, $product->id));
    }

    public function test_get_user_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $this->assertNull(Review::getUserReview($customer->id, $product->id));

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 4,
            'comment' => 'Good product',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $review = Review::getUserReview($customer->id, $product->id);

        $this->assertNotNull($review);
        $this->assertEquals(4, $review->star);
        $this->assertEquals('Good product', $review->comment);
    }

    public function test_review_with_images(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $images = ['reviews/image1.jpg', 'reviews/image2.jpg'];

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Look at these photos!',
            'images' => $images,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertCount(2, $review->images);
        $this->assertEquals('reviews/image1.jpg', $review->images[0]);
    }

    public function test_review_without_images(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'No images',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertNull($review->images);
    }

    public function test_can_filter_reviews_by_star_rating(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Perfect!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer2->id,
            'star' => 3,
            'comment' => 'Okay',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $fiveStarReviews = Review::query()->where('star', 5)->get();
        $threeStarReviews = Review::query()->where('star', 3)->get();

        $this->assertCount(1, $fiveStarReviews);
        $this->assertCount(1, $threeStarReviews);
    }

    public function test_can_filter_reviews_by_product(): void
    {
        $customer = $this->createCustomer();

        $product1 = Product::query()->create([
            'name' => 'Product 1',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product 2',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Review::query()->create([
            'product_id' => $product1->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great product 1!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        Review::query()->create([
            'product_id' => $product1->id,
            'customer_id' => $customer2->id,
            'star' => 4,
            'comment' => 'Good product 1!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Review::query()->create([
            'product_id' => $product2->id,
            'customer_id' => $customer->id,
            'star' => 3,
            'comment' => 'Product 2 is okay',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product1Reviews = Review::query()->where('product_id', $product1->id)->get();
        $product2Reviews = Review::query()->where('product_id', $product2->id)->get();

        $this->assertCount(2, $product1Reviews);
        $this->assertCount(1, $product2Reviews);
    }

    public function test_product_name_attribute(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('Test Product', $review->product_name);
    }

    public function test_user_name_attribute(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'star' => 5,
            'comment' => 'Great!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('Test Customer', $review->user_name);
    }

    public function test_review_with_guest_customer_name(): void
    {
        $product = $this->createProduct();

        $review = Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => 0,
            'customer_name' => 'Guest Reviewer',
            'customer_email' => 'guest@example.com',
            'star' => 4,
            'comment' => 'Guest review',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('Guest Reviewer', $review->customer_name);
        $this->assertEquals('guest@example.com', $review->customer_email);
    }

    public function test_can_calculate_average_rating(): void
    {
        $product = $this->createProduct();

        $customers = [];
        for ($i = 1; $i <= 3; $i++) {
            $customers[] = Customer::query()->create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
                'password' => bcrypt('password'),
            ]);
        }

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customers[0]->id,
            'star' => 5,
            'comment' => 'Excellent!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customers[1]->id,
            'star' => 4,
            'comment' => 'Good!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Review::query()->create([
            'product_id' => $product->id,
            'customer_id' => $customers[2]->id,
            'star' => 3,
            'comment' => 'Okay',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $averageRating = Review::query()
            ->where('product_id', $product->id)
            ->avg('star');

        $this->assertEquals(4, $averageRating);
    }
}
