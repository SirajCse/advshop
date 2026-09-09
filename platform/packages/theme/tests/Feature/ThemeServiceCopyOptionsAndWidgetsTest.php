<?php

namespace Botble\Theme\Tests\Feature;

use Botble\Setting\Facades\Setting;
use Botble\Setting\Models\Setting as SettingModel;
use Botble\Setting\Supports\SettingStore;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Facades\ThemeOption;
use Botble\Theme\Services\ThemeService;
use Botble\Widget\Models\Widget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class ThemeServiceCopyOptionsAndWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected ThemeService $themeService;

    protected Filesystem $files;

    protected SettingStore $settingStore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);

        // Clean start - delete settings (not truncate, which breaks DB transactions)
        SettingModel::query()->delete();

        // Ensure a theme is always set
        SettingModel::query()->insert([
            [
                'key' => 'theme',
                'value' => 'homzen',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Re-instantiate to clear cache
        $this->settingStore = app(SettingStore::class);
        $this->settingStore->forgetAll();

        $this->themeService = app(ThemeService::class);
    }

    protected function refreshSettings(): void
    {
        // Force reload settings from database
        $this->settingStore->forgetAll();
        // Reset the loaded flag so next get() will reload from database
        $this->settingStore->load(force: true);
    }

    protected function createThemeDirectory(string $themeName): void
    {
        $themeDir = theme_path($themeName);
        if (! $this->files->isDirectory($themeDir)) {
            $this->files->makeDirectory($themeDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up created theme directories
        $this->files->deleteDirectory(theme_path('homzen-custom'));
        $this->files->deleteDirectory(theme_path('homzen-premium'));

        parent::tearDown();
    }

    /**
     * Test copyThemeOptions correctly copies parent theme options to child theme
     */
    public function testCopyThemeOptionsFromParentToChildTheme(): void
    {
        // Setup: Set parent theme as current
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();
        $this->refreshSettings();

        // Create parent theme options
        Setting::set('theme-homzen-logo', 'logo.png');
        Setting::set('theme-homzen-primary-color', '#0066ff');
        Setting::save();
        $this->refreshSettings();

        // Copy options from parent to child theme
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Assert: Child theme should have copied options with correct prefix
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-logo',
            'value' => 'logo.png',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-primary-color',
            'value' => '#0066ff',
        ]);

        // Assert: Parent theme options should still exist
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-logo',
            'value' => 'logo.png',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-primary-color',
            'value' => '#0066ff',
        ]);
    }

    /**
     * Test copyThemeOptions does NOT copy child theme keys (prevents recursive stacking)
     */
    public function testCopyThemeOptionsExcludesChildThemeKeys(): void
    {
        // Setup: Create child theme directories so they're recognized as related
        $this->createThemeDirectory('homzen-custom');
        $this->createThemeDirectory('homzen-premium');

        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create parent and sibling theme options
        Setting::set('theme-homzen-logo', 'parent-logo.png');
        Setting::set('theme-homzen-premium-logo', 'premium-logo.png');
        Setting::save();
        $this->refreshSettings();

        // Copy options to child theme (should NOT include premium settings because premium is a sibling)
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Assert: Parent option copied to child
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-logo',
            'value' => 'parent-logo.png',
        ]);

        // Assert: Premium variant was NOT copied (excluded because homzen-premium is a related theme)
        $premiumCustom = SettingModel::query()
            ->where('key', 'theme-homzen-custom-premium-logo')
            ->first();
        $this->assertNull($premiumCustom);

        // Assert: Premium options still exist in their own namespace
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-premium-logo',
            'value' => 'premium-logo.png',
        ]);

        // Count total settings - should not have recursive stacking
        $settingsCount = SettingModel::query()
            ->where('key', 'LIKE', 'theme-homzen-%')
            ->count();

        // Should have: parent-logo, homzen-custom-logo, homzen-premium-logo = 3
        $this->assertEquals(3, $settingsCount);
    }

    /**
     * Test copyThemeWidgets correctly copies parent theme widgets
     */
    public function testCopyThemeWidgetsFromParentToChildTheme(): void
    {
        // Setup: Parent theme is active
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create parent theme widgets
        Widget::query()->insert([
            [
                'widget_id' => 'recent-posts',
                'sidebar_id' => 'home-sidebar',
                'theme' => 'homzen',
                'position' => 0,
                'data' => json_encode(['title' => 'Recent Posts']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => 'categories',
                'sidebar_id' => 'home-sidebar',
                'theme' => 'homzen',
                'position' => 1,
                'data' => json_encode(['title' => 'Categories']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Copy widgets from parent to child theme
        $this->themeService->copyThemeWidgets('homzen-custom');
        $this->refreshSettings();

        // Assert: Child theme should have copied widgets
        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen-custom',
            'widget_id' => 'recent-posts',
        ]);

        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen-custom',
            'widget_id' => 'categories',
        ]);

        // Assert: Parent widgets still exist
        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen',
            'widget_id' => 'recent-posts',
        ]);

        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen',
            'widget_id' => 'categories',
        ]);
    }

    /**
     * Test copyThemeWidgets does NOT copy child theme widgets (prevents recursive stacking)
     */
    public function testCopyThemeWidgetsExcludesChildThemeWidgets(): void
    {
        // Setup: Parent theme is active
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create parent and child theme widgets
        Widget::query()->insert([
            [
                'widget_id' => 'recent-posts',
                'sidebar_id' => 'home-sidebar',
                'theme' => 'homzen',
                'position' => 0,
                'data' => json_encode(['title' => 'Parent Recent Posts']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Existing child widget from previous activation
            [
                'widget_id' => 'featured-products',
                'sidebar_id' => 'home-sidebar',
                'theme' => 'homzen-custom',
                'position' => 1,
                'data' => json_encode(['title' => 'Child Featured Products']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Another child theme variant widget
            [
                'widget_id' => 'testimonials',
                'sidebar_id' => 'footer-sidebar',
                'theme' => 'homzen-premium',
                'position' => 0,
                'data' => json_encode(['title' => 'Premium Testimonials']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Copy widgets to child theme
        $this->themeService->copyThemeWidgets('homzen-custom');
        $this->refreshSettings();

        // Assert: Only parent widget copied, not child variants
        $customThemeWidgets = Widget::query()
            ->where('theme', 'homzen-custom')
            ->pluck('widget_id')
            ->toArray();

        $this->assertContains('recent-posts', $customThemeWidgets);

        // Assert: Premium variant widgets not copied
        $premiumWidgets = Widget::query()
            ->where('theme', 'homzen-premium')
            ->count();

        $this->assertEquals(1, $premiumWidgets);
    }

    /**
     * Test repeated parent→child activation doesn't create stacked keys
     */
    public function testRepeatedActivationDoesNotCreateStackedKeys(): void
    {
        // Setup: Activate parent theme
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create initial parent theme option
        Setting::set('theme-homzen-logo', 'logo-v1.png');
        Setting::save();
        $this->refreshSettings();

        // First activation: homzen → homzen-custom
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Assert: Child copy was created
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-logo',
            'value' => 'logo-v1.png',
        ]);

        // Switch to child theme
        Setting::forceSet('theme', 'homzen-custom');
        Setting::save();
        $this->refreshSettings();

        // Verify theme was switched
        $this->assertEquals('homzen-custom', setting('theme'));

        // Attempt to activate child theme again (same theme, so should be skipped)
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Assert: No new keys created with repeated activation
        $allThemeOptions = SettingModel::query()
            ->where('key', 'LIKE', 'theme-homzen%')
            ->pluck('key')
            ->toArray();

        // Should only have: theme-homzen-logo, theme-homzen-custom-logo
        $this->assertCount(2, $allThemeOptions);
        $this->assertContains('theme-homzen-logo', $allThemeOptions);
        $this->assertContains('theme-homzen-custom-logo', $allThemeOptions);

        // Assert: No triple-stacked keys
        $this->assertEmpty(array_filter($allThemeOptions, fn ($key) => str_contains($key, 'custom-custom')));
    }

    /**
     * Test locale-suffixed settings are handled correctly
     */
    public function testLocaleSpecificThemeSettingsAreCopied(): void
    {
        // Setup: Parent theme is active
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create locale-specific theme options
        Setting::set('theme-homzen-title', 'Site Title');
        Setting::set('theme-homzen-title-vi', 'Tiêu đề Trang Web');
        Setting::set('theme-homzen-title-fr', 'Titre du Site');
        Setting::save();
        $this->refreshSettings();

        // Copy options to child theme
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Assert: All locale variants copied
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-title',
            'value' => 'Site Title',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-title-vi',
            'value' => 'Tiêu đề Trang Web',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-title-fr',
            'value' => 'Titre du Site',
        ]);
    }

    /**
     * Test locale-suffixed widgets are handled correctly
     */
    public function testLocaleSpecificThemeWidgetsAreCopied(): void
    {
        // Setup: Parent theme is active
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Create locale-specific widgets
        Widget::query()->insert([
            [
                'widget_id' => 'about-widget',
                'sidebar_id' => 'footer',
                'theme' => 'homzen',
                'position' => 0,
                'data' => json_encode(['content' => 'About Us']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => 'about-widget',
                'sidebar_id' => 'footer',
                'theme' => 'homzen-vi',
                'position' => 0,
                'data' => json_encode(['content' => 'Về Chúng Tôi']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_id' => 'about-widget',
                'sidebar_id' => 'footer',
                'theme' => 'homzen-fr',
                'position' => 0,
                'data' => json_encode(['content' => 'À Propos']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Copy widgets to child theme
        $this->themeService->copyThemeWidgets('homzen-custom');
        $this->refreshSettings();

        // Assert: Base widget copied to child
        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen-custom',
            'widget_id' => 'about-widget',
        ]);

        // Assert: Locale variants should be copied with correct mapping
        // homzen-vi → homzen-custom-vi, homzen-fr → homzen-custom-fr
        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen-custom-vi',
            'widget_id' => 'about-widget',
        ]);

        $this->assertDatabaseHas('widgets', [
            'theme' => 'homzen-custom-fr',
            'widget_id' => 'about-widget',
        ]);
    }

    /**
     * Test copyThemeOptions with same theme does nothing
     */
    public function testCopyThemeOptionsWithSameThemeDoesNothing(): void
    {
        // Setup
        Setting::set('theme', 'homzen');
        Setting::set('theme-homzen-logo', 'logo.png');
        Setting::save();
        $this->refreshSettings();

        $settingsCountBefore = SettingModel::query()->count();

        // Call with same theme
        $this->themeService->copyThemeOptions('homzen');

        $settingsCountAfter = SettingModel::query()->count();

        // Assert: No new settings created
        $this->assertEquals($settingsCountBefore, $settingsCountAfter);
    }

    /**
     * Test copyThemeWidgets with same theme does nothing
     */
    public function testCopyThemeWidgetsWithSameThemeDoesNothing(): void
    {
        // Setup
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        Widget::query()->insert([
            [
                'widget_id' => 'recent-posts',
                'sidebar_id' => 'home-sidebar',
                'theme' => 'homzen',
                'position' => 0,
                'data' => json_encode(['title' => 'Recent Posts']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $widgetsCountBefore = Widget::query()->count();

        // Call with same theme
        $this->themeService->copyThemeWidgets('homzen');

        $widgetsCountAfter = Widget::query()->count();

        // Assert: No new widgets created
        $this->assertEquals($widgetsCountBefore, $widgetsCountAfter);
    }

    /**
     * Test multiple sibling child themes don't interfere with each other
     */
    public function testMultipleSiblingChildThemesDoNotInterfere(): void
    {
        // Setup: Create child theme directories
        $this->createThemeDirectory('homzen-custom');
        $this->createThemeDirectory('homzen-premium');

        Setting::set('theme', 'homzen');
        Setting::set('theme-homzen-logo', 'logo.png');
        Setting::save();
        $this->refreshSettings();

        // Create first child theme
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->refreshSettings();

        // Create second child theme (but stay as parent theme, so both copy from same parent)
        $this->themeService->copyThemeOptions('homzen-premium');
        $this->refreshSettings();

        // Assert: First child has correct settings
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-custom-logo',
            'value' => 'logo.png',
        ]);

        // Assert: Second child has correct settings
        $this->assertDatabaseHas('settings', [
            'key' => 'theme-homzen-premium-logo',
            'value' => 'logo.png',
        ]);

        // Assert: Total count is correct
        $logoKeys = SettingModel::query()
            ->where('key', 'LIKE', 'theme-homzen%logo')
            ->pluck('key')
            ->toArray();

        // Should have: theme-homzen-logo, theme-homzen-custom-logo, theme-homzen-premium-logo = 3
        $this->assertCount(3, $logoKeys);
        $this->assertContains('theme-homzen-logo', $logoKeys);
        $this->assertContains('theme-homzen-custom-logo', $logoKeys);
        $this->assertContains('theme-homzen-premium-logo', $logoKeys);
    }

    /**
     * Test that empty options/widgets list doesn't cause errors
     */
    public function testCopyWithNoOptionsOrWidgets(): void
    {
        // Setup: Parent theme with no options
        Setting::set('theme', 'homzen');
        Setting::save();
        $this->refreshSettings();

        // Should not throw any exceptions
        $this->themeService->copyThemeOptions('homzen-custom');
        $this->themeService->copyThemeWidgets('homzen-custom');

        // Assert: Database clean
        $this->assertDatabaseCount('settings', 1); // Only 'theme' setting
        $this->assertDatabaseCount('widgets', 0);
    }
}
