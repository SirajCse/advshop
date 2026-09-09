<?php

namespace Botble\LanguageAdvanced\Tests;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Forms\FormFieldOptions;
use Botble\Language\Facades\Language as LanguageFacade;
use Botble\Language\Models\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\Setting\Models\Setting;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\ThemeOption;
use Botble\Theme\ThemeOption\Fields\TextField;
use Botble\Theme\ThemeOption\ThemeOptionSection;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SharedThemeOptionFieldTest extends TestCase
{
    protected User $user;

    protected array $languages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->languages = $this->createLanguages();
        $this->user = $this->createUser();
    }

    // --- Field metadata unit tests ---

    public function testThemeOptionFieldSharedDefaultsFalse(): void
    {
        $field = TextField::make()
            ->name('test_field')
            ->sectionId('general')
            ->label('Test');

        $this->assertFalse($field->isShared());
        $this->assertFalse($field->toArray()['shared']);
    }

    public function testThemeOptionFieldSharedCanBeEnabled(): void
    {
        $field = TextField::make()
            ->name('test_field')
            ->sectionId('general')
            ->label('Test')
            ->shared();

        $this->assertTrue($field->isShared());
        $this->assertTrue($field->toArray()['shared']);
    }

    public function testThemeOptionFieldSharedCanBeDisabled(): void
    {
        $field = TextField::make()
            ->name('test_field')
            ->sectionId('general')
            ->label('Test')
            ->shared()
            ->shared(false);

        $this->assertFalse($field->isShared());
    }

    public function testThemeOptionSectionSharedDefaultsFalse(): void
    {
        $section = ThemeOptionSection::make('test-section')
            ->title('Test')
            ->icon('ti ti-settings');

        $this->assertFalse($section->isShared());
        $this->assertFalse($section->toArray()['shared']);
    }

    public function testThemeOptionSectionSharedCanBeEnabled(): void
    {
        $section = ThemeOptionSection::make('test-section')
            ->title('Test')
            ->icon('ti ti-settings')
            ->shared();

        $this->assertTrue($section->isShared());
        $this->assertTrue($section->toArray()['shared']);
    }

    public function testFormFieldOptionsSharedDefaultsFalse(): void
    {
        $options = FormFieldOptions::make();

        $this->assertFalse($options->isShared());
        $this->assertArrayNotHasKey('shared', $options->toArray());
    }

    public function testFormFieldOptionsSharedIncludedInArrayWhenTrue(): void
    {
        $options = FormFieldOptions::make()->shared();

        $this->assertTrue($options->isShared());
        $this->assertArrayHasKey('shared', $options->toArray());
        $this->assertTrue($options->toArray()['shared']);
    }

    // --- isFieldShared logic tests ---

    public function testIsFieldSharedReturnsFalseForUnregisteredField(): void
    {
        RenderingThemeOptionSettings::dispatch();

        $this->assertFalse(ThemeOption::isFieldShared('non_existent_field'));
    }

    public function testIsFieldSharedReturnsTrueForSharedField(): void
    {
        $this->registerTestThemeOptions();

        $this->assertTrue(ThemeOption::isFieldShared('shared_color'));
    }

    public function testIsFieldSharedReturnsFalseForTranslatableField(): void
    {
        $this->registerTestThemeOptions();

        $this->assertFalse(ThemeOption::isFieldShared('translatable_title'));
    }

    public function testIsFieldSharedInheritsSectionShared(): void
    {
        $this->registerTestThemeOptions();

        $this->assertTrue(ThemeOption::isFieldShared('field_in_shared_section'));
    }

    public function testIsFieldSharedFilterHookCanOverride(): void
    {
        $this->registerTestThemeOptions();

        add_filter('theme_option_field_is_shared', function (bool $isShared, string $key): bool {
            if ($key === 'translatable_title') {
                return true;
            }

            return $isShared;
        }, 100, 2);

        $this->assertTrue(ThemeOption::isFieldShared('translatable_title'));
    }

    // --- setOption / getOptionKey storage tests ---

    public function testSetOptionUsesDefaultKeyForSharedField(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $theme = setting('theme') ?: 'shofy';

        // Make an admin request to set admin context
        $vietnameseCode = $this->languages[1]->lang_code;
        $this->get(route('theme.options') . '?ref_lang=' . $vietnameseCode);

        ThemeOption::setOption('shared_color', '#FF0000');
        setting()->save();

        // Shared field should be stored without locale suffix
        $defaultKey = "theme-{$theme}-shared_color";
        $this->assertNotNull(
            Setting::query()->where('key', $defaultKey)->first(),
            "Shared field should be stored at default key: {$defaultKey}"
        );

        // Should NOT have a locale-specific key
        $localeKey = "theme-{$theme}-{$vietnameseCode}-shared_color";
        $this->assertNull(
            Setting::query()->where('key', $localeKey)->first(),
            "Shared field should NOT create locale-specific key: {$localeKey}"
        );

        Setting::query()->where('key', 'LIKE', "theme-{$theme}-%shared_color")->delete();
    }

    public function testSharedFieldSavedViaPostOmitsLocaleKey(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $theme = setting('theme') ?: 'shofy';
        $vietnameseCode = $this->languages[1]->lang_code;

        // POST with ref_lang — shared fields should still go to default key
        $this->post(
            route('theme.options.post') . '?ref_lang=' . $vietnameseCode,
            ['shared_color' => '#FF0000', 'translatable_title' => 'Vietnamese Title']
        );

        // Shared field should be at default key (no locale suffix)
        $defaultKey = "theme-{$theme}-shared_color";
        $this->assertNotNull(
            Setting::query()->where('key', $defaultKey)->first(),
            "Shared field should be stored at default key: {$defaultKey}"
        );

        // Shared field should NOT have a locale-specific key
        $localeKey = "theme-{$theme}-{$vietnameseCode}-shared_color";
        $this->assertNull(
            Setting::query()->where('key', $localeKey)->first(),
            "Shared field should NOT create locale-specific key: {$localeKey}"
        );

        Setting::query()->where('key', 'LIKE', "theme-{$theme}-%shared_color")->delete();
        Setting::query()->where('key', 'LIKE', "theme-{$theme}-%translatable_title")->delete();
    }

    public function testGetOptionKeyOmitsLocaleForSharedField(): void
    {
        $this->registerTestThemeOptions();

        $theme = setting('theme') ?: 'shofy';

        // Shared field: getOptionKey with null locale → no locale suffix
        $key = ThemeOption::getOptionKey('shared_color', null, $theme);
        $this->assertEquals("theme-{$theme}-shared_color", $key);

        // Translatable field: getOptionKey with locale → has locale suffix
        $key = ThemeOption::getOptionKey('translatable_title', 'vi', $theme);
        $this->assertEquals("theme-{$theme}-vi-translatable_title", $key);
    }

    // --- prepareFromArray tests ---

    public function testPrepareFromArrayRejectsSharedFieldsForNonDefaultLocale(): void
    {
        $this->registerTestThemeOptions();

        $options = [
            'shared_color' => '#FF0000',
            'translatable_title' => 'Hello',
        ];

        $result = ThemeOption::prepareFromArray($options, 'vi', 'en_US');

        $keys = array_keys($result);
        $hasShared = collect($keys)->contains(fn ($k) => str_contains($k, 'shared_color'));
        $hasTranslatable = collect($keys)->contains(fn ($k) => str_contains($k, 'translatable_title'));

        $this->assertFalse($hasShared, 'Shared field should be rejected from non-default locale array');
        $this->assertTrue($hasTranslatable, 'Translatable field should be kept in non-default locale array');
    }

    public function testPrepareFromArrayKeepsSharedFieldsForDefaultLocale(): void
    {
        $this->registerTestThemeOptions();

        $options = [
            'shared_color' => '#FF0000',
            'translatable_title' => 'Hello',
        ];

        $result = ThemeOption::prepareFromArray($options, 'en_US', 'en_US');

        $keys = array_keys($result);
        $hasShared = collect($keys)->contains(fn ($k) => str_contains($k, 'shared_color'));
        $hasTranslatable = collect($keys)->contains(fn ($k) => str_contains($k, 'translatable_title'));

        $this->assertTrue($hasShared, 'Shared field should be present for default locale');
        $this->assertTrue($hasTranslatable, 'Translatable field should be present for default locale');
    }

    public function testPrepareFromArrayKeepsAllFieldsWhenNoLocale(): void
    {
        $this->registerTestThemeOptions();

        $options = [
            'shared_color' => '#FF0000',
            'translatable_title' => 'Hello',
        ];

        $result = ThemeOption::prepareFromArray($options, null, null);

        $this->assertCount(2, $result);
    }

    // --- Language plugin filter hook integration ---

    public function testThemeOptionsIsNonDefaultLocaleFilterForDefaultLanguage(): void
    {
        $this->actingAs($this->user);

        RenderingThemeOptionSettings::dispatch();

        $defaultCode = $this->languages[0]->lang_code;
        LanguageFacade::setCurrentAdminLocale($defaultCode);

        $result = apply_filters('theme_options_is_non_default_locale', false);
        $this->assertFalse($result, 'Default locale should return false');
    }

    public function testThemeOptionsIsNonDefaultLocaleFilterForNonDefaultLanguage(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $vietnameseCode = $this->languages[1]->lang_code;

        // Access theme options with ref_lang — the filter should detect non-default locale
        // and cause shared fields to show the disabled notice
        $response = $this->get(
            route('theme.options', 'test-shared-section') . '?ref_lang=' . $vietnameseCode
        );
        $response->assertOk();

        // The filter enables disabling shared fields for non-default locale
        $response->assertSee('pointer-events: none');
    }

    // --- Admin UI integration tests ---

    public function testThemeOptionsAdminShowsSharedBadge(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $response = $this->get(route('theme.options', 'test-shared-section'));
        $response->assertOk();

        $response->assertSee(trans('packages/theme::theme.all_languages'));
    }

    public function testThemeOptionsAdminDisablesSharedFieldsForNonDefaultLocale(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $vietnameseCode = $this->languages[1]->lang_code;

        $response = $this->get(
            route('theme.options', 'test-shared-section') . '?ref_lang=' . $vietnameseCode
        );
        $response->assertOk();

        $response->assertSee(trans('packages/theme::theme.shared_field_notice'));
    }

    public function testThemeOptionsAdminDoesNotDisableSharedFieldsForDefaultLocale(): void
    {
        $this->actingAs($this->user);
        $this->registerTestThemeOptions();

        $response = $this->get(route('theme.options', 'test-shared-section'));
        $response->assertOk();

        $response->assertDontSee(trans('packages/theme::theme.shared_field_notice'));
    }

    // --- Helpers ---

    protected function registerTestThemeOptions(): void
    {
        RenderingThemeOptionSettings::dispatch();

        ThemeOption::setSection(
            ThemeOptionSection::make('test-shared-section')
                ->title('Shared Section Test')
                ->icon('ti ti-settings')
        );

        ThemeOption::setSection(
            ThemeOptionSection::make('test-shared-section-inherits')
                ->title('Shared Section Inherits Test')
                ->icon('ti ti-settings')
                ->shared()
        );

        ThemeOption::setField([
            'id' => 'shared_color',
            'section_id' => 'test-shared-section',
            'type' => 'customColor',
            'label' => 'Shared Color',
            'shared' => true,
            'attributes' => [
                'name' => 'shared_color',
                'value' => '#000000',
            ],
        ]);

        ThemeOption::setField([
            'id' => 'translatable_title',
            'section_id' => 'test-shared-section',
            'type' => 'text',
            'label' => 'Translatable Title',
            'attributes' => [
                'name' => 'translatable_title',
                'value' => '',
            ],
        ]);

        ThemeOption::setField([
            'id' => 'field_in_shared_section',
            'section_id' => 'test-shared-section-inherits',
            'type' => 'text',
            'label' => 'Field In Shared Section',
            'attributes' => [
                'name' => 'field_in_shared_section',
                'value' => '',
            ],
        ]);
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
