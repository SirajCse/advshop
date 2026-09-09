<?php

namespace Botble\LanguageAdvanced\Tests;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Page\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LanguageAdvancedManagerTest extends TestCase
{
    protected User $user;

    protected array $languages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->languages = $this->createLanguages();
        $this->user = $this->createUser();
    }

    public function testPluginsAreActive(): void
    {
        $this->assertTrue(is_plugin_active('language'));
        $this->assertTrue(is_plugin_active('language-advanced'));
    }

    public function testPageModelIsSupported(): void
    {
        $this->assertTrue(LanguageAdvancedManager::isSupported(Page::class));
        $this->assertTrue(LanguageAdvancedManager::isSupported(new Page()));
    }

    public function testUnsupportedModelReturnsFalse(): void
    {
        $this->assertFalse(LanguageAdvancedManager::isSupported(User::class));
        $this->assertFalse(LanguageAdvancedManager::isSupported(null));
    }

    public function testGetTranslatableColumns(): void
    {
        $columns = LanguageAdvancedManager::getTranslatableColumns(Page::class);

        $this->assertContains('name', $columns);
        $this->assertContains('description', $columns);
        $this->assertContains('content', $columns);
    }

    public function testGetTranslatableColumnsForUnsupportedModel(): void
    {
        $columns = LanguageAdvancedManager::getTranslatableColumns(User::class);

        $this->assertEmpty($columns);
    }

    public function testGetTranslatableColumnsWithNull(): void
    {
        $columns = LanguageAdvancedManager::getTranslatableColumns(null);

        $this->assertEmpty($columns);
    }

    public function testRegisterModule(): void
    {
        $testModel = 'App\Models\TestModel';
        $columns = ['title', 'body'];

        $result = LanguageAdvancedManager::registerModule($testModel, $columns);

        $this->assertTrue($result);
        $this->assertTrue(LanguageAdvancedManager::isSupported($testModel));
        $this->assertEquals($columns, LanguageAdvancedManager::getTranslatableColumns($testModel));

        // Clean up
        LanguageAdvancedManager::removeModule($testModel);
    }

    public function testRemoveModule(): void
    {
        $testModel = 'App\Models\TestModelToRemove';
        $columns = ['title'];

        LanguageAdvancedManager::registerModule($testModel, $columns);
        $this->assertTrue(LanguageAdvancedManager::isSupported($testModel));

        LanguageAdvancedManager::removeModule($testModel);
        $this->assertFalse(LanguageAdvancedManager::isSupported($testModel));
    }

    public function testRemoveMultipleModules(): void
    {
        $testModel1 = 'App\Models\TestModel1';
        $testModel2 = 'App\Models\TestModel2';

        LanguageAdvancedManager::registerModule($testModel1, ['title']);
        LanguageAdvancedManager::registerModule($testModel2, ['name']);

        $this->assertTrue(LanguageAdvancedManager::isSupported($testModel1));
        $this->assertTrue(LanguageAdvancedManager::isSupported($testModel2));

        LanguageAdvancedManager::removeModule([$testModel1, $testModel2]);

        $this->assertFalse(LanguageAdvancedManager::isSupported($testModel1));
        $this->assertFalse(LanguageAdvancedManager::isSupported($testModel2));
    }

    public function testSupportedModels(): void
    {
        $models = LanguageAdvancedManager::supportedModels();

        $this->assertContains(Page::class, $models);
    }

    public function testIsTranslatableMetaBox(): void
    {
        $this->assertTrue(LanguageAdvancedManager::isTranslatableMetaBox('language_advanced_wrap'));
        $this->assertTrue(LanguageAdvancedManager::isTranslatableMetaBox('seo_wrap'));
        $this->assertFalse(LanguageAdvancedManager::isTranslatableMetaBox('non_existent_meta_box'));
    }

    public function testAddTranslatableMetaBox(): void
    {
        $metaBoxKey = 'custom_meta_box_test';

        $this->assertFalse(LanguageAdvancedManager::isTranslatableMetaBox($metaBoxKey));

        $result = LanguageAdvancedManager::addTranslatableMetaBox($metaBoxKey);

        $this->assertTrue($result);
        $this->assertTrue(LanguageAdvancedManager::isTranslatableMetaBox($metaBoxKey));
    }

    public function testSaveTranslation(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'English Page',
            'description' => 'English Description',
            'content' => 'English Content',
            'user_id' => $this->user->getKey(),
        ]);

        $vietnameseCode = $this->languages[1]->lang_code;

        $request = Request::create('/test', 'POST', [
            'language' => $vietnameseCode,
            'name' => 'Vietnamese Page',
            'description' => 'Vietnamese Description',
            'content' => 'Vietnamese Content',
        ]);

        $result = LanguageAdvancedManager::save($page, $request);

        $this->assertTrue($result);

        // Verify translation was saved
        $translation = DB::table('pages_translations')
            ->where('pages_id', $page->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertNotNull($translation);
        $this->assertEquals('Vietnamese Page', $translation->name);
        $this->assertEquals('Vietnamese Description', $translation->description);
        $this->assertEquals('Vietnamese Content', $translation->content);

        // Clean up
        $page->delete();
    }

    public function testSaveTranslationUpdatesExisting(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'English Page',
            'user_id' => $this->user->getKey(),
        ]);

        $vietnameseCode = $this->languages[1]->lang_code;

        // Insert initial translation
        DB::table('pages_translations')->insert([
            'lang_code' => $vietnameseCode,
            'pages_id' => $page->getKey(),
            'name' => 'Initial Vietnamese',
        ]);

        // Update translation
        $request = Request::create('/test', 'POST', [
            'language' => $vietnameseCode,
            'name' => 'Updated Vietnamese',
        ]);

        LanguageAdvancedManager::save($page, $request);

        $translation = DB::table('pages_translations')
            ->where('pages_id', $page->getKey())
            ->where('lang_code', $vietnameseCode)
            ->first();

        $this->assertEquals('Updated Vietnamese', $translation->name);

        // Verify only one translation exists
        $count = DB::table('pages_translations')
            ->where('pages_id', $page->getKey())
            ->where('lang_code', $vietnameseCode)
            ->count();

        $this->assertEquals(1, $count);

        // Clean up
        $page->delete();
    }

    public function testSaveReturnsFalseForUnsupportedModel(): void
    {
        $request = Request::create('/test', 'POST', [
            'language' => 'vi',
            'name' => 'Test',
        ]);

        $result = LanguageAdvancedManager::save($this->user, $request);

        $this->assertFalse($result);
    }

    public function testIsValidLanguageCodeAcceptsActiveCodes(): void
    {
        $this->assertTrue(LanguageAdvancedManager::isValidLanguageCode($this->languages[1]->lang_code));
        $this->assertTrue(LanguageAdvancedManager::isValidLanguageCode($this->languages[0]->lang_code));
    }

    public function testIsValidLanguageCodeRejectsInvalidValues(): void
    {
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode(null));
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode(''));
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode('zz_ZZ'));
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode('if(now()=sysdate(),sleep(15),0)'));
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode(['vi']));
        $this->assertFalse(LanguageAdvancedManager::isValidLanguageCode(123));
    }

    public function testSaveRejectsInjectionStyleLanguage(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'English Page',
            'user_id' => $this->user->getKey(),
        ]);

        $payload = 'if(now()=sysdate(),sleep(15),0)';

        $request = Request::create('/test', 'POST', [
            'language' => $payload,
            'name' => 'Injected',
        ]);

        $result = LanguageAdvancedManager::save($page, $request);

        $this->assertFalse($result);

        // Ensure nothing was written under the truncated/injected lang_code.
        $this->assertEquals(
            0,
            DB::table('pages_translations')
                ->where('pages_id', $page->getKey())
                ->where('lang_code', substr($payload, 0, 20))
                ->count()
        );

        $page->delete();
    }

    public function testSaveRejectsXLanguageHeaderInjection(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'English Page',
            'user_id' => $this->user->getKey(),
        ]);

        $request = Request::create('/test', 'POST', ['name' => 'Injected']);
        $request->headers->set('X-LANGUAGE', "' OR 1=1 --");

        $result = LanguageAdvancedManager::save($page, $request);

        $this->assertFalse($result);

        $page->delete();
    }

    public function testDeleteTranslations(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'Page to Delete',
            'user_id' => $this->user->getKey(),
        ]);

        // Insert translations
        DB::table('pages_translations')->insert([
            ['lang_code' => 'vi', 'pages_id' => $page->getKey(), 'name' => 'Vietnamese'],
            ['lang_code' => 'fr', 'pages_id' => $page->getKey(), 'name' => 'French'],
        ]);

        $this->assertEquals(2, DB::table('pages_translations')->where('pages_id', $page->getKey())->count());

        $result = LanguageAdvancedManager::delete($page);

        $this->assertTrue($result);
        $this->assertEquals(0, DB::table('pages_translations')->where('pages_id', $page->getKey())->count());

        // Clean up
        $page->delete();
    }

    public function testDeleteReturnsFalseForUnsupportedModel(): void
    {
        $result = LanguageAdvancedManager::delete($this->user);

        $this->assertFalse($result);
    }

    public function testGetTranslationLocaleReturnsRefLangInAdmin(): void
    {
        $this->actingAs($this->user);

        $vietnameseCode = $this->languages[1]->lang_code;
        $url = route('pages.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;

        $this->get($url);

        $locale = LanguageAdvancedManager::getTranslationLocale();

        $this->assertEquals($vietnameseCode, $locale);
    }

    public function testIsDefaultLocaleReturnsTrueForDefaultLanguage(): void
    {
        $this->actingAs($this->user);

        // Access admin without ref_lang (uses default)
        $this->get(route('pages.index'));

        // When no ref_lang is specified and getCurrentAdminLocaleCode returns null,
        // isDefaultLocale should return true
        $isDefault = LanguageAdvancedManager::isDefaultLocale();

        $this->assertTrue($isDefault);
    }

    public function testIsDefaultLocaleReturnsFalseForNonDefaultLanguage(): void
    {
        $this->actingAs($this->user);

        $vietnameseCode = $this->languages[1]->lang_code;
        $url = route('pages.index') . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;

        $this->get($url);

        $isDefault = LanguageAdvancedManager::isDefaultLocale();

        $this->assertFalse($isDefault);
    }

    public function testModelAccessorReturnsTranslatedValue(): void
    {
        $this->actingAs($this->user);

        $englishTitle = 'English Title';
        $vietnameseTitle = 'Vietnamese Title';
        $vietnameseCode = $this->languages[1]->lang_code;

        $page = Page::query()->create([
            'name' => $englishTitle,
            'user_id' => $this->user->getKey(),
        ]);

        DB::table('pages_translations')->insert([
            'lang_code' => $vietnameseCode,
            'pages_id' => $page->getKey(),
            'name' => $vietnameseTitle,
        ]);

        // Access in Vietnamese context
        $url = route('pages.edit', $page->getKey()) . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        // Reload the page model
        $page->refresh();
        $page->load('translations');

        $this->assertEquals($vietnameseTitle, $page->name);

        // Clean up
        $page->delete();
    }

    public function testModelAccessorReturnsOriginalValueWhenNoTranslation(): void
    {
        $this->actingAs($this->user);

        $englishTitle = 'English Only Title';
        $vietnameseCode = $this->languages[1]->lang_code;

        $page = Page::query()->create([
            'name' => $englishTitle,
            'user_id' => $this->user->getKey(),
        ]);

        // Access in Vietnamese context but no translation exists
        $url = route('pages.edit', $page->getKey()) . '?' . LanguageFacade::refLangKey() . '=' . $vietnameseCode;
        $this->get($url);

        $page->refresh();
        $page->load('translations');

        // Should return original English value
        $this->assertEquals($englishTitle, $page->name);

        // Clean up
        $page->delete();
    }

    public function testTranslationsRelationship(): void
    {
        $this->actingAs($this->user);

        $page = Page::query()->create([
            'name' => 'Page with Translations',
            'user_id' => $this->user->getKey(),
        ]);

        DB::table('pages_translations')->insert([
            ['lang_code' => 'vi', 'pages_id' => $page->getKey(), 'name' => 'Vietnamese'],
            ['lang_code' => 'fr', 'pages_id' => $page->getKey(), 'name' => 'French'],
        ]);

        $page->refresh();

        // Load translations relation
        $translations = $page->translations;

        $this->assertNotNull($translations);
        $this->assertCount(2, $translations);

        // Verify we can find specific translations
        $this->assertNotNull($translations->where('lang_code', 'vi')->first());
        $this->assertNotNull($translations->where('lang_code', 'fr')->first());
        $this->assertEquals('Vietnamese', $translations->where('lang_code', 'vi')->first()->name);
        $this->assertEquals('French', $translations->where('lang_code', 'fr')->first()->name);

        // Clean up
        $page->delete();
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
            [
                'lang_name' => 'Français',
                'lang_locale' => 'fr',
                'lang_is_default' => false,
                'lang_code' => 'fr',
                'lang_is_rtl' => false,
                'lang_flag' => 'fr',
                'lang_order' => 2,
            ],
        ];

        Schema::disableForeignKeyConstraints();

        Language::query()->truncate();
        LanguageMeta::query()->truncate();
        DB::table('pages_translations')->truncate();

        $results = [];

        foreach ($languages as $item) {
            $results[] = Language::query()->create($item);
        }

        return $results;
    }
}
