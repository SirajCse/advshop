<?php

namespace Botble\Language\Listeners;

use Botble\Base\Facades\BaseHelper;
use Botble\Language\Facades\Language;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Page\Models\Page;
use Botble\Slug\Models\Slug;
use Botble\Theme\Events\RenderingSingleEvent;
use Exception;

class AddHrefLangListener
{
    public function handle(RenderingSingleEvent $event): void
    {
        try {
            if (! defined('THEME_FRONT_HEADER')) {
                return;
            }

            add_filter(THEME_FRONT_HEADER, function ($header) use ($event) {
                $referenceType = $event->slug->reference_type;
                $referenceId = $event->slug->reference_id;

                if (! $referenceType && ! $referenceId) {
                    $referenceType = Page::class;
                }

                $isSupported = in_array($referenceType, Language::supportedModels());

                if (is_plugin_active('language-advanced') && class_exists(LanguageAdvancedManager::class)) {
                    $isSupported = $isSupported || LanguageAdvancedManager::isSupported($referenceType);
                }

                if (! $isSupported) {
                    return $header;
                }

                $hreflangUrls = $this->generateHreflangUrls($referenceType, $referenceId);

                Language::setSwitcherURLs($hreflangUrls);

                return $header . view('plugins/language::partials.hreflang', compact('hreflangUrls'))->render();
            }, 55);
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }

    protected function generateHreflangUrls(?string $referenceType, int|string|null $referenceId): array
    {
        $entries = [];
        $languageVariantCounts = $this->countLanguageVariants();
        $page = (int) request()->query('page');

        foreach (Language::getSupportedLocales() as $localeCode => $properties) {
            $hreflangCode = Language::formatLocaleForHrefLang($properties['lang_code']);
            $url = Language::getLocalizedURL($localeCode, url()->current(), [], false);

            $translatedUrl = $this->getTranslatedUrl($referenceType, $referenceId, $properties['lang_code'], $localeCode);

            if ($translatedUrl) {
                $url = $translatedUrl;
            }

            $url = rtrim($url, '/');

            // Preserve pagination so hreflang self-references the current paginated URL,
            // matching the canonical tag (see seo-helper MiscTags::addCanonical).
            if ($page > 1) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'page=' . $page;
            }

            if (str_contains($hreflangCode, '-')) {
                $languageOnly = explode('-', $hreflangCode)[0];

                if (($languageVariantCounts[$languageOnly] ?? 0) > 1) {
                    $entries[$hreflangCode] = $url;
                } else {
                    $entries[$languageOnly] = $url;
                }
            } else {
                $entries[$hreflangCode] = $url;
            }
        }

        return $entries;
    }

    protected function countLanguageVariants(): array
    {
        $counts = [];

        foreach (Language::getSupportedLocales() as $properties) {
            $hreflangCode = Language::formatLocaleForHrefLang($properties['lang_code']);

            if (str_contains($hreflangCode, '-')) {
                $languageOnly = explode('-', $hreflangCode)[0];
            } else {
                $languageOnly = $hreflangCode;
            }

            $counts[$languageOnly] = ($counts[$languageOnly] ?? 0) + 1;
        }

        return $counts;
    }

    protected function getTranslatedUrl(?string $referenceType, int|string|null $referenceId, string $langCode, string $localeCode): ?string
    {
        if (! $referenceType || ! $referenceId) {
            return null;
        }

        $currentLocaleCode = Language::getCurrentLocaleCode();
        $defaultLocale = Language::getDefaultLocale();

        if ($langCode === $currentLocaleCode) {
            return null;
        }

        if ($this->isLanguageAdvancedSupported($referenceType)) {
            return $this->getAdvancedTranslatedUrl($referenceType, $referenceId, $langCode, $localeCode, $defaultLocale);
        }

        return $this->getStandardTranslatedUrl($referenceType, $referenceId, $langCode, $localeCode, $defaultLocale);
    }

    protected ?Slug $cachedSlug = null;

    protected bool $slugCacheDone = false;

    protected function getAdvancedTranslatedUrl(string $referenceType, int|string $referenceId, string $langCode, string $localeCode, string $defaultLocale): ?string
    {
        if (! $this->slugCacheDone) {
            $this->slugCacheDone = true;
            $this->cachedSlug = Slug::query()
                ->where('reference_id', $referenceId)
                ->where('reference_type', $referenceType)
                ->select(['id', 'key', 'prefix', 'reference_id'])
                ->with('translations')
                ->first();
        }

        if (! $this->cachedSlug) {
            return null;
        }

        foreach ($this->cachedSlug->translations as $translation) {
            if ($translation->lang_code === $langCode) {
                $locale = Language::getLocaleByLocaleCode($translation->lang_code);

                if ($locale == $defaultLocale && Language::hideDefaultLocaleInURL()) {
                    $locale = null;
                }

                $basePrefix = $this->cachedSlug->getRawOriginal('prefix');
                $translatedPrefix = $translation->prefix ?? $basePrefix;

                return url($locale . ($translatedPrefix ? '/' . $translatedPrefix : '') . '/' . $translation->key);
            }
        }

        $locale = Language::getLocaleByLocaleCode($langCode);

        if ($locale == $defaultLocale && Language::hideDefaultLocaleInURL()) {
            $locale = null;
        }

        $prefix = $this->cachedSlug->getRawOriginal('prefix');
        $key = $this->cachedSlug->getRawOriginal('key');

        return url($locale . ($prefix ? '/' . $prefix : '') . '/' . $key);
    }

    protected ?array $cachedStandardSlugs = null;

    protected function getStandardTranslatedUrl(string $referenceType, int|string $referenceId, string $langCode, string $localeCode, string $defaultLocale): ?string
    {
        if ($this->cachedStandardSlugs === null) {
            $this->cachedStandardSlugs = [];

            $languageMetas = LanguageMeta::query()
                ->join('language_meta as meta', 'meta.lang_meta_origin', 'language_meta.lang_meta_origin')
                ->where([
                    'meta.reference_type' => $referenceType,
                    'meta.reference_id' => $referenceId,
                ])
                ->select(['language_meta.lang_meta_code', 'language_meta.reference_id'])
                ->get();

            if ($languageMetas->isNotEmpty()) {
                $slugs = Slug::query()
                    ->whereIn('reference_id', $languageMetas->pluck('reference_id'))
                    ->where('reference_type', $referenceType)
                    ->select(['key', 'prefix', 'reference_id'])
                    ->get()
                    ->keyBy('reference_id');

                foreach ($languageMetas as $meta) {
                    $slug = $slugs[$meta->reference_id] ?? null;

                    if ($slug) {
                        $this->cachedStandardSlugs[$meta->lang_meta_code] = $slug;
                    }
                }
            }
        }

        $slug = $this->cachedStandardSlugs[$langCode] ?? null;

        if (! $slug) {
            return null;
        }

        $locale = Language::getLocaleByLocaleCode($langCode);

        if ($locale == $defaultLocale && Language::hideDefaultLocaleInURL()) {
            $locale = null;
        }

        return url($locale . ($slug->prefix ? '/' . $slug->prefix : '') . '/' . $slug->key);
    }

    protected function isLanguageAdvancedSupported(string $referenceType): bool
    {
        if (! is_plugin_active('language-advanced') || ! class_exists(LanguageAdvancedManager::class)) {
            return false;
        }

        return LanguageAdvancedManager::isSupported($referenceType);
    }
}
