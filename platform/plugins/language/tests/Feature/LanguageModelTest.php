<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Page\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LanguageModelTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->delete();
        LanguageMeta::query()->delete();
    }

    public function testLanguageCanBeCreated(): void
    {
        $language = Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        $this->assertDatabaseHas('languages', ['lang_name' => 'English']);
        $this->assertEquals('en_US', $language->lang_code);
        $this->assertTrue($language->lang_is_default);
        $this->assertFalse($language->lang_is_rtl);
    }

    public function testLanguageCastsCorrectly(): void
    {
        $language = Language::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => 1,
            'lang_code' => 'ar',
            'lang_is_rtl' => 1,
            'lang_flag' => 'sa',
            'lang_order' => '5',
        ]);

        $this->assertIsBool($language->lang_is_default);
        $this->assertIsBool($language->lang_is_rtl);
        $this->assertIsInt($language->lang_order);
        $this->assertTrue($language->lang_is_rtl);
        $this->assertEquals(5, $language->lang_order);
    }

    public function testLanguageHasMetaRelationship(): void
    {
        $language = Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        LanguageMeta::query()->create([
            'lang_meta_code' => 'en_US',
            'lang_meta_origin' => md5('test'),
            'reference_id' => 1,
            'reference_type' => Page::class,
        ]);

        $this->assertCount(1, $language->meta);
        $this->assertEquals('en_US', $language->meta->first()->lang_meta_code);
    }

    public function testDeletingLanguageAutoSetsNewDefault(): void
    {
        $english = Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        Language::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => false,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 1,
        ]);

        $english->delete();

        $remaining = Language::query()->first();
        $this->assertTrue($remaining->lang_is_default);
    }

    public function testDeletingLanguageCascadesDeletesMeta(): void
    {
        $language = Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        $page = Page::query()->create([
            'name' => 'Cascade Test Page',
            'user_id' => 0,
        ]);

        LanguageMeta::query()->create([
            'lang_meta_code' => 'en_US',
            'lang_meta_origin' => md5('test'),
            'reference_id' => $page->getKey(),
            'reference_type' => Page::class,
        ]);

        $this->assertDatabaseHas('language_meta', ['lang_meta_code' => 'en_US']);

        $language->delete();

        $this->assertDatabaseMissing('language_meta', ['lang_meta_code' => 'en_US']);
    }

    public function testLanguageHasNoTimestamps(): void
    {
        $language = new Language();
        $this->assertFalse($language->timestamps);
    }

    public function testLanguageUsesCustomPrimaryKey(): void
    {
        $language = new Language();
        $this->assertEquals('lang_id', $language->getKeyName());
    }

    public function testLanguageMetaCanBeCreated(): void
    {
        $meta = LanguageMeta::query()->create([
            'lang_meta_code' => 'en_US',
            'lang_meta_origin' => md5('test-origin'),
            'reference_id' => 1,
            'reference_type' => Page::class,
        ]);

        $this->assertDatabaseHas('language_meta', [
            'lang_meta_code' => 'en_US',
            'reference_id' => 1,
            'reference_type' => Page::class,
        ]);
        $this->assertEquals('lang_meta_id', $meta->getKeyName());
    }

    public function testLanguageMetaSaveMetaDataWithDefaults(): void
    {
        Language::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        $page = Page::create([
            'name' => 'Test Page',
            'user_id' => 0,
        ]);

        LanguageMeta::saveMetaData($page);

        $meta = LanguageMeta::query()->where('reference_id', $page->getKey())->first();
        $this->assertNotNull($meta);
        $this->assertNotEmpty($meta->lang_meta_origin);
        $this->assertNotEmpty($meta->lang_meta_code);
    }

    public function testLanguageMetaSaveMetaDataWithExplicitValues(): void
    {
        $page = Page::create([
            'name' => 'Test Page',
            'user_id' => 0,
        ]);

        $origin = md5('custom-origin');
        LanguageMeta::saveMetaData($page, 'fr_FR', $origin);

        $meta = LanguageMeta::query()->where('reference_id', $page->getKey())->first();
        $this->assertEquals('fr_FR', $meta->lang_meta_code);
        $this->assertEquals($origin, $meta->lang_meta_origin);
    }

    public function testLanguageMetaHasNoTimestamps(): void
    {
        $meta = new LanguageMeta();
        $this->assertFalse($meta->timestamps);
    }

    public function testLanguageMetaReferenceRelationship(): void
    {
        $page = Page::query()->create([
            'name' => 'Test Page',
            'user_id' => 0,
        ]);

        $meta = LanguageMeta::query()->create([
            'lang_meta_code' => 'en_US',
            'lang_meta_origin' => md5('test'),
            'reference_id' => $page->getKey(),
            'reference_type' => Page::class,
        ]);

        $reference = $meta->reference;
        $this->assertInstanceOf(Page::class, $reference);
        $this->assertEquals('Test Page', $reference->name);
    }
}
