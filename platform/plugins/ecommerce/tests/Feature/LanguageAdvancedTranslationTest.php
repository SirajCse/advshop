<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Models\GlobalOptionValue;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\OptionValue;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tests that ecommerce translation saving uses LanguageAdvancedManager::getTranslationLocale()
 * instead of $request->input('language'), which returns null when language-advanced is active.
 *
 * @see EcommerceServiceProvider LANGUAGE_ADVANCED_ACTION_SAVED handler
 */
class LanguageAdvancedTranslationTest extends TestCase
{
    protected User $user;

    protected array $languages;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_plugin_active('language') || ! is_plugin_active('language-advanced') || ! is_plugin_active('ecommerce')) {
            $this->markTestSkipped('language, language-advanced, and ecommerce plugins must be active.');
        }

        $this->languages = $this->createLanguages();
        $this->user = $this->createUser();
    }

    public function test_option_translation_uses_locale_from_manager(): void
    {
        $this->actingAs($this->user);

        $option = Option::query()->create(['name' => 'Color']);

        $vietnameseCode = $this->languages[1]->lang_code;

        // Simulate language-advanced context: set ref_lang query param (no 'language' form input)
        $url = route('products.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        LanguageAdvancedManager::clearLocaleCache();

        // Build request WITHOUT 'language' input — this is what language-advanced does
        $request = Request::create($url, 'POST', [
            'ref_lang' => $vietnameseCode,
            'options' => [
                [
                    'id' => $option->getKey(),
                    'name' => 'Màu sắc',
                    'values' => [],
                ],
            ],
        ]);

        app()->instance('request', $request);

        // Fire the action handler
        do_action(LANGUAGE_ADVANCED_ACTION_SAVED, Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]), $request);

        $translation = DB::table('ec_options_translations')
            ->where('ec_options_id', $option->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertNotNull($translation, 'Option translation should be saved when language-advanced is active');
        $this->assertEquals('Màu sắc', $translation->name);
    }

    public function test_option_value_translation_uses_locale_from_manager(): void
    {
        $this->actingAs($this->user);

        $option = Option::query()->create(['name' => 'Size']);
        $optionValue = OptionValue::query()->create([
            'option_id' => $option->getKey(),
            'option_value' => 'Large',
        ]);

        $vietnameseCode = $this->languages[1]->lang_code;

        $url = route('products.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        LanguageAdvancedManager::clearLocaleCache();

        $request = Request::create($url, 'POST', [
            'ref_lang' => $vietnameseCode,
            'options' => [
                [
                    'id' => $option->getKey(),
                    'name' => 'Kích cỡ',
                    'values' => [
                        [
                            'id' => $optionValue->getKey(),
                            'option_value' => 'Lớn',
                        ],
                    ],
                ],
            ],
        ]);

        app()->instance('request', $request);

        do_action(LANGUAGE_ADVANCED_ACTION_SAVED, Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]), $request);

        $translation = DB::table('ec_option_value_translations')
            ->where('ec_option_value_id', $optionValue->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertNotNull($translation, 'OptionValue translation should be saved when language-advanced is active');
        $this->assertEquals('Lớn', $translation->option_value);
    }

    public function test_product_attribute_set_translation_uses_locale_from_manager(): void
    {
        $this->actingAs($this->user);

        $attributeSet = ProductAttributeSet::query()->create([
            'title' => 'Color',
            'slug' => 'color',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $attribute = ProductAttribute::query()->create([
            'attribute_set_id' => $attributeSet->getKey(),
            'title' => 'Red',
            'slug' => 'red',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $vietnameseCode = $this->languages[1]->lang_code;

        $url = route('product-attribute-sets.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        LanguageAdvancedManager::clearLocaleCache();

        $request = Request::create($url, 'POST', [
            'ref_lang' => $vietnameseCode,
            'attributes' => json_encode([
                [
                    'id' => $attribute->getKey(),
                    'title' => 'Đỏ',
                ],
            ]),
        ]);

        app()->instance('request', $request);

        do_action(LANGUAGE_ADVANCED_ACTION_SAVED, $attributeSet, $request);

        $translation = DB::table('ec_product_attributes_translations')
            ->where('ec_product_attributes_id', $attribute->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertNotNull($translation, 'ProductAttribute translation should be saved when language-advanced is active');
        $this->assertEquals('Đỏ', $translation->title);
    }

    public function test_global_option_value_translation_uses_locale_from_manager(): void
    {
        $this->actingAs($this->user);

        $globalOption = GlobalOption::query()->create(['name' => 'Warranty', 'option_type' => 'field']);
        $globalOptionValue = GlobalOptionValue::query()->create([
            'option_id' => $globalOption->getKey(),
            'option_value' => '1 Year',
        ]);

        $vietnameseCode = $this->languages[1]->lang_code;

        $url = route('global-option.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        LanguageAdvancedManager::clearLocaleCache();

        $request = Request::create($url, 'POST', [
            'ref_lang' => $vietnameseCode,
            'id' => $globalOption->getKey(),
            'language' => null, // Explicitly null — simulates language-advanced removing the field
            'options' => [
                [
                    'id' => $globalOptionValue->getKey(),
                    'option_value' => '1 Năm',
                ],
            ],
        ]);

        app()->instance('request', $request);

        do_action(LANGUAGE_ADVANCED_ACTION_SAVED, $globalOption, $request);

        $translation = DB::table('ec_global_option_value_translations')
            ->where('ec_global_option_value_id', $globalOptionValue->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertNotNull($translation, 'GlobalOptionValue translation should be saved when language-advanced is active');
        $this->assertEquals('1 Năm', $translation->option_value);
    }

    public function test_translation_not_saved_when_no_options_provided(): void
    {
        $this->actingAs($this->user);

        $vietnameseCode = $this->languages[1]->lang_code;

        $url = route('products.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        // Record count before action
        $countBefore = DB::table('ec_options_translations')->count();

        $request = Request::create($url, 'POST', [
            'ref_lang' => $vietnameseCode,
            'options' => [],
        ]);

        app()->instance('request', $request);

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        do_action(LANGUAGE_ADVANCED_ACTION_SAVED, $product, $request);

        $countAfter = DB::table('ec_options_translations')->count();

        $this->assertEquals($countBefore, $countAfter, 'No new translations should be saved when options array is empty');
    }

    protected function createUser(): User
    {
        Schema::disableForeignKeyConstraints();

        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@domain.com',
            'username' => config('core.base.general.demo.account.username'),
            'password' => config('core.base.general.demo.account.password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

    protected function createLanguages(): array
    {
        $languages = [
            [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_is_default' => true,
                'lang_code' => 'en_US',
                'lang_is_rtl' => false,
                'lang_flag' => 'us',
                'lang_order' => 0,
            ],
            [
                'lang_name' => 'Tiếng Việt',
                'lang_locale' => 'vi',
                'lang_is_default' => false,
                'lang_code' => 'vi',
                'lang_is_rtl' => false,
                'lang_flag' => 'vn',
                'lang_order' => 1,
            ],
        ];

        Schema::disableForeignKeyConstraints();

        Language::query()->truncate();
        LanguageMeta::query()->truncate();

        $results = [];

        foreach ($languages as $item) {
            $results[] = Language::query()->create($item);
        }

        return $results;
    }
}
