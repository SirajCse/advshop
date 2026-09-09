<?php

namespace Botble\Translation\Tables;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Base\Supports\Language;
use Botble\DataSynchronize\Table\HeaderActions\ExportHeaderAction;
use Botble\DataSynchronize\Table\HeaderActions\ImportHeaderAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\FormattedColumn;
use Botble\Translation\Services\GetGroupedTranslationsService;
use Botble\Translation\Tables\Concerns\HandlesTranslationTableFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TranslationTable extends TableAbstract
{
    use HandlesTranslationTableFilters;

    protected string $locale = 'en';

    public function setup(): void
    {
        parent::setup();

        $this->pageLength = 100;

        Assets::addScripts(['bootstrap-editable'])
            ->addStyles(['bootstrap-editable']);

        $this->useDefaultSorting = false;

        $this
            ->setView('core/table::base-table')
            ->addHeaderActions([
                ExportHeaderAction::make()->route('tools.data-synchronize.export.other-translations.index')->permission('other-translations.export'),
                ImportHeaderAction::make()->route('tools.data-synchronize.import.other-translations.index')->permission('other-translations.import'),
            ])
            ->onAjax(function () {
                $translations = $this->applyTranslationFilters(
                    (new GetGroupedTranslationsService())->handle()
                );

                return $this->toJson($this->table->of($translations));
            });
    }

    public function columns(): array
    {
        $service = new GetGroupedTranslationsService();

        return [
            FormattedColumn::make('group')
                ->title(trans('plugins/translation::translation.group'))
                ->alignStart()
                ->searchable(false)
                ->getValueUsing(function (FormattedColumn $column) use ($service) {
                    $item = $column->getItem();

                    return Html::tag(
                        'code',
                        $service->formatGroupLabel($item->group),
                        [
                            'data-bs-toggle' => 'tooltip',
                            'data-bs-original-title' => $item->group,
                        ]
                    );
                }),
            FormattedColumn::make('key')
                ->title(Arr::get(Language::getAvailableLocales(), 'en.name', 'en'))
                ->alignStart()
                ->searchable(false)
                ->getValueUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    return $this->formatKeyAndValue(is_array($item->value) ? $item->key : (string) $item->value);
                }),
            FormattedColumn::make('value')
                ->title(Arr::get(Language::getAvailableLocales(), "{$this->locale}.name", $this->locale))
                ->alignStart()
                ->getValueUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    $trans = trans(Str::of($item->group)->replaceLast(DIRECTORY_SEPARATOR, '::')->append(".$item->key")->toString(), [], $this->locale);

                    $value = $this->formatKeyAndValue(is_array($trans) ? $item->value : $trans);

                    return Html::link('#edit', $value, [
                        'class' => sprintf('editable locale-%s', $this->locale),
                        'data-locale' => $this->locale,
                        'data-name' => sprintf('%s|%s', $this->locale, $item->key),
                        'data-type' => 'textarea',
                        'data-pk' => $item->key,
                        'data-title' => trans('plugins/translation::translation.edit_title'),
                        'data-url' => route('translations.group.edit', ['group' => $item->group]),
                    ]);
                }),
        ];
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    protected function formatKeyAndValue(?string $value): ?string
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    public function htmlDrawCallbackFunction(): ?string
    {
        return parent::htmlDrawCallbackFunction() . 'Botble.initEditable()';
    }
}
