<?php

namespace Botble\Shortcode\Forms;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Models\BaseModel;
use Botble\Shortcode\Compilers\ShortcodeCompiler;
use Botble\Shortcode\Forms\Fields\ShortcodeTabsField;

class ShortcodeForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(BaseModel::class)
            ->contentOnly()
            ->addCustomField('tabs', ShortcodeTabsField::class);
    }

    public function renderForm(array $options = [], bool $showStart = false, bool $showFields = true, bool $showEnd = false): string
    {
        return parent::renderForm($options, $showStart, $showFields, $showEnd);
    }

    public function withLazyLoading(bool $lazy = true): static
    {
        self::beforeRendering(function (self $form) use ($lazy) {
            if (! $lazy) {
                $form->remove('enable_lazy_loading');

                return $this;
            }

            $form
                ->remove('enable_lazy_loading')
                ->add(
                    'enable_lazy_loading',
                    SelectField::class,
                    SelectFieldOption::make()
                        ->label(trans('packages/shortcode::shortcode.form.enable_lazy_loading'))
                        ->choices([
                            'no' => trans('packages/shortcode::shortcode.form.no'),
                            'yes' => trans('packages/shortcode::shortcode.form.yes'),
                        ])
                        ->helperText(trans('packages/shortcode::shortcode.form.lazy_loading_helper'))
                );

            return $this;
        });

        return $this;
    }

    public function withCaching(bool $caching = true): static
    {
        self::beforeRendering(function (self $form) use ($caching) {
            if (! $caching) {
                $form->remove('enable_caching');

                return $this;
            }

            if (! setting('shortcode_cache_enabled', false)) {
                return $this;
            }

            $form
                ->remove('enable_caching')
                ->add(
                    'enable_caching',
                    SelectField::class,
                    SelectFieldOption::make()
                        ->label(trans('packages/shortcode::shortcode.form.enable_caching'))
                        ->choices([
                            'yes' => trans('packages/shortcode::shortcode.form.yes'),
                            'no' => trans('packages/shortcode::shortcode.form.no'),
                        ])
                        ->helperText(trans('packages/shortcode::shortcode.form.caching_helper'))
                );

            return $this;
        });

        return $this;
    }

    public function withCacheWarning(string $shortcodeName): static
    {
        if (! setting('shortcode_cache_enabled', false)) {
            return $this;
        }

        if (! app()->hasDebugModeEnabled()) {
            return $this;
        }

        self::beforeRendering(function (self $form) use ($shortcodeName) {
            if (in_array($shortcodeName, ShortcodeCompiler::getIgnoredCaches())) {
                $form
                    ->remove('enable_caching')
                    ->add(
                        'cache_warning',
                        AlertField::class,
                        AlertFieldOption::make()
                        ->type('warning')
                        ->content(trans('packages/shortcode::shortcode.form.cache_disabled_notice'))
                    );
            }

            return $this;
        });

        return $this;
    }

    public function withLazyLoadingWarning(string $shortcodeName): static
    {
        self::beforeRendering(function (self $form) use ($shortcodeName) {
            if (in_array($shortcodeName, ShortcodeCompiler::getIgnoredLazyLoading())) {
                $form
                    ->remove('enable_lazy_loading')
                    ->add(
                        'lazy_loading_warning',
                        AlertField::class,
                        AlertFieldOption::make()
                        ->type('warning')
                        ->content(trans('packages/shortcode::shortcode.form.lazy_loading_disabled_notice'))
                    );
            }

            return $this;
        });

        return $this;
    }

    public function withHtmlAttributes(string $defaultBackgroundColor = '#fff', ?string $defaultColor = null): static
    {
        return $this
            ->withBackgroundColor($defaultBackgroundColor)
            ->withBackgroundImage()
            ->withTextColor($defaultColor)
            ->withCustomCSS();
    }

    public function withCustomCSS(): static
    {
        return $this->add(
            'custom_css',
            TextareaField::class,
            TextareaFieldOption::make()
                ->label(trans('packages/shortcode::shortcode.form.custom_css'))
                ->helperText(trans('packages/shortcode::shortcode.form.custom_css_helper'))
                ->shared()
        );
    }

    public function withBackgroundColor(string $defaultColor = '#fff'): static
    {
        return $this->add(
            'background_color',
            ColorField::class,
            ColorFieldOption::make()
                ->label(trans('packages/shortcode::shortcode.form.background_color'))
                ->when($defaultColor, fn (ColorFieldOption $option) => $option->defaultValue($defaultColor))
                ->shared()
        );
    }

    public function withTextColor(?string $defaultColor = null): static
    {
        return $this->add(
            'text_color',
            ColorField::class,
            ColorFieldOption::make()
                ->label(trans('packages/shortcode::shortcode.form.text_color'))
                ->when($defaultColor, fn (ColorFieldOption $option) => $option->defaultValue($defaultColor))
                ->helperText(trans('packages/shortcode::shortcode.form.text_color_helper'))
                ->shared()
        );
    }

    public function withBackgroundImage(?string $defaultImage = null): static
    {
        return $this->add(
            'background_image',
            MediaImageField::class,
            MediaImageFieldOption::make()
                ->label(trans('packages/shortcode::shortcode.form.background_image'))
                ->when($defaultImage, fn (MediaImageFieldOption $option) => $option->defaultValue($defaultImage))
                ->shared()
        );
    }
}
