<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class ProductSearchSettingTest extends BaseTestCase
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

    public function test_can_access_product_search_settings_page(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->get(route('ecommerce.settings.product-search'));

        $response->assertOk();
    }

    public function test_can_save_product_search_settings(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.product-search.update'), [
            'search_for_an_exact_phrase' => '0',
            'search_products_by' => ['name', 'sku', 'barcode'],
            'enable_filter_products_by_categories' => '1',
            'enable_filter_products_by_brands' => '1',
            'enable_filter_products_by_tags' => '1',
            'number_of_popular_tags_for_filter' => 10,
            'enable_filter_products_by_attributes' => '1',
            'enable_filter_products_by_price' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $searchBy = EcommerceHelper::getProductsSearchBy();

        $this->assertContains('name', $searchBy);
        $this->assertContains('sku', $searchBy);
        $this->assertContains('barcode', $searchBy);
    }

    public function test_can_save_all_search_options(): void
    {
        $this->actingAs($this->admin, 'web');

        $allOptions = ['name', 'sku', 'variation_sku', 'barcode', 'description', 'brand', 'tag'];

        $response = $this->put(route('ecommerce.settings.product-search.update'), [
            'search_for_an_exact_phrase' => '1',
            'search_products_by' => $allOptions,
            'enable_filter_products_by_categories' => '1',
            'enable_filter_products_by_brands' => '1',
            'enable_filter_products_by_tags' => '1',
            'number_of_popular_tags_for_filter' => 15,
            'enable_filter_products_by_attributes' => '1',
            'enable_filter_products_by_price' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $searchBy = EcommerceHelper::getProductsSearchBy();

        foreach ($allOptions as $option) {
            $this->assertContains($option, $searchBy);
        }
    }

    public function test_rejects_invalid_search_option(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.product-search.update'), [
            'search_for_an_exact_phrase' => '0',
            'search_products_by' => ['name', 'invalid_option'],
            'enable_filter_products_by_categories' => '1',
            'enable_filter_products_by_brands' => '1',
            'enable_filter_products_by_tags' => '1',
            'enable_filter_products_by_attributes' => '1',
            'enable_filter_products_by_price' => '1',
        ]);

        $response->assertSessionHasErrors('search_products_by.1');
    }

    public function test_search_products_by_requires_at_least_one_option(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.product-search.update'), [
            'search_for_an_exact_phrase' => '0',
            'search_products_by' => [],
            'enable_filter_products_by_categories' => '1',
            'enable_filter_products_by_brands' => '1',
            'enable_filter_products_by_tags' => '1',
            'enable_filter_products_by_attributes' => '1',
            'enable_filter_products_by_price' => '1',
        ]);

        $response->assertSessionHasErrors('search_products_by');
    }

    public function test_default_search_options_when_no_setting(): void
    {
        Setting::forceSet('ecommerce_search_products_by', null);
        Setting::save();

        $searchBy = EcommerceHelper::getProductsSearchBy();

        $this->assertEquals(['name', 'sku', 'description'], $searchBy);
    }

    public function test_can_save_barcode_as_only_search_option(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->put(route('ecommerce.settings.product-search.update'), [
            'search_for_an_exact_phrase' => '0',
            'search_products_by' => ['barcode'],
            'enable_filter_products_by_categories' => '1',
            'enable_filter_products_by_brands' => '1',
            'enable_filter_products_by_tags' => '1',
            'enable_filter_products_by_attributes' => '1',
            'enable_filter_products_by_price' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $searchBy = EcommerceHelper::getProductsSearchBy();

        $this->assertCount(1, $searchBy);
        $this->assertContains('barcode', $searchBy);
    }
}
