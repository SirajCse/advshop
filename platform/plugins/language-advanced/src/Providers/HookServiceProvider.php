<?php

namespace Botble\LanguageAdvanced\Providers;

use Botble\Base\Contracts\BaseModel;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Supports\ServiceProvider;
use Botble\Language\Facades\Language;
use Botble\Language\Models\Language as LanguageModel;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Page\Models\Page;
use Botble\Slug\Models\Slug;
use Botble\Table\CollectionDataTable;
use Botble\Table\EloquentDataTable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        LanguageAdvancedManager::registerImportersAndExporters();

        if (LanguageAdvancedManager::isSupported(Page::class)) {
            LanguageAdvancedManager::registerTranslationImportExport(
                Page::class,
                fn () => trans('plugins/language-advanced::language-advanced.page_translations'),
                [
                    'import' => 'page-translations.import',
                    'export' => 'page-translations.export',
                ]
            );
        }

        $this->setLocaleFromRefLang();

        if (! $this->app->runningInConsole()) {
            add_action(BASE_ACTION_META_BOXES, [$this, 'addLanguageBox'], 1134, 2);
            add_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, [$this, 'addCurrentLanguageEditingAlert'], 1134, 3);
            add_action(BASE_ACTION_BEFORE_EDIT_CONTENT, [$this, 'getCurrentAdminLanguage'], 1134, 2);
            add_action(BASE_ACTION_META_BOXES, [$this, 'customizeMetaBoxes'], 10, 2);

            add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addLanguageTableHeading'], 1134, 2);
            add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addLanguageColumn'], 1134, 2);
            add_filter(BASE_FILTER_BEFORE_GET_FRONT_PAGE_ITEM, [$this, 'checkItemLanguageBeforeShow'], 1134, 2);
            add_filter(BASE_FILTER_BEFORE_GET_ADMIN_LIST_ITEM, [$this, 'checkItemLanguageBeforeGetAdminListItem'], 50, 2);
            add_filter('setting_permalink_meta_boxes', [$this, 'addPermalinkMetaBox'], 1134, 2);

            add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'changeFormDataBeforeRendering'], 1134);
            add_filter('page_visual_builder_content', [$this, 'getVisualBuilderContent'], 1134, 3);
            add_filter('page_visual_builder_save_content', [$this, 'saveVisualBuilderContent'], 1134, 4);
            add_filter('page_visual_builder_after_header', [$this, 'addVisualBuilderLanguageNotification'], 1134, 2);
            add_filter('page_visual_builder_header_actions', [$this, 'addVisualBuilderLanguageSwitcher'], 1134, 2);
        }

        add_filter('stored_meta_box_key', [$this, 'storeMetaBoxKey'], 1134, 2);
        add_filter('slug_helper_get_slug_query', [$this, 'getSlugQuery'], 1134, 2);
        add_filter('language_switcher_get_url', [$this, 'translateSlugSwitcherUrl'], 1134, 4);
        add_filter('slug_get_translated_slug', [$this, 'getTranslatedSlug'], 1134, 2);
        add_filter(['model_after_execute_get', 'model_after_execute_paginate'], function ($data, BaseModel $model) {
            if ($model instanceof LanguageModel) {
                return $data;
            }

            if (
                is_plugin_active('language') &&
                is_plugin_active('language-advanced') &&
                LanguageAdvancedManager::isSupported($model) &&
                Language::getCurrentLocaleCode() != Language::getDefaultLocaleCode()
            ) {
                $data->loadMissing('translations');
            }

            return $data;
        }, 1134, 2);
    }

    public function addLanguageBox(string $priority, array|Model|string|null $object = null): void
    {
        if (
            $priority == 'top' &&
            ! empty($object) &&
            $object instanceof Model &&
            $object->getKey() &&
            LanguageAdvancedManager::isSupported($object) &&
            Language::getActiveLanguage([
                'lang_code',
                'lang_flag',
                'lang_name',
            ])->count() > 1
        ) {
            MetaBox::addMetaBox(
                'language_advanced_wrap',
                trans('plugins/language::language.name'),
                [$this, 'languageMetaField'],
                $object::class,
                'top'
            );
        }
    }

    public function languageMetaField(): ?string
    {
        $languages = Language::getActiveLanguage([
            'lang_code',
            'lang_flag',
            'lang_name',
        ]);

        if ($languages->count() < 2) {
            return null;
        }

        $args = func_get_args();

        $currentLanguage = self::checkCurrentLanguage($languages);

        if (! $currentLanguage) {
            $currentLanguage = Language::getDefaultLanguage([
                'lang_flag',
                'lang_name',
                'lang_code',
            ]);
        }

        $route = $this->getRoutes();

        return view(
            'plugins/language-advanced::language-box',
            compact(
                'args',
                'languages',
                'currentLanguage',
                'route'
            )
        )->render();
    }

    public function checkCurrentLanguage(array|Collection $languages)
    {
        $referenceLanguage = Language::getRefLang();

        foreach ($languages as $language) {
            if (($referenceLanguage && $language->lang_code == $referenceLanguage) ||
                $language->lang_is_default
            ) {
                return $language;
            }
        }

        return null;
    }

    protected function getRoutes(): array
    {
        $currentRoute = implode('.', explode('.', Route::currentRouteName(), -1));

        return apply_filters(LANGUAGE_FILTER_ROUTE_ACTION, [
            'create' => $currentRoute . '.create',
            'edit' => $currentRoute . '.edit',
        ]);
    }

    public function addCurrentLanguageEditingAlert(Request $request, array|Model|string|null $data = null): void
    {
        $model = $data;
        if (is_object($data)) {
            $model = $data::class;
        }

        if ($data && LanguageAdvancedManager::isSupported($model) && Language::getActiveLanguage()->count() > 1) {
            $code = Language::getCurrentAdminLocaleCode();
            if (empty($code)) {
                $code = $this->getCurrentAdminLanguage($request, $data);
            }

            $language = null;
            if (! empty($code) && is_string($code)) {
                Language::setCurrentAdminLocale($code);
                LanguageAdvancedManager::clearLocaleCache();
                $language = LanguageModel::query()->where('lang_code', $code)->value('lang_name');
            }

            if (! $language) {
                $language = Language::getDefaultLanguage(['lang_name'])->lang_name;
            }

            echo view('plugins/language::partials.notification', compact('language'))->render();
        }
    }

    public function getCurrentAdminLanguage(Request $request, Model|string|null $data = null): ?string
    {
        $code = null;
        if ($refLang = Language::getRefLang()) {
            $code = $refLang;
        } elseif (! empty($data) && $data->getKey()) {
            $code = $data->languageMeta?->lang_meta_code;
        }

        if (empty($code) || ! is_string($code)) {
            $code = Language::getDefaultLocaleCode();
        }

        Language::setCurrentAdminLocale($code);
        LanguageAdvancedManager::clearLocaleCache();

        return $code;
    }

    public function languageSwitcher(array $options = []): string
    {
        return view('plugins/language::partials.switcher', compact('options'))->render();
    }

    public function addLanguageColumn(
        EloquentDataTable|CollectionDataTable $data,
        Model|string|null $model
    ): EloquentDataTable|CollectionDataTable {
        if (
            $model instanceof BaseModel &&
            LanguageAdvancedManager::isSupported($model) &&
            ($countLanguage = count(Language::getActiveLanguage())) &&
            $countLanguage > 1 &&
            $countLanguage < 4
        ) {
            $route = $this->getRoutes();

            if (is_in_admin() && Auth::guard()->check() && ! Auth::guard()->user()->hasAnyPermission($route)) {
                return $data;
            }

            return $data->addColumn('language', function ($item) use ($route) {
                $languages = Language::getActiveLanguage();

                return view('plugins/language-advanced::language-column', compact('item', 'route', 'languages'))
                    ->render();
            });
        }

        return $data;
    }

    public function addLanguageTableHeading(array $headings, Model|string|null $model): array
    {
        if (
            $model instanceof BaseModel &&
            LanguageAdvancedManager::isSupported($model) &&
            ($countLanguage = count(Language::getActiveLanguage())) &&
            $countLanguage > 1 &&
            $countLanguage < 4
        ) {
            if (is_in_admin() && Auth::guard()->check() && ! Auth::guard()->user()->hasAnyPermission($this->getRoutes())) {
                return $headings;
            }

            return array_merge($headings, Language::getTableHeading());
        }

        return $headings;
    }

    public function checkItemLanguageBeforeShow($query, Model|string|null $model): Builder|EloquentBuilder|Model
    {
        $currentLocale = Language::getCurrentLocaleCode();

        if ($currentLocale == Language::getDefaultLocaleCode()) {
            return $query;
        }

        return $this->getDataByCurrentLanguageCode($query, $model, Language::getCurrentLocaleCode());
    }

    public function checkItemLanguageBeforeGetAdminListItem(
        EloquentBuilder|Model $query,
        Model|string|null $model
    ): EloquentBuilder|Model {
        return $this->getDataByCurrentLanguageCode($query, $model, LanguageAdvancedManager::getTranslationLocale());
    }

    protected function getDataByCurrentLanguageCode(
        $query,
        Model|string|null $model,
        ?string $currentLocale
    ): Builder|EloquentBuilder|Model {
        if ($query instanceof Builder || $query instanceof EloquentBuilder) {
            $model = $query->getModel();
        }

        if (! LanguageAdvancedManager::isSupported($model) || ! $currentLocale) {
            return $query;
        }

        LanguageAdvancedManager::initModelRelations();

        return $query->with([
            'translations' => function ($query) use ($model, $currentLocale): void {
                $query->where($model->getTable() . '_translations' . '.lang_code', $currentLocale);
            },
        ]);
    }

    public function changeFormDataBeforeRendering(FormAbstract $form): FormAbstract
    {
        $model = $form->getModel();

        if (
            ! $model instanceof BaseModel
            || ! $model->getKey()
            || ! is_in_admin()
            || LanguageAdvancedManager::isDefaultLocale()
            || ! LanguageAdvancedManager::isSupported($model)) {
            return $form;
        }

        foreach ($form->getMetaBoxes() as $key => $metaBox) {
            if (LanguageAdvancedManager::isTranslatableMetaBox($key)) {
                continue;
            }

            $form->removeMetaBox($key);
        }

        $columns = LanguageAdvancedManager::getTranslatableColumns($model);

        $columns = [
            ...$columns,
            'submit',
            'save',
            'save_and_continue',
        ];

        foreach ($form->getFields() as $key => $field) {
            if (! in_array($key, $columns)) {
                $field = $form->getField($key);

                if ($field->getType() !== 'hidden') {
                    $form->remove($key);
                }
            }
        }

        // Reaching here guarantees a non-default locale (see early return above),
        // so a ref_lang query string is always appended.
        $refLang = '?ref_lang=' . LanguageAdvancedManager::getTranslationLocale();

        return $form
            ->setUrl(route('language-advanced.save', $model->getKey()) . $refLang)
            ->add('model', 'hidden', ['value' => $model::class])
            ->add('form', 'hidden', ['value' => $form::class]);
    }

    public function customizeMetaBoxes(string $context, array|string|Model|null $object = null): void
    {
        if (
            is_in_admin() &&
            ! LanguageAdvancedManager::isDefaultLocale() &&
            LanguageAdvancedManager::isSupported($object)
        ) {
            foreach (MetaBox::getMetaBoxes() as $reference => $metaBox) {
                foreach ($metaBox as $context => $position) {
                    foreach ($position as $item) {
                        foreach (array_keys($item) as $key) {
                            if (LanguageAdvancedManager::isTranslatableMetaBox($key)) {
                                continue;
                            }

                            MetaBox::removeMetaBox($key, $reference, $context);
                        }
                    }
                }
            }
        }
    }

    public function storeMetaBoxKey(string $key, Model|string|null $object): string
    {
        $translatableColumns = LanguageAdvancedManager::getTranslatableColumns($object);

        $translatableColumns[] = 'seo_meta';

        if (
            ! LanguageAdvancedManager::isDefaultLocale() &&
            in_array($key, $translatableColumns)
        ) {
            $key = LanguageAdvancedManager::getTranslationLocale() . '_' . $key;
        }

        return $key;
    }

    public function getSlugQuery(EloquentBuilder $query, array $condition = []): EloquentBuilder
    {
        try {
            return $query
                ->orWhereHas('translations', function (EloquentBuilder $query) use ($condition) {
                    return $query->where($condition);
                });
        } catch (Throwable) {
            return $query;
        }
    }

    protected ?object $cachedSlugRecord = null;

    protected ?Collection $cachedSlugTranslations = null;

    protected bool $slugLookupDone = false;

    public function translateSlugSwitcherUrl(string $url, string $localeCode, string $languageCode, $languageManager): string
    {
        try {
            if (! $this->slugLookupDone) {
                $this->resolveCurrentSlug();
            }

            if (! $this->cachedSlugRecord) {
                return $url;
            }

            // slugs_translations.lang_code stores the languages.lang_code value (e.g. "ru_RU"),
            // not the URL prefix (e.g. "ru"). Match the translation row by $languageCode so the
            // switcher resolves to the translated slug even when lang_code differs from lang_locale.
            if ($languageCode === Language::getDefaultLocaleCode()) {
                $targetPrefix = $this->cachedSlugRecord->prefix;
                $targetKey = $this->cachedSlugRecord->key;
            } else {
                $targetTranslation = $this->cachedSlugTranslations?->firstWhere('lang_code', $languageCode);

                if ($targetTranslation) {
                    $targetPrefix = $targetTranslation->prefix;
                    $targetKey = $targetTranslation->key;
                } else {
                    $targetPrefix = $this->cachedSlugRecord->prefix;
                    $targetKey = $this->cachedSlugRecord->key;
                }
            }

            $path = $targetPrefix ? $targetPrefix . '/' . $targetKey : $targetKey;

            $queryString = request()->getQueryString();

            $translatedUrl = $languageManager->getLocalizedURL($localeCode, '/' . $path, [], false);

            if ($queryString) {
                $translatedUrl .= '?' . $queryString;
            }

            return $translatedUrl;
        } catch (Throwable) {
            return $url;
        }
    }

    /**
     * Provide translated slug key and prefix for URL generation on non-default locales.
     * This ensures menu nodes, breadcrumbs, and other components that call $model->url
     * get the correctly translated slug instead of the default language slug.
     */
    protected array $slugTranslationCache = [];

    public function getTranslatedSlug(mixed $translatedSlug, mixed $slug): mixed
    {
        if (is_in_admin() || ! $slug instanceof Slug || ! $slug->id) {
            return $translatedSlug;
        }

        if (LanguageAdvancedManager::isDefaultLocale()) {
            return $translatedSlug;
        }

        $langCode = LanguageAdvancedManager::getTranslationLocale();

        if (! $langCode) {
            return $translatedSlug;
        }

        $cacheKey = $slug->id . '_' . $langCode;

        if (array_key_exists($cacheKey, $this->slugTranslationCache)) {
            return $this->slugTranslationCache[$cacheKey];
        }

        $translation = DB::table('slugs_translations')
            ->where('slugs_id', $slug->id)
            ->where('lang_code', $langCode)
            ->first();

        if (! $translation) {
            $this->slugTranslationCache[$cacheKey] = null;

            return $translatedSlug;
        }

        $result = [
            'key' => $translation->key ?: $slug->key, // ?: intentional — empty key is invalid, fall back to default
            'prefix' => $translation->prefix ?? $slug->prefix, // ?? intentional — empty prefix is valid (pages have no content-type prefix)
        ];

        $this->slugTranslationCache[$cacheKey] = $result;

        return $result;
    }

    protected function resolveCurrentSlug(): void
    {
        $this->slugLookupDone = true;

        $route = Route::current();

        if (! $route) {
            return;
        }

        $currentSlug = $route->parameter('slug');

        if (! $currentSlug) {
            return;
        }

        $currentPrefix = $route->parameter('prefix');
        $defaultLocaleCode = Language::getDefaultLocaleCode();
        $currentLocaleCode = Language::getCurrentLocaleCode();

        if ($currentLocaleCode === $defaultLocaleCode) {
            $query = DB::table('slugs')->where('key', $currentSlug);

            if ($currentPrefix) {
                $query->where('prefix', $currentPrefix);
            }

            $this->cachedSlugRecord = $query->first();
        } else {
            // slugs_translations.lang_code stores the languages.lang_code value (e.g. "ru_RU"),
            // not the URL prefix. Use getCurrentLocaleCode() so the lookup matches the column.
            $query = DB::table('slugs_translations')
                ->where('key', $currentSlug)
                ->where('lang_code', $currentLocaleCode);

            if ($currentPrefix) {
                $query->where('prefix', $currentPrefix);
            }

            $translation = $query->first();

            if ($translation) {
                $this->cachedSlugRecord = DB::table('slugs')->where('id', $translation->slugs_id)->first();
            }
        }

        if ($this->cachedSlugRecord) {
            $this->cachedSlugTranslations = DB::table('slugs_translations')
                ->where('slugs_id', $this->cachedSlugRecord->id)
                ->get();
        }
    }

    public function addPermalinkMetaBox(?string $data, array $params = []): string
    {
        $languages = Language::getActiveLanguage(['lang_id', 'lang_name', 'lang_code', 'lang_flag']);

        if ($languages->count() < 2) {
            return $data;
        }

        $route = 'slug.settings';

        return $data . view('plugins/language::partials.admin-list-language-chooser', compact('route', 'params', 'languages'))->render();
    }

    public function addVisualBuilderLanguageNotification(string $html, Model $page): string
    {
        $refLang = Language::getRefLang();

        if (! $refLang || $refLang === Language::getDefaultLocaleCode()) {
            return $html;
        }

        $language = LanguageModel::query()->where('lang_code', $refLang)->value('lang_name');

        if (! $language) {
            return $html;
        }

        return $html . view('plugins/language::partials.notification', compact('language'))->render();
    }

    public function addVisualBuilderLanguageSwitcher(string $html, Model $page): string
    {
        $languages = Language::getActiveLanguage(['lang_code', 'lang_flag', 'lang_name']);

        if ($languages->count() < 2) {
            return $html;
        }

        $currentLangCode = Language::getRefLang() ?: Language::getDefaultLocaleCode();
        $currentLanguage = $languages->firstWhere('lang_code', $currentLangCode);

        return $html . view(
            'plugins/language-advanced::visual-builder-language-switcher',
            compact('languages', 'currentLanguage', 'page')
        )->render();
    }

    public function getVisualBuilderContent(string $content, Model $page, Request $request): string
    {
        if (! LanguageAdvancedManager::isSupported($page)) {
            return $content;
        }

        $refLang = Language::getRefLang();

        if (! $refLang || $refLang === Language::getDefaultLocaleCode()) {
            return $content;
        }

        $table = $page->getTable() . '_translations';

        $translation = DB::table($table)
            ->where('lang_code', $refLang)
            ->where($page->getTable() . '_id', $page->getKey())
            ->value('content');

        return $translation ?? $content;
    }

    public function saveVisualBuilderContent(bool $saved, Model $page, string $content, Request $request): bool
    {
        $refLang = $request->input('ref_lang');

        if (! $refLang) {
            return false;
        }

        $defaultLocale = Language::getDefaultLocaleCode();

        if ($refLang === $defaultLocale) {
            return false;
        }

        $table = $page->getTable() . '_translations';

        DB::table($table)->updateOrInsert(
            [
                'lang_code' => $refLang,
                $page->getTable() . '_id' => $page->getKey(),
            ],
            [
                'content' => $content,
            ]
        );

        return true;
    }

    protected function setLocaleFromRefLang(): void
    {
        if (! is_plugin_active('language')) {
            return;
        }

        $request = request();
        $refLang = $request->input('ref_lang');

        if (! $refLang) {
            return;
        }

        $locale = Language::getLocaleByLocaleCode($refLang);

        if (! $locale) {
            return;
        }

        $path = $request->path();
        $isShortcodeAjax = str_contains($path, 'ajax/render-ui-blocks');
        $isVisualBuilderPreview = (bool) preg_match('#/pages/[^/]+/preview$#', $path);

        if (! $isShortcodeAjax && ! $isVisualBuilderPreview) {
            return;
        }

        app()->setLocale($locale);
        Language::setCurrentLocaleCode($refLang);
        LanguageAdvancedManager::clearLocaleCache();

        if ($isVisualBuilderPreview) {
            Language::setLocale($locale);
            Language::setCurrentLocale($locale);
        }
    }
}
