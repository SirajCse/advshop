<?php

namespace Botble\LanguageAdvanced\Database\Seeders\Traits;

use Illuminate\Support\Facades\File;

trait HasTranslationLoader
{
    protected function loadTranslations(string $entity, string $locale): array
    {
        $path = $this->getTranslationPath($entity, $locale);

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "JSON decode error in {$path}: " . json_last_error_msg()
            );
        }

        return $decoded ?? [];
    }

    protected function loadAllTranslations(string $entity, array $locales): array
    {
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = $this->loadTranslations($entity, $locale);
        }

        return $translations;
    }

    protected function getTranslationPath(string $entity, string $locale): string
    {
        return database_path("seeders/translations/{$locale}/{$entity}.json");
    }

    protected function translationFileExists(string $entity, string $locale): bool
    {
        return File::exists($this->getTranslationPath($entity, $locale));
    }
}
