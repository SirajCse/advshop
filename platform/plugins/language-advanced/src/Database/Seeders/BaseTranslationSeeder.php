<?php

namespace Botble\LanguageAdvanced\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasTranslationLoader;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class BaseTranslationSeeder extends BaseSeeder
{
    use HasTranslationLoader;

    /**
     * Seed translations for an entity from JSON files
     *
     * @param string $entity Table name (e.g., 'categories', 're_features')
     * @param string $tableName Translation table name (e.g., 'categories_translations')
     * @param string $modelClass Model class name
     * @param array $locales Array of locale codes
     * @param array $columns Translatable columns
     */
    protected function seedEntityFromJson(
        string $entity,
        string $tableName,
        string $modelClass,
        array $locales,
        array $columns = ['name']
    ): void {
        $translations = $this->loadAllTranslations($entity, $locales);

        if (empty($translations)) {
            return;
        }

        $sourceTable = (new $modelClass())->getTable();
        $existingColumns = DB::getSchemaBuilder()->getColumnListing($sourceTable);

        // Filter columns to only those that exist in source table
        $validColumns = array_filter($columns, fn ($col) => in_array($col, $existingColumns));

        if (empty($validColumns)) {
            return;
        }

        // Get all records from source table
        $records = DB::table($sourceTable)->get(['id', ...$validColumns]);

        if ($records->isEmpty()) {
            return;
        }

        $foreignKey = $sourceTable . '_id';

        foreach ($locales as $locale) {
            $localeTranslations = $translations[$locale] ?? [];

            if (empty($localeTranslations)) {
                continue;
            }

            foreach ($records as $record) {
                $data = [
                    'lang_code' => $locale,
                    $foreignKey => $record->id,
                ];

                // Find nested translation entry by matching any column value as key
                $nestedTranslation = null;

                foreach ($validColumns as $column) {
                    $originalValue = $record->{$column};

                    if ($originalValue === null) {
                        continue;
                    }

                    $match = $localeTranslations[$originalValue] ?? $localeTranslations[trim($originalValue)] ?? null;

                    if (is_array($match)) {
                        $nestedTranslation = $match;

                        break;
                    }
                }

                foreach ($validColumns as $column) {
                    $originalValue = $record->{$column};

                    if ($nestedTranslation && isset($nestedTranslation[$column])) {
                        $data[$column] = $nestedTranslation[$column];
                    } else {
                        $data[$column] = $this->translateValue($localeTranslations, $originalValue);
                    }
                }

                try {
                    DB::table($tableName)->updateOrInsert(
                        ['lang_code' => $locale, $foreignKey => $record->id],
                        $data
                    );
                } catch (\Throwable) {
                    // Skip if insert fails
                }
            }
        }
    }

    /**
     * Auto-seed all translatable models from JSON files
     *
     * @param array $locales Array of locale codes
     */
    protected function seedAllTranslatableModelsFromJson(array $locales): void
    {
        $models = array_unique(array_merge(
            LanguageAdvancedManager::supportedModels()
        ));

        // Tables that are handled manually by specific seeder methods
        $skipTables = $this->getSkippedTables();

        foreach ($models as $modelClass) {
            try {
                $model = new $modelClass();
                $tableName = $model->getTable();
                $translationTable = $tableName . '_translations';

                // Skip tables that have custom seeding logic
                if (in_array($tableName, $skipTables, true)) {
                    continue;
                }

                if (! Schema::hasTable($translationTable)) {
                    continue;
                }

                $columns = LanguageAdvancedManager::getTranslatableColumns($modelClass);

                if (empty($columns) || ! $this->translationFileExists($tableName, $locales[0])) {
                    continue;
                }

                $this->seedEntityFromJson(
                    entity: $tableName,
                    tableName: $translationTable,
                    modelClass: $modelClass,
                    locales: $locales,
                    columns: $columns
                );

                $this->command->info("✓ Seeded {$tableName} translations from JSON");
            } catch (\Throwable $e) {
                $this->command->warn("⚠ Skipped {$modelClass}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get tables that should be skipped from auto-seeding
     * Override this method in your seeder to skip specific tables
     *
     * @return array
     */
    protected function getSkippedTables(): array
    {
        return ['pages']; // Pages have custom seeding logic with nested JSON structure
    }

    /**
     * Translate a value using the translation array
     *
     * @param array $translations Translation key-value pairs
     * @param string|null $value Original value to translate
     * @return string|null Translated value or original if not found
     */
    protected function translateValue(array $translations, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        // Try exact match first, then trimmed value, then return original.
        // Skip nested-array matches (those are entity-level translation containers
        // handled elsewhere) — fall back to the original string instead.
        $candidate = $translations[$value] ?? $translations[$trimmed] ?? null;

        if (is_string($candidate)) {
            return $candidate;
        }

        return $value;
    }

    /**
     * Seed slug translations for entities that have translated names.
     * Reads translated names from *_translations tables and generates localized slugs.
     *
     * @param array $modelClasses Array of model class names to seed slug translations for
     * @param array $locales Array of locale codes
     */
    protected function seedSlugTranslations(array $modelClasses, array $locales): void
    {
        if (! Schema::hasTable('slugs') || ! Schema::hasTable('slugs_translations')) {
            return;
        }

        $turnOffLatin = SlugHelper::turnOffAutomaticUrlTranslationIntoLatin();

        foreach ($modelClasses as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $model = new $modelClass();
            $tableName = $model->getTable();
            $translationTable = $tableName . '_translations';
            $foreignKey = $tableName . '_id';

            if (! Schema::hasTable($translationTable)) {
                continue;
            }

            $slugs = DB::table('slugs')
                ->where('reference_type', $modelClass)
                ->get(['id', 'reference_id', 'prefix']);

            if ($slugs->isEmpty()) {
                continue;
            }

            $slugsByRefId = $slugs->keyBy('reference_id');

            foreach ($locales as $locale) {
                $translations = DB::table($translationTable)
                    ->where('lang_code', $locale)
                    ->get([$foreignKey, 'name']);

                foreach ($translations as $translation) {
                    $refId = $translation->{$foreignKey};
                    $slug = $slugsByRefId[$refId] ?? null;

                    if (! $slug || empty($translation->name)) {
                        continue;
                    }

                    $slugKey = $turnOffLatin
                        ? $translation->name
                        : Str::slug($translation->name);

                    if (empty($slugKey)) {
                        continue;
                    }

                    try {
                        DB::table('slugs_translations')->updateOrInsert(
                            [
                                'lang_code' => $locale,
                                'slugs_id' => $slug->id,
                            ],
                            [
                                'lang_code' => $locale,
                                'slugs_id' => $slug->id,
                                'key' => $slugKey,
                                'prefix' => $slug->prefix,
                            ]
                        );
                    } catch (\Throwable) {
                    }
                }
            }

            $this->command->info("✓ Seeded {$tableName} slug translations");
        }
    }
}
