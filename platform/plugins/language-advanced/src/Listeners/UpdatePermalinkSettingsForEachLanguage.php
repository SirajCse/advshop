<?php

namespace Botble\LanguageAdvanced\Listeners;

use Botble\Slug\Events\UpdatedPermalinkSettings;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Facades\DB;

class UpdatePermalinkSettingsForEachLanguage
{
    public function handle(UpdatedPermalinkSettings $event): void
    {
        if (! $event->request->filled('ref_lang')) {
            return;
        }

        $langCode = $event->request->input('ref_lang');

        $slugsQuery = Slug::query()->where('reference_type', $event->reference);

        $missingSlugTranslations = (clone $slugsQuery)
            ->whereNotIn('id', function ($query) use ($langCode) {
                $query->select('slugs_id')
                    ->from('slugs_translations')
                    ->where('lang_code', $langCode);
            })
            ->select(['id', 'key'])
            ->get();

        if ($missingSlugTranslations->isNotEmpty()) {
            $rows = $missingSlugTranslations->map(fn ($slug) => [
                'slugs_id' => $slug->id,
                'lang_code' => $langCode,
                'key' => $slug->key,
                'prefix' => $event->prefix,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('slugs_translations')->insert($chunk);
            }
        }

        DB::table('slugs_translations')
            ->whereIn('slugs_id', (clone $slugsQuery)->select('id'))
            ->where('lang_code', $langCode)
            ->update(['prefix' => $event->prefix]);
    }
}
