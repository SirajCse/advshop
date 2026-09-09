<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Botble\Theme\Facades\Theme;
use Botble\Widget\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HasWidgetSeeder
{
    /**
     * Seed widgets for all locales
     * Loads translations from database/seeders/translations/{locale}/widget.json
     *
     * @param array $locales
     */
    protected function seedWidgets(array $locales): void
    {
        $baseTheme = Theme::getThemeName();
        $baseWidgets = Widget::query()
            ->where('theme', $baseTheme)
            ->get();
        $useUuid = $this->widgetIdsUseUuid();
        $nextId = $useUuid ? null : (int) Widget::query()->max('id');
        $now = $this->now();

        foreach ($locales as $locale) {
            $translations = $this->loadWidgetTranslations($locale);
            if (empty($translations)) {
                continue;
            }

            $themeName = Widget::getThemeName($locale);

            $widgets = Widget::query()
                ->where('theme', $themeName)
                ->get();

            if ($widgets->isEmpty() && $baseWidgets->isNotEmpty()) {
                $clonedWidgets = $baseWidgets
                    ->map(function (Model $widget) use ($themeName, $useUuid, &$nextId, $now): array {
                        return [
                            'id' => $useUuid ? (string) Str::uuid() : ++$nextId,
                            'widget_id' => $widget->widget_id,
                            'sidebar_id' => $widget->sidebar_id,
                            'theme' => $themeName,
                            'position' => $widget->position,
                            'data' => json_encode(
                                $widget->data,
                                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                            ),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->all();

                if (! empty($clonedWidgets)) {
                    Widget::query()->insert($clonedWidgets);
                }

                $widgets = Widget::query()
                    ->where('theme', $themeName)
                    ->get();
            }

            if ($widgets->isEmpty()) {
                continue;
            }

            foreach ($widgets as $widget) {
                $data = $widget->data ?? [];
                $data = $this->applyWidgetTranslations($data, $translations, $locale);
                $widget->update(['data' => $data]);
            }
        }
    }

    /**
     * Load widget translations from JSON file
     *
     * @param string $locale
     * @return array
     */
    protected function loadWidgetTranslations(string $locale): array
    {
        $path = database_path("seeders/translations/{$locale}/widget.json");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        return $data ?: [];
    }

    /**
     * Check if widget table uses UUID for ID
     *
     * @return bool
     */
    protected function widgetIdsUseUuid(): bool
    {
        $column = DB::selectOne("SHOW COLUMNS FROM widgets WHERE Field = 'id'");

        if (! $column || ! isset($column->Type)) {
            return false;
        }

        $type = strtolower((string) $column->Type);

        return str_contains($type, 'char')
            || str_contains($type, 'binary')
            || str_contains($type, 'uuid');
    }

    /**
     * Apply translations to widget data
     *
     * @param array $data Widget data
     * @param array $translations Translations for the locale
     * @param string $locale Current locale
     * @return array Updated widget data
     */
    protected function applyWidgetTranslations(array $data, array $translations, string $locale): array
    {
        $translatableTopKeys = [
            'name',
            'title',
            'subtitle',
            'about',
            'content',
            'description',
            'button_label',
            'action_label',
        ];

        foreach ($translatableTopKeys as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = $this->translateValue($translations, $data[$key]);
            }
        }

        // Localize menu_id slug for translated widgets
        if (! empty($data['menu_id']) && is_string($data['menu_id'])) {
            $data['menu_id'] = sprintf('%s-%s', $data['menu_id'], $locale);
        }

        $translatableItemKeys = ['label', 'text', 'action_label', 'description', 'title'];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemIndex => $item) {
                foreach ($item as $fieldIndex => $field) {
                    $key = Arr::get($field, 'key');
                    $value = Arr::get($field, 'value');

                    if (! is_string($value)) {
                        continue;
                    }

                    if (in_array($key, $translatableItemKeys, true)) {
                        $data['items'][$itemIndex][$fieldIndex]['value'] = $this->translateValue(
                            $translations,
                            $value
                        );
                    }
                }
            }
        }

        return $data;
    }
}
