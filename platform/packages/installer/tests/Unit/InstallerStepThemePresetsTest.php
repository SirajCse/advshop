<?php

namespace Botble\Installer\Tests\Unit;

use Botble\Installer\InstallerStep\InstallerStep;
use Botble\Theme\Facades\Manager;
use Botble\Theme\Facades\Theme;
use ReflectionClass;
use Tests\TestCase;

class InstallerStepThemePresetsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetStaticPresetCache();
    }

    protected function tearDown(): void
    {
        $this->resetStaticPresetCache();

        parent::tearDown();
    }

    /**
     * Clear the static $themePresets cache between tests so each
     * test controls the Manager mock independently.
     */
    protected function resetStaticPresetCache(): void
    {
        $reflection = new ReflectionClass(InstallerStep::class);

        $cache = $reflection->getProperty('themePresets');
        $cache->setAccessible(true);
        $cache->setValue(null, []);
    }

    /**
     * Lightweight stub for the Theme facade. We cannot use
     * Mockery::mock(BaseTheme::class) because Mockery's generated
     * __call signature conflicts with the real Theme class.
     */
    protected function themeStub(): object
    {
        return new class {
            public function getThemeScreenshot(string $theme, ?string $name = null): string
            {
                return 'stub-screenshot.png';
            }

            public function getThemeName(): string
            {
                return 'stub-theme';
            }

            public function __call(string $method, array $parameters = [])
            {
                return null;
            }
        };
    }

    public function test_filters_out_string_preset_entries(): void
    {
        InstallerStep::setCurrentTheme('test-theme-string-filter');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-string-filter')
            ->andReturn([
                'Creative Agency',
                ['name' => 'Valid Preset'],
                'Another String',
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertCount(2, $presets, 'Expected the default preset + 1 valid custom preset (strings filtered).');
        $this->assertArrayHasKey('test-theme-string-filter-default', $presets);
        $this->assertArrayHasKey('test-theme-string-filter-valid-preset', $presets);
    }

    public function test_filters_out_entries_missing_name(): void
    {
        InstallerStep::setCurrentTheme('test-theme-missing-name');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-missing-name')
            ->andReturn([
                ['screenshot' => 'screenshot-1.png'],
                ['name' => '', 'screenshot' => 'screenshot-2.png'],
                ['name' => 'Valid'],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertCount(2, $presets);
        $this->assertArrayHasKey('test-theme-missing-name-default', $presets);
        $this->assertArrayHasKey('test-theme-missing-name-valid', $presets);
    }

    public function test_includes_database_field_from_preset_definition(): void
    {
        InstallerStep::setCurrentTheme('test-theme-with-db');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-with-db')
            ->andReturn([
                [
                    'id' => 'home-2',
                    'name' => 'Home 2',
                    'screenshot' => 'screenshot-home-2.png',
                    'database' => 'database-home2.sql',
                ],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertArrayHasKey('test-theme-with-db-home-2', $presets);
        $this->assertSame('database-home2.sql', $presets['test-theme-with-db-home-2']['database']);
    }

    public function test_database_field_defaults_to_null_when_omitted(): void
    {
        InstallerStep::setCurrentTheme('test-theme-no-db');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-no-db')
            ->andReturn([
                ['id' => 'digital-agency', 'name' => 'Digital Agency'],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertArrayHasKey('test-theme-no-db-digital-agency', $presets);
        $this->assertNull($presets['test-theme-no-db-digital-agency']['database']);
    }

    public function test_uses_explicit_id_when_provided(): void
    {
        InstallerStep::setCurrentTheme('test-theme-explicit-id');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-explicit-id')
            ->andReturn([
                ['id' => 'custom-slug', 'name' => 'Totally Different Label'],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertArrayHasKey('test-theme-explicit-id-custom-slug', $presets);
        $this->assertSame('Totally Different Label', $presets['test-theme-explicit-id-custom-slug']['label']);
    }

    public function test_kebabs_name_as_fallback_id_when_id_missing(): void
    {
        InstallerStep::setCurrentTheme('test-theme-kebab');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-kebab')
            ->andReturn([
                ['name' => 'Digital Agency'],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $this->assertArrayHasKey('test-theme-kebab-digital-agency', $presets);
    }

    public function test_includes_default_preset_first(): void
    {
        InstallerStep::setCurrentTheme('test-theme-default-order');

        Manager::shouldReceive('getThemePresets')
            ->with('test-theme-default-order')
            ->andReturn([
                ['id' => 'custom', 'name' => 'Custom'],
            ]);

        Theme::swap($this->themeStub());

        $presets = InstallerStep::getThemePresets();

        $keys = array_keys($presets);
        $this->assertSame('test-theme-default-order-default', $keys[0]);
        $this->assertSame('test-theme-default-order-custom', $keys[1]);
    }
}
