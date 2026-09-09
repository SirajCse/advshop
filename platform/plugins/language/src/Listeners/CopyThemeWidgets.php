<?php

namespace Botble\Language\Listeners;

use Botble\Language\Events\LanguageCreated;
use Botble\Language\Listeners\Concerns\EnsureThemePackageExists;
use Botble\Widget\Models\Widget;

class CopyThemeWidgets
{
    use EnsureThemePackageExists;

    public function handle(LanguageCreated $event): void
    {
        if (! $this->determineIfThemesExists()) {
            return;
        }

        if (! class_exists(Widget::class)) {
            return;
        }

        $theme = setting('theme');

        if (! $theme) {
            return;
        }

        $newTheme = $theme . '-' . $event->language->lang_code;

        $existingWidgetsCount = Widget::query()
            ->where('theme', $newTheme)
            ->count();

        if ($existingWidgetsCount > 0) {
            return;
        }

        $widgets = Widget::query()
            ->where('theme', $theme)
            ->get();

        foreach ($widgets as $widget) {
            $newWidget = $widget->replicate();
            $newWidget->theme = $newTheme;
            $newWidget->save();
        }
    }
}
