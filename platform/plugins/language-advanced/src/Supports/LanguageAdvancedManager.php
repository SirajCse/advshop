<?php

namespace Botble\LanguageAdvanced\Supports;

use Botble\Base\Contracts\BaseModel;
use Botble\Base\Facades\MacroableModels;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\DataSynchronize\PanelSections\ExportPanelSection;
use Botble\DataSynchronize\PanelSections\ImportPanelSection;
use Botble\Language\Facades\Language;
use Botble\LanguageAdvanced\Exporters\ModelTranslationExporter;
use Botble\LanguageAdvanced\Exporters\TranslationExporterManager;
use Botble\LanguageAdvanced\Importers\ModelTranslationImporter;
use Botble\LanguageAdvanced\Importers\TranslationImporterManager;
use Botble\LanguageAdvanced\Models\TranslationResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LanguageAdvancedManager
{
    protected const LOCALE_CACHE_KEY = '_language_advanced_locale';

    protected const IS_DEFAULT_CACHE_KEY = '_language_advanced_is_default';

    public static function clearLocaleCache(): void
    {
        $request = request();

        $request->attributes->remove(self::LOCALE_CACHE_KEY);
        $request->attributes->remove(self::IS_DEFAULT_CACHE_KEY);
    }

    public static function getTranslationLocale(): ?string
    {
        $request = request();

        if ($request->attributes->has(self::LOCALE_CACHE_KEY)) {
            return $request->attributes->get(self::LOCALE_CACHE_KEY);
        }

        $locale = is_in_admin()
            ? (Language::getCurrentAdminLocaleCode() ?: Language::getRefLang())
            : Language::getCurrentLocaleCode();

        $request->attributes->set(self::LOCALE_CACHE_KEY, $locale);

        return $locale;
    }

    public static function isDefaultLocale(): bool
    {
        $request = request();

        if ($request->attributes->has(self::IS_DEFAULT_CACHE_KEY)) {
            return $request->attributes->get(self::IS_DEFAULT_CACHE_KEY);
        }

        $locale = self::getTranslationLocale();
        $isDefault = $locale === null || $locale == Language::getDefaultLocaleCode();

        $request->attributes->set(self::IS_DEFAULT_CACHE_KEY, $isDefault);

        return $isDefault;
    }

    public static function save(?Model $object, Request $request): bool
    {
        if (! self::isSupported($object)) {
            return false;
        }

        $language = $request->input('language') ?: $request->header('X-LANGUAGE');

        // Whitelist user-supplied language codes against active languages.
        // Prevents arbitrary values (e.g., SQLi probes) from being stored as lang_code.
        if (! empty($language) && ! self::isValidLanguageCode($language)) {
            return false;
        }

        if (! $language) {
            $language = self::getTranslationLocale();
        }

        if (! $language) {
            return false;
        }

        $condition = [
            'lang_code' => $language,
            $object->getTable() . '_id' => $object->getKey(),
        ];

        $table = $object->getTable() . '_translations';

        $data = [];
        foreach (DB::getSchemaBuilder()->getColumnListing($table) as $column) {
            if (! in_array($column, array_keys($condition))) {
                $data[$column] = $request->input($column);
            }
        }

        $data = array_merge($data, $condition);

        $data = apply_filters('language_advanced_before_save', $data, $object, $request);

        $data = array_map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }, $data);

        $translate = DB::table($table)->where($condition)->exists();

        if ($translate) {
            DB::table($table)->where($condition)->update($data);
        } else {
            DB::table($table)->insert($data);
        }

        $defaultLocale = Language::getDefaultLocaleCode();

        if ($language != $defaultLocale) {
            $defaultTranslation = DB::table($table)
                ->where([
                    'lang_code' => $defaultLocale,
                    $object->getTable() . '_id' => $object->getKey(),
                ])
                ->first();

            if ($defaultTranslation) {
                foreach (DB::getSchemaBuilder()->getColumnListing($table) as $column) {
                    if (! in_array($column, array_keys($condition))) {
                        $object->{$column} = $defaultTranslation->{$column};
                    }
                }

                $object->save();
            }
        }

        return true;
    }

    public static function isSupported(BaseModel|Model|string|null $model): bool
    {
        if (! $model) {
            return false;
        }

        if (is_object($model)) {
            $model = get_class($model);
        }

        return in_array($model, self::supportedModels());
    }

    public static function getActiveLanguageCodes(): array
    {
        return Language::getActiveLanguage(['lang_code'])->pluck('lang_code')->all();
    }

    public static function isValidLanguageCode(mixed $code): bool
    {
        if (! is_string($code) || $code === '' || strlen($code) > 20) {
            return false;
        }

        return in_array($code, self::getActiveLanguageCodes(), true);
    }

    public static function supportedModels(): array
    {
        return array_keys(self::getSupported());
    }

    public static function getSupported(): array
    {
        return config('plugins.language-advanced.general.supported', []);
    }

    public static function getConfigs(): array
    {
        return config('plugins.language-advanced.general', []);
    }

    public static function getTranslatableColumns(BaseModel|Model|string|null $model): array
    {
        if (! $model) {
            return [];
        }

        if (is_object($model)) {
            $model = $model::class;
        }

        return Arr::get(self::getSupported(), $model, []);
    }

    public static function registerModule(string $model, array $columns): bool
    {
        config([
            'plugins.language-advanced.general.supported' => array_merge(self::getSupported(), [$model => $columns]),
        ]);

        return true;
    }

    public static function removeModule(array|string $model): void
    {
        foreach ((array) $model as $item) {
            $supported = self::getSupported();

            if (isset($supported[$item])) {
                unset($supported[$item]);
            }

            config(['plugins.language-advanced.general.supported' => $supported]);
        }
    }

    public static function delete(BaseModel|Model|string|null $object): bool
    {
        if (! self::isSupported($object)) {
            return false;
        }

        $table = $object->getTable() . '_translations';

        DB::table($table)->where([$object->getTable() . '_id' => $object->getKey()])->delete();

        return true;
    }

    public static function isTranslatableMetaBox(string $metaBoxKey): bool
    {
        return in_array($metaBoxKey, Arr::get(self::getConfigs(), 'translatable_meta_boxes', []));
    }

    public static function addTranslatableMetaBox(string $metaBoxKey): bool
    {
        $metaBoxes = array_merge(Arr::get(self::getConfigs(), 'translatable_meta_boxes', []), [$metaBoxKey]);

        config(['plugins.language-advanced.general.translatable_meta_boxes' => $metaBoxes]);

        return true;
    }

    public static function initModelRelations(): void
    {
        foreach (self::getSupported() as $item => $columns) {
            if (! class_exists($item)) {
                continue;
            }
            /**
             * @var Model $item
             */
            $item::resolveRelationUsing('translations', function ($model) {
                $instance = tap(
                    new TranslationResolver(),
                    function ($instance): void {
                        if (! $instance->getConnectionName()) {
                            $instance->setConnection(DB::getDefaultConnection());
                        }
                    }
                );

                $modelTable = $model->getTable();

                $instance->setTable($modelTable . '_translations');

                $instance->fillable(array_merge([
                    'lang_code',
                    $modelTable . '_id',
                ], self::getTranslatableColumns($model::class)));

                return new HasMany(
                    $instance->newQuery(),
                    $model,
                    $modelTable . '_translations.' . $modelTable . '_id',
                    $model->getKeyName()
                );
            });

            foreach ($columns as $column) {
                MacroableModels::addMacro(
                    $item,
                    'get' . ucfirst(Str::camel($column)) . 'Attribute',
                    function () use ($column) {
                        /**
                         * @var Model $model
                         */
                        $model = $this; // @phpstan-ignore-line

                        if (
                            ! $model->lang_code &&
                            ! LanguageAdvancedManager::isDefaultLocale() &&
                            $translation = $model->translations->where('lang_code', LanguageAdvancedManager::getTranslationLocale())->value($column)
                        ) {
                            return $translation;
                        }

                        return $model->getAttribute($column);
                    }
                );
            }
        }
    }

    public static function registerTranslationImportExport(
        string $modelClass,
        callable|string $name,
        array $permissions = [],
        int $priority = 100
    ): void {
        if (! self::isSupported($modelClass)) {
            return;
        }

        $importPermission = $permissions['import'] ?? $modelClass . '.import';
        $exportPermission = $permissions['export'] ?? $modelClass . '.export';

        $modelName = class_basename($modelClass);
        $itemKey = Str::kebab($modelName) . '-translations';

        PanelSectionManager::setGroupId('data-synchronize')
            ->beforeRendering(function () use (
                $name,
                $modelClass,
                $exportPermission,
                $importPermission,
                $itemKey,
                $priority
            ): void {
                $name = is_callable($name) ? $name() : $name;

                PanelSectionManager::default()
                    ->registerItem(
                        ImportPanelSection::class,
                        fn () => PanelSectionItem::make($itemKey)
                            ->setTitle($name)
                            ->withDescription(trans('plugins/language-advanced::language-advanced.import_description', ['name' => $name]))
                            ->withPriority($priority)
                            ->withPermission($importPermission)
                            ->withRoute('tools.data-synchronize.import.translations.index', ['type' => 'model', 'class' => $modelClass])
                    );

                PanelSectionManager::default()
                    ->registerItem(
                        ExportPanelSection::class,
                        fn () => PanelSectionItem::make($itemKey)
                            ->setTitle($name)
                            ->withDescription(trans('plugins/language-advanced::language-advanced.export_description', ['name' => $name]))
                            ->withPriority($priority)
                            ->withPermission($exportPermission)
                            ->withRoute('tools.data-synchronize.export.translations.index', ['type' => 'model', 'class' => $modelClass])
                    );
            });
    }

    public static function registerImportersAndExporters(): void
    {
        $importerManager = App::make(TranslationImporterManager::class);

        $exporterManager = App::make(TranslationExporterManager::class);

        $importerManager->register('model', ModelTranslationImporter::class);
        $exporterManager->register('model', ModelTranslationExporter::class);
    }
}
