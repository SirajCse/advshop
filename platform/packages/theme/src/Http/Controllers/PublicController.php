<?php

namespace Botble\Theme\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Models\Post;
use Botble\Language\Facades\Language;
use Botble\Page\Models\Page;
use Botble\Page\Services\PageService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Events\RenderingHomePageEvent;
use Botble\Theme\Events\RenderingSingleEvent;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class PublicController extends BaseController
{
    public function getIndex()
    {
        Theme::addBodyAttributes(['id' => 'page-home']);

        if (defined('PAGE_MODULE_SCREEN_NAME') && BaseHelper::getHomepageId()) {
            $data = (new PageService())->handleFrontRoutes(null);

            event(new RenderingSingleEvent(new Slug()));

            if ($data) {
                return Theme::scope($data['view'], $data['data'], $data['default_view'])->render();
            }
        }

        SeoHelper::setTitle(Theme::getSiteTitle());

        event(RenderingHomePageEvent::class);

        return Theme::scope('index')->render();
    }

    public function getView(?string $key = null, string $prefix = '')
    {
        if (empty($key)) {
            return $this->getIndex();
        }

        $slug = SlugHelper::getSlug($key, $prefix);

        abort_unless($slug, 404);

        if (
            defined('PAGE_MODULE_SCREEN_NAME') &&
            $slug->reference_type === Page::class &&
            BaseHelper::isHomepage($slug->reference_id)
        ) {
            return redirect()->to(BaseHelper::getHomepageUrl());
        }

        $result = apply_filters(BASE_FILTER_PUBLIC_SINGLE_DATA, $slug);

        $extension = SlugHelper::getPublicSingleEndingURL();

        if ($extension) {
            $key = Str::replaceLast($extension, '', $key);
        }

        if ($result instanceof BaseHttpResponse) {
            return $result;
        }

        if (isset($result['slug']) && $result['slug'] !== $key) {
            $prefix = SlugHelper::getPrefix(Arr::first($result['data'])::class);

            return redirect()->route('public.single', empty($prefix) ? $result['slug'] : "$prefix/{$result['slug']}");
        }

        event(new RenderingSingleEvent($slug));

        if (! empty($result) && is_array($result)) {
            if (isset($result['view'])) {
                Theme::addBodyAttributes(['id' => Str::slug(Str::snake(Str::afterLast($slug->reference_type, '\\'))) . '-' . $slug->reference_id]);

                return Theme::scope($result['view'], $result['data'], Arr::get($result, 'default_view'))->render();
            }

            return $result;
        }

        abort(404);
    }

    public function getSiteMap()
    {
        // When the Language plugin is active and the request hits the locale-less
        // /sitemap.xml URL, return a sitemap index of every active locale's sitemap
        // so search engines can discover the per-locale sitemaps.
        if (
            is_plugin_active('language')
            && ! Language::checkLocaleInSupportedLocales(request()->segment(1))
        ) {
            // Use the supported-locales array keys (lang_locale) — these are what
            // the language plugin uses as the route prefix, not lang_code. For
            // English the lang_code is "en_US" while lang_locale is "en", so
            // /{lang_code}/sitemap.xml would 404.
            $supportedLocales = Language::getSupportedLocales();

            if (count($supportedLocales) > 1) {
                $sitemaps = collect($supportedLocales)
                    ->map(fn (array $language, string $localeCode) => [
                        'loc' => url($localeCode . '/sitemap.xml'),
                        'lastmod' => null,
                    ])
                    ->values()
                    ->all();

                // Resolve the sitemap service so its deferred provider boots and
                // registers the `packages/sitemap` view namespace before render.
                app('sitemap');

                return response()
                    ->view('packages/sitemap::sitemapindex', [
                        'sitemaps' => $sitemaps,
                        'style' => null,
                    ])
                    ->header('Content-Type', 'application/xml');
            }
        }

        return $this->getSiteMapIndex();
    }

    public function getSiteMapIndex(?string $key = null, string $extension = 'xml')
    {
        if ($key == 'sitemap') {
            $key = null;
        }

        if ($key && SiteMapManager::isKeyExcluded($key)) {
            abort(404);
        }

        if (! SiteMapManager::init($key, $extension)->isCached()) {
            event(new RenderingSiteMapEvent($key));
        }

        // show your site map (options: 'xml' (default), 'xml-mobile', 'html', 'txt', 'ror-rss', 'ror-rdf', 'google-news')
        return SiteMapManager::render($key ? $extension : 'sitemapindex');
    }

    public function getViewWithPrefix(string $prefix, ?string $slug = null)
    {
        return $this->getView($slug, $prefix);
    }

    /**
     * Generate a default llms.txt following the https://llmstxt.org specification.
     * Served only when a static public/llms.txt file does not exist (the web server
     * serves the static file first, so this route acts as the dynamic fallback).
     */
    public function getLlmsTxt()
    {
        $lines = [];

        // Spec requires the document to begin with a single H1 (the site name).
        $siteTitle = Theme::getSiteTitle() ?: setting('admin_title', config('app.name'));
        $lines[] = '# ' . $this->cleanLlmsText($siteTitle, 150);

        // Short site summary as a blockquote, right after the H1.
        $description = theme_option('seo_description') ?: setting('admin_description');
        if ($description) {
            $lines[] = '';
            $lines[] = '> ' . $this->cleanLlmsText($description, 300);
        }

        // Content sections. Each model is optional - skipped when its plugin is absent.
        $lines = array_merge($lines, $this->buildLlmsModelSection(Page::class, __('Pages'), 100));
        $lines = array_merge($lines, $this->buildLlmsModelSection(Post::class, __('Blog'), 50));

        // Reference the XML sitemap for full coverage.
        if (setting('sitemap_enabled', true)) {
            $lines[] = '';
            $lines[] = '## Optional';
            $lines[] = '- [XML Sitemap](' . route('public.sitemap') . ')';
        }

        $content = implode("\n", $lines) . "\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Build a markdown "## Heading" section listing a slugable model's published
     * items as links. Returns [] (no leading blank line) when the model's plugin is
     * not installed or there are no valid items.
     */
    protected function buildLlmsModelSection(string $modelClass, string $heading, int $limit): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        try {
            $items = $modelClass::query()
                ->wherePublished()
                ->latest()
                ->select(['id', 'name', 'description'])
                ->with('slugable')
                ->limit($limit)
                ->get();
        } catch (Throwable) {
            return [];
        }

        $bullets = [];

        foreach ($items as $item) {
            // Read into variables first: `url` is a dynamic magic accessor, so
            // empty($item->url) would short-circuit to true via __isset().
            $url = $item->url;
            $name = $item->name;

            if (! $url || ! $name) {
                continue;
            }

            // Strip brackets from the label so they cannot break the [title](url) syntax.
            $title = str_replace(['[', ']'], '', $this->cleanLlmsText($name, 150));
            $line = sprintf('- [%s](%s)', $title, $url);

            $description = $item->description;
            if ($description) {
                $line .= ': ' . $this->cleanLlmsText($description, 150);
            }

            $bullets[] = $line;
        }

        if (empty($bullets)) {
            return [];
        }

        return array_merge(['', '## ' . $heading], $bullets);
    }

    /**
     * Normalize text for plain-text/markdown output: strip HTML, collapse
     * whitespace, and truncate to a readable length.
     */
    protected function cleanLlmsText(string $text, int $limit): string
    {
        $text = strip_tags($text);
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) > $limit) {
            $text = rtrim(mb_substr($text, 0, $limit - 3)) . '...';
        }

        return $text;
    }
}
