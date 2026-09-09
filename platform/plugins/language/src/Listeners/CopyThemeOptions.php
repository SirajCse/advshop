<?php

namespace Botble\Language\Listeners;

use Botble\Language\Events\LanguageCreated;
use Botble\Language\Listeners\Concerns\EnsureThemePackageExists;
use Botble\Setting\Models\Setting;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\ThemeOption;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class CopyThemeOptions
{
    use EnsureThemePackageExists;

    public function handle(LanguageCreated $event): void
    {
        if (! $this->determineIfThemesExists()) {
            return;
        }

        $fromTheme = setting('theme');

        if (! $fromTheme) {
            return;
        }

        $fromThemeKey = 'theme-' . $fromTheme . '-';
        $themeKey = 'theme-' . $fromTheme . '-' . $event->language->lang_code . '-';

        RenderingThemeOptionSettings::dispatch();

        $themeFields = Arr::get(ThemeOption::getFields(), 'theme', []);
        $existsThemeOptionKeys = array_keys($themeFields);

        $sharedFieldKeys = collect($themeFields)
            ->filter(fn (array $field) => ThemeOption::isFieldShared(Arr::get($field, 'id', '')))
            ->keys()
            ->all();

        $themeOptions = collect(ThemeOption::getOptions())
            ->filter(
                function (mixed $value, string $key) use ($existsThemeOptionKeys, $sharedFieldKeys, $fromThemeKey) {
                    $fieldKey = Str::after($key, $fromThemeKey);

                    return Str::startsWith($key, $fromThemeKey)
                        && in_array($fieldKey, $existsThemeOptionKeys, true)
                        && ! in_array($fieldKey, $sharedFieldKeys, true);
                }
            )
            ->toArray();

        if (empty($themeOptions)) {
            return;
        }

        $copiedThemeOptions = [];

        $now = Date::now();

        foreach ($themeOptions as $key => $option) {
            $key = str_replace($fromThemeKey, $themeKey, $key);

            $copiedThemeOptions[] = [
                'key' => $key,
                'value' => $option,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (count($copiedThemeOptions)) {
            Setting::query()
                ->insertOrIgnore($copiedThemeOptions);
        }
    }
}
