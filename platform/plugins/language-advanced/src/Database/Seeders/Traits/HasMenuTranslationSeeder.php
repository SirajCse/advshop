<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Botble\Language\Models\LanguageMeta;
use Botble\Menu\Database\Traits\HasMenuSeeder;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HasMenuTranslationSeeder
{
    use HasMenuSeeder;

    /**
     * Seed menu translations for all locales
     *
     * @param array $locales
     */
    protected function seedMenus(array $locales): void
    {
        $defaultMenuSlugs = $this->getDefaultMenuSlugs();
        $menus = MenuModel::query()
            ->whereIn('slug', $defaultMenuSlugs)
            ->get()
            ->keyBy('slug');

        if ($menus->isEmpty()) {
            return;
        }

        $menuOrigins = [];
        foreach ($menus as $slug => $menu) {
            $menuOrigins[$slug] = $this->getLanguageMetaOrigin($menu);
        }

        $mainMenuLocation = MenuLocation::query()
            ->where('menu_id', $menus->get('main-menu')?->getKey())
            ->where('location', 'main-menu')
            ->first();

        $locationOrigin = $mainMenuLocation ? $this->getLanguageMetaOrigin($mainMenuLocation) : null;

        $pageIds = Page::query()->pluck('id', 'name')->all();

        foreach ($locales as $locale) {
            $translations = $this->loadMenuTranslations($locale);
            if (empty($translations)) {
                continue;
            }

            if (isset($translations['main-menu']) && is_array($translations['main-menu'])) {
                $mainMenu = $translations['main-menu'];

                $this->createMenuTranslation(
                    $locale,
                    'main-menu',
                    $mainMenu['name'] ?? 'Main menu',
                    $this->buildMainMenuItems($mainMenu, $pageIds),
                    $menuOrigins['main-menu'] ?? null,
                    $locationOrigin
                );
            }
        }

        Menu::clearCacheMenuItems();
    }

    /**
     * Load menu translations from JSON file
     *
     * @param string $locale
     * @return array
     */
    protected function loadMenuTranslations(string $locale): array
    {
        $path = database_path("seeders/translations/{$locale}/menu.json");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return $data ?: [];
    }

    /**
     * Build main menu items structure
     * Override this method in your seeder to customize menu structure
     *
     * @param array $labels
     * @param array $pageIds
     * @return array
     */
    protected function buildMainMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['home'],
                'children' => [
                    [
                        'title' => $labels['homepage_1'],
                        'reference_type' => Page::class,
                        'reference_id' => $pageIds['Homepage 1'] ?? null,
                    ],
                    [
                        'title' => $labels['homepage_2'],
                        'reference_type' => Page::class,
                        'reference_id' => $pageIds['Homepage 2'] ?? null,
                    ],
                    [
                        'title' => $labels['homepage_3'],
                        'reference_type' => Page::class,
                        'reference_id' => $pageIds['Homepage 3'] ?? null,
                    ],
                    [
                        'title' => $labels['homepage_4'],
                        'reference_type' => Page::class,
                        'reference_id' => $pageIds['Homepage 4'] ?? null,
                    ],
                    [
                        'title' => $labels['homepage_5'],
                        'reference_type' => Page::class,
                        'reference_id' => $pageIds['Homepage 5'] ?? null,
                    ],
                ],
            ],
            [
                'title' => $labels['projects'],
                'url' => '/projects',
            ],
            [
                'title' => $labels['properties'],
                'url' => '/properties',
            ],
            [
                'title' => $labels['pages'],
                'url' => '#',
                'children' => [
                    [
                        'title' => $labels['agents'],
                        'url' => '/agents',
                    ],
                    [
                        'title' => $labels['careers'],
                        'url' => '/careers',
                    ],
                    [
                        'title' => $labels['wishlist'],
                        'url' => '/wishlist',
                    ],
                    [
                        'title' => $labels['about_us'],
                        'url' => '/about-us',
                    ],
                    [
                        'title' => $labels['services'],
                        'url' => '/services',
                    ],
                    [
                        'title' => $labels['pricing'],
                        'url' => '/pricing',
                    ],
                ],
            ],
            [
                'title' => $labels['contact'],
                'url' => '/contact',
            ],
            [
                'title' => $labels['faqs'],
                'url' => '/faqs',
            ],
            [
                'title' => $labels['privacy'],
                'url' => '/privacy-policy',
            ],
            [
                'title' => $labels['coming_soon'],
                'url' => '/coming-soon',
            ],
            [
                'title' => $labels['blog'],
                'url' => '#',
                'children' => [
                    [
                        'title' => $labels['blog_list'],
                        'url' => '/blog',
                    ],
                    [
                        'title' => $labels['blog_detail'],
                        'url' => '/blog/understanding-the-real-estate-market-a-comprehensive-guide',
                    ],
                ],
            ],
        ];
    }

    /**
     * Create menu translation
     *
     * @param string $locale
     * @param string $baseSlug
     * @param string $name
     * @param array $items
     * @param string|null $menuOrigin
     * @param string|null $locationOrigin
     */
    protected function createMenuTranslation(
        string $locale,
        string $baseSlug,
        string $name,
        array $items,
        ?string $menuOrigin,
        ?string $locationOrigin
    ): void {
        $slug = $this->localizedSlug($baseSlug, $locale);

        $menu = MenuModel::updateOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        MenuNode::query()->where('menu_id', $menu->getKey())->delete();
        MenuLocation::query()->where('menu_id', $menu->getKey())->delete();

        if ($baseSlug === 'main-menu') {
            $menuLocation = MenuLocation::create([
                'menu_id' => $menu->getKey(),
                'location' => 'main-menu',
            ]);

            if ($locationOrigin) {
                LanguageMeta::saveMetaData($menuLocation, $locale, $locationOrigin);
            } else {
                LanguageMeta::saveMetaData($menuLocation, $locale);
            }
        }

        foreach ($items as $position => $menuNode) {
            $this->createMenuNode($position, $menuNode, $menu->getKey());
        }

        if ($menuOrigin) {
            LanguageMeta::saveMetaData($menu, $locale, $menuOrigin);
        } else {
            LanguageMeta::saveMetaData($menu, $locale);
        }
    }

    /**
     * Get default menu slugs to translate
     *
     * @return array
     */
    protected function getDefaultMenuSlugs(): array
    {
        return ['main-menu'];
    }

    /**
     * Get language meta origin for a model
     *
     * @param object $model
     * @return string
     */
    protected function getLanguageMetaOrigin(object $model): string
    {
        $origin = LanguageMeta::query()
            ->where('reference_type', $model::class)
            ->where('reference_id', $model->getKey())
            ->value('lang_meta_origin');

        return $origin ?: md5($model->getKey() . $model::class . Str::random(6));
    }

    /**
     * Create localized slug
     *
     * @param string $slug
     * @param string $locale
     * @return string
     */
    protected function localizedSlug(string $slug, string $locale): string
    {
        return sprintf('%s-%s', $slug, $locale);
    }
}
