<?php

namespace Botble\Translation\Tables\Concerns;

use Botble\Translation\Services\GetGroupedTranslationsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesTranslationTableFilters
{
    protected function applyTranslationFilters(Collection $translations): Collection
    {
        $request = $this->request();
        $service = new GetGroupedTranslationsService();

        $source = $this->normalizeFilterValue($request->query('source'));
        $module = $this->normalizeFilterValue($request->query('module'));
        $group = $this->normalizeFilterValue($request->query('group'));
        $status = $this->normalizeFilterValue($request->query('status'));
        $keyword = $this->normalizeFilterValue($request->input('search.value'))
            ?? $this->normalizeFilterValue($request->query('q'));

        if ($this->isFiltering()) {
            foreach ((array) $request->query('filter_columns', []) as $index => $column) {
                $operator = $request->input("filter_operators.$index");
                $value = $this->normalizeFilterValue($request->input("filter_values.$index"));

                if ($value === null || $operator !== '=') {
                    continue;
                }

                match ($column) {
                    'group' => $group ??= $value,
                    'source' => $source ??= $value,
                    'module' => $module ??= $value,
                    'status' => $status ??= $value,
                    default => null,
                };
            }
        }

        if ($source !== null) {
            $translations = $translations->filter(
                fn (array $item) => $service->getSourceOf($item['group']) === $source
            );
        }

        if ($module !== null) {
            $translations = $translations->filter(
                fn (array $item) => $service->getModuleOf($item['group']) === $module
            );
        }

        if ($group !== null) {
            $translations = $translations->filter(fn (array $item) => $item['group'] === $group);
        }

        if ($status === 'untranslated' && $this->locale !== 'en') {
            $translations = $translations->filter(function (array $item) {
                $key = Str::of($item['group'])
                    ->replaceLast(DIRECTORY_SEPARATOR, '::')
                    ->append('.' . $item['key'])
                    ->toString();

                return trans($key, [], $this->locale) === $item['value'];
            });
        }

        if ($keyword !== null) {
            $needle = $this->normalizeSearchTerm($keyword);

            $translations = $translations->filter(function (array $item) use ($needle, $service) {
                $haystacks = [
                    (string) $item['value'],
                    (string) $item['key'],
                    (string) $item['group'],
                    $service->formatGroupLabel((string) $item['group']),
                ];

                foreach ($haystacks as $haystack) {
                    if (str_contains($this->normalizeSearchTerm($haystack), $needle)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $translations;
    }

    protected function normalizeFilterValue(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeSearchTerm(string $value): string
    {
        return mb_strtolower(Str::ascii(trim($value)));
    }
}
