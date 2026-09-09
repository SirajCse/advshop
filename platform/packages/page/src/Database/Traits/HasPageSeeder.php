<?php

namespace Botble\Page\Database\Traits;

use Botble\ACL\Models\User;
use Botble\Page\Models\Page;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

trait HasPageSeeder
{
    protected function getPageId(string $name): int|string|null
    {
        return Page::query()->where('name', $name)->value('id');
    }

    protected function createPages(array $pages): void
    {
        $userId = User::query()->value('id');

        foreach ($pages as $item) {
            $item['user_id'] = $userId ?: 0;

            /**
             * @var Page $page
             */
            $page = Page::query()->create(Arr::except($item, 'metadata'));

            $this->createMetadata($page, $item);

            SlugHelper::createSlug($page);
        }
    }

    /**
     * Create pages with translations stored in pages_translations table.
     *
     * @param array $pages Array of page data, each with optional 'translations' key
     */
    protected function createPagesWithTranslations(array $pages): void
    {
        $userId = User::query()->value('id');

        foreach ($pages as $pageData) {
            $translations = $pageData['translations'] ?? [];
            unset($pageData['translations']);

            $pageData['user_id'] = $userId ?: 0;

            /**
             * @var Page $page
             */
            $page = Page::query()->create(Arr::except($pageData, 'metadata'));

            $this->createMetadata($page, $pageData);

            SlugHelper::createSlug($page);

            foreach ($translations as $locale => $translation) {
                DB::table('pages_translations')->insert([
                    'lang_code' => $locale,
                    'pages_id' => $page->id,
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                    'content' => $translation['content'] ?? null,
                ]);
            }
        }
    }

    /**
     * Truncate pages and their translations.
     */
    protected function truncatePages(): void
    {
        Page::query()->truncate();
    }

    /**
     * Truncate pages translations table.
     */
    protected function truncatePagesTranslations(): void
    {
        DB::table('pages_translations')->truncate();
    }

    protected function generateShortcodeContent(array $shortcodes): string
    {
        return htmlentities(implode(PHP_EOL, array_map(
            fn ($shortcode): string => Shortcode::generateShortcode(
                $shortcode['name'],
                Arr::get($shortcode, 'attributes', []),
                Arr::get($shortcode, 'content')
            ),
            $shortcodes
        )), ENT_NOQUOTES, 'UTF-8');
    }
}
