<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Botble\Page\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

trait HasPageTranslation
{
    /**
     * Seed page translations for all locales
     * Loads basic translations from JSON, merges with custom content from pageTranslations() if exists
     *
     * @param array $locales
     */
    protected function seedPageTranslations(array $locales): void
    {
        if (! Schema::hasTable('pages_translations')) {
            return;
        }

        $pages = Page::query()->get(['id', 'name', 'description', 'content']);

        // Get custom translations if method exists (for complex content generation)
        $customTranslations = method_exists($this, 'pageTranslations') ? $this->pageTranslations() : [];

        foreach ($locales as $locale) {
            // Load basic translations from JSON
            $jsonTranslations = $this->loadPageTranslationsFromJson($locale);

            // Merge with custom translations (custom overrides JSON)
            $localeTranslations = array_merge(
                $jsonTranslations,
                $customTranslations[$locale] ?? []
            );

            // Load flat dictionary applied via string replacement on page content
            $contentDictionary = $this->loadPageContentDictionary($locale);

            foreach ($pages as $page) {
                $translation = $localeTranslations[$page->name] ?? [];

                $content = $translation['content']
                    ?? $this->applyPageContentDictionary($page->content, $contentDictionary);

                DB::table('pages_translations')->updateOrInsert(
                    [
                        'lang_code' => $locale,
                        'pages_id' => $page->id,
                    ],
                    [
                        'lang_code' => $locale,
                        'pages_id' => $page->id,
                        'name' => $translation['name'] ?? $page->name,
                        'description' => $translation['description'] ?? $page->description,
                        'content' => $content,
                    ]
                );
            }
        }
    }

    /**
     * Load flat string dictionary used to replace English strings inside page content.
     *
     * @param string $locale
     * @return array<string, string>
     */
    protected function loadPageContentDictionary(string $locale): array
    {
        $path = database_path("seeders/translations/{$locale}/page-content.json");

        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data)) {
            return [];
        }

        // Sort by source length DESC so longer phrases are replaced before their substrings.
        uksort($data, fn ($a, $b) => strlen((string) $b) - strlen((string) $a));

        return array_filter(
            $data,
            fn ($value, $key) => is_string($key) && is_string($value) && $key !== '' && $value !== '',
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Apply a flat translation dictionary to page content via string replacement.
     * Tries both raw and HTML-entity-encoded variants because shortcode attributes
     * store inner HTML tags encoded (e.g. `<span>` becomes `&lt;span&gt;`).
     *
     * @param string|null $content
     * @param array<string, string> $dictionary
     * @return string|null
     */
    protected function applyPageContentDictionary(?string $content, array $dictionary): ?string
    {
        if (empty($content) || empty($dictionary)) {
            return $content;
        }

        $expanded = [];
        foreach ($dictionary as $source => $translated) {
            $expanded[$source] = $translated;

            $encodedSource = htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
            $encodedTranslated = htmlspecialchars(
                $translated,
                ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                'UTF-8'
            );

            if ($encodedSource !== $source && ! isset($expanded[$encodedSource])) {
                $expanded[$encodedSource] = $encodedTranslated;
            }

            // Shortcode attribute storage doubles backslashes. So a source string
            // containing literal `\n` (backslash+n) becomes `\\n` (two backslashes+n)
            // in the stored content. Register the doubled variant so strtr matches it.
            if (str_contains($source, '\\')) {
                $doubledSource = str_replace('\\', '\\\\', $source);
                $doubledTranslated = str_replace('\\', '\\\\', $translated);
                if (! isset($expanded[$doubledSource])) {
                    $expanded[$doubledSource] = $doubledTranslated;
                }

                $doubledEncodedSource = htmlspecialchars(
                    $doubledSource,
                    ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                    'UTF-8'
                );
                if ($doubledEncodedSource !== $doubledSource && ! isset($expanded[$doubledEncodedSource])) {
                    $expanded[$doubledEncodedSource] = htmlspecialchars(
                        $doubledTranslated,
                        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
                        'UTF-8'
                    );
                }
            }
        }

        // Resort by source length DESC so longer phrases win in strtr.
        uksort($expanded, fn ($a, $b) => strlen((string) $b) - strlen((string) $a));

        return strtr($content, $expanded);
    }

    /**
     * Load page translations from JSON file
     *
     * @param string $locale
     * @return array
     */
    protected function loadPageTranslationsFromJson(string $locale): array
    {
        $path = database_path("seeders/translations/{$locale}/pages.json");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return $data ?: [];
    }

    protected function translatePageContent(string $pageName, string $locale): string
    {
        $page = Page::query()->where('name', $pageName)->first();

        if (! $page) {
            return '';
        }

        $translations = $this->getPageTranslations($pageName, $locale);

        if (empty($translations)) {
            return $page->content;
        }

        $content = $page->content;

        foreach ($translations as $english => $translated) {
            $pattern = '/' . preg_quote($english, '/') . '/su';
            $pattern = str_replace(' ', '\s+', $pattern);
            $content = preg_replace($pattern, $translated, $content);
        }

        return $content;
    }

    protected function getPageTranslations(string $pageName, string $locale): array
    {
        $methodName = 'get' . str_replace(' ', '', ucwords($pageName)) . 'Translations';

        if (method_exists($this, $methodName)) {
            $translations = $this->$methodName();

            return $translations[$locale] ?? [];
        }

        return [];
    }
}
