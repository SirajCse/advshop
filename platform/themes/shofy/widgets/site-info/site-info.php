<?php

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Theme\Facades\Theme;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;

class SiteInfoWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Site information'),
            'description' => __('Widget display site information'),
            'logo' => null,
            'logo_height' => 35,
            'about' => null,
            'show_social_links' => true,
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Logo'))
                    ->defaultValue(Theme::getLogo())
                    ->helperText(__('Leave empty to use the default logo in Theme Options.'))
            )
            ->add(
                'logo_height',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(__('Logo height (default: 35px)'))
                    ->defaultValue(35)
            )
            ->add(
                'about',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(__('About'))
            )
            ->add(
                'show_social_links',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(__('Show social links'))
                    ->helperText(
                        __(
                            'Toggle to display or hide social links on your site. Configure the links in Theme Options -> Social Links.'
                        )
                    )
            );
    }

    public function data(): array
    {
        $height = $this->getConfig('logo_height') ?: theme_option('logo_height', 35);

        $attributes = [
            'style' => sprintf('max-height: %s', is_numeric($height) ? "{$height}px" : $height),
            'loading' => 'lazy',
        ];

        return compact('attributes');
    }
}
