<?php

namespace Botble\LanguageAdvanced\Tests\Feature;

use Botble\Language\Models\Language as LanguageModel;
use Botble\LanguageAdvanced\Listeners\UpdatePermalinkSettingsForEachLanguage;
use Botble\Page\Models\Page;
use Botble\Setting\Supports\SettingStore;
use Botble\Slug\Events\UpdatedPermalinkSettings;
use Botble\Slug\Models\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpdatePermalinkSettingsForEachLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingStore::class)->forgetAll();

        LanguageModel::query()->truncate();
        Slug::query()->truncate();
        DB::table('slugs_translations')->truncate();

        $this->createLanguages();
    }

    public function testSkipsWhenRefLangNotFilled(): void
    {
        $this->createSlugsForReference(Page::class, 3);

        $request = new Request();
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);

        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        $this->assertSame(0, DB::table('slugs_translations')->count());
    }

    public function testCreatesTranslationsForAllSlugsOfReferenceType(): void
    {
        $this->createSlugsForReference(Page::class, 5);

        $request = new Request(['ref_lang' => 'vi']);
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);

        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        $this->assertSame(5, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        DB::table('slugs_translations')->where('lang_code', 'vi')->get()->each(function ($row) {
            $this->assertSame('trang', $row->prefix);
        });
    }

    public function testDoesNotCreateDuplicateTranslationsForSameLanguage(): void
    {
        $slugs = $this->createSlugsForReference(Page::class, 3);

        // Run once
        $request = new Request(['ref_lang' => 'vi']);
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        // Run again with different prefix — should NOT create duplicates
        $event2 = new UpdatedPermalinkSettings(Page::class, 'cac-trang', $request);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($event2);

        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        DB::table('slugs_translations')->where('lang_code', 'vi')->get()->each(function ($row) {
            $this->assertSame('cac-trang', $row->prefix);
        });
    }

    public function testCreatesTranslationsPerLanguageIndependently(): void
    {
        $this->createSlugsForReference(Page::class, 3);

        // Save Vietnamese prefix
        $viRequest = new Request(['ref_lang' => 'vi']);
        $viEvent = new UpdatedPermalinkSettings(Page::class, 'trang', $viRequest);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($viEvent);

        // Save Arabic prefix — should create separate translations
        $arRequest = new Request(['ref_lang' => 'ar']);
        $arEvent = new UpdatedPermalinkSettings(Page::class, 'صفحات', $arRequest);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($arEvent);

        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'vi')->count());
        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'ar')->count());
        $this->assertSame(6, DB::table('slugs_translations')->count());
    }

    public function testDoesNotOverwriteOtherLanguagePrefixes(): void
    {
        $this->createSlugsForReference(Page::class, 3);

        // Save Vietnamese prefix
        $viRequest = new Request(['ref_lang' => 'vi']);
        $viEvent = new UpdatedPermalinkSettings(Page::class, 'trang', $viRequest);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($viEvent);

        // Save Arabic prefix
        $arRequest = new Request(['ref_lang' => 'ar']);
        $arEvent = new UpdatedPermalinkSettings(Page::class, 'صفحات', $arRequest);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($arEvent);

        // Vietnamese prefix must remain 'trang', not overwritten by Arabic
        DB::table('slugs_translations')->where('lang_code', 'vi')->get()->each(function ($row) {
            $this->assertSame('trang', $row->prefix);
        });

        DB::table('slugs_translations')->where('lang_code', 'ar')->get()->each(function ($row) {
            $this->assertSame('صفحات', $row->prefix);
        });
    }

    public function testUpdatesOnlyTargetLanguagePrefix(): void
    {
        $this->createSlugsForReference(Page::class, 2);

        // Create translations for both languages
        $viRequest = new Request(['ref_lang' => 'vi']);
        (new UpdatePermalinkSettingsForEachLanguage())->handle(
            new UpdatedPermalinkSettings(Page::class, 'trang', $viRequest)
        );

        $arRequest = new Request(['ref_lang' => 'ar']);
        (new UpdatePermalinkSettingsForEachLanguage())->handle(
            new UpdatedPermalinkSettings(Page::class, 'صفحات', $arRequest)
        );

        // Update Vietnamese prefix to new value
        (new UpdatePermalinkSettingsForEachLanguage())->handle(
            new UpdatedPermalinkSettings(Page::class, 'cac-trang', $viRequest)
        );

        // Vietnamese should be updated
        DB::table('slugs_translations')->where('lang_code', 'vi')->get()->each(function ($row) {
            $this->assertSame('cac-trang', $row->prefix);
        });

        // Arabic should remain unchanged
        DB::table('slugs_translations')->where('lang_code', 'ar')->get()->each(function ($row) {
            $this->assertSame('صفحات', $row->prefix);
        });
    }

    public function testOnlyAffectsSlugsOfTargetReferenceType(): void
    {
        $this->createSlugsForReference(Page::class, 3);
        $this->createSlugsForReference('Botble\RealEstate\Models\Project', 2, 'projects');

        $request = new Request(['ref_lang' => 'vi']);
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        // Project slugs should have no translations
        $projectSlugIds = Slug::query()
            ->where('reference_type', 'Botble\RealEstate\Models\Project')
            ->pluck('id')
            ->all();

        $this->assertSame(
            0,
            DB::table('slugs_translations')->whereIn('slugs_id', $projectSlugIds)->count()
        );
    }

    public function testPreservesSlugKeyInTranslations(): void
    {
        $slugs = $this->createSlugsForReference(Page::class, 3);

        $request = new Request(['ref_lang' => 'vi']);
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        foreach ($slugs as $slug) {
            $translation = DB::table('slugs_translations')
                ->where('slugs_id', $slug->id)
                ->where('lang_code', 'vi')
                ->first();

            $this->assertNotNull($translation);
            $this->assertSame($slug->key, $translation->key);
        }
    }

    public function testHandlesLargeNumberOfSlugs(): void
    {
        // Create more slugs than the chunk size (500) to test chunked insert
        $this->createSlugsForReference(Page::class, 550);

        $request = new Request(['ref_lang' => 'vi']);
        $event = new UpdatedPermalinkSettings(Page::class, 'trang', $request);
        (new UpdatePermalinkSettingsForEachLanguage())->handle($event);

        $this->assertSame(550, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        DB::table('slugs_translations')->where('lang_code', 'vi')->get()->each(function ($row) {
            $this->assertSame('trang', $row->prefix);
        });
    }

    public function testNewSlugGetsTranslationOnSubsequentRun(): void
    {
        $this->createSlugsForReference(Page::class, 3);

        $request = new Request(['ref_lang' => 'vi']);
        (new UpdatePermalinkSettingsForEachLanguage())->handle(
            new UpdatedPermalinkSettings(Page::class, 'trang', $request)
        );

        $this->assertSame(3, DB::table('slugs_translations')->where('lang_code', 'vi')->count());

        // Add a new slug (simulating new content creation)
        $page = Page::query()->create(['name' => 'New Page']);
        Slug::query()->create([
            'key' => 'new-page',
            'prefix' => 'pages',
            'reference_type' => Page::class,
            'reference_id' => $page->getKey(),
        ]);

        // Run again — should pick up the new slug
        (new UpdatePermalinkSettingsForEachLanguage())->handle(
            new UpdatedPermalinkSettings(Page::class, 'trang', $request)
        );

        $this->assertSame(4, DB::table('slugs_translations')->where('lang_code', 'vi')->count());
    }

    /**
     * @return \Illuminate\Support\Collection<int, Slug>
     */
    protected function createSlugsForReference(string $referenceType, int $count, string $prefix = 'pages'): Collection
    {
        $slugs = collect();

        for ($i = 1; $i <= $count; $i++) {
            $page = Page::query()->create(['name' => "Page $i"]);

            $slugs->push(Slug::query()->create([
                'key' => "page-$i-" . uniqid(),
                'prefix' => $prefix,
                'reference_type' => $referenceType,
                'reference_id' => $page->getKey(),
            ]));
        }

        return $slugs;
    }

    protected function createLanguages(): void
    {
        LanguageModel::query()->create([
            'lang_name' => 'English',
            'lang_locale' => 'en',
            'lang_is_default' => true,
            'lang_code' => 'en_US',
            'lang_is_rtl' => false,
            'lang_flag' => 'us',
            'lang_order' => 0,
        ]);

        LanguageModel::query()->create([
            'lang_name' => 'Tiếng Việt',
            'lang_locale' => 'vi',
            'lang_is_default' => false,
            'lang_code' => 'vi',
            'lang_is_rtl' => false,
            'lang_flag' => 'vn',
            'lang_order' => 1,
        ]);

        LanguageModel::query()->create([
            'lang_name' => 'Arabic',
            'lang_locale' => 'ar',
            'lang_is_default' => false,
            'lang_code' => 'ar',
            'lang_is_rtl' => true,
            'lang_flag' => 'sa',
            'lang_order' => 2,
        ]);
    }
}
