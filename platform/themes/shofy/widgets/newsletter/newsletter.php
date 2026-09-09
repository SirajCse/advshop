<?php

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Newsletter\Forms\Fronts\NewsletterForm;
use Botble\Theme\Facades\Theme;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Collection;

class NewsletterWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Newsletter form'),
            'description' => __('Display Newsletter form on sidebar'),
            'title' => __('Subscribe our Newsletter'),
            'subtitle' => __('Sale 20% off all store'),
            'background_image' => null,
            'use_shape_images' => 1,
            'use_air_paper_icon_with_animation' => 1,
            'shape_1' => null,
            'shape_2' => null,
            'shape_3' => null,
            'shape_4' => null,
        ]);
    }

    protected function data(): array|Collection
    {
        $form = NewsletterForm::create()
            ->remove(['wrapper_before', 'wrapper_after'])
            ->addBefore(
                'email',
                'open_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<div class="tp-subscribe-input">')
            )
            ->addAfter(
                'submit',
                'close_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('</div>')
            )
            ->modify('submit', 'submit', [
                'attr' => [
                    'class' => '',
                ],
            ]);

        return compact('form');
    }

    protected function settingForm(): WidgetForm|string|null
    {
        $form = WidgetForm::createFromArray($this->getConfig())
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title')),
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle')),
            )
            ->add(
                'background_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Background image'))
                    ->helperText(__('Image size should be 1920x200px'))
            )
            ->add(
                'use_shape_images',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(__('Use shape images'))
                    ->defaultValue(1)
            );

        foreach (range(1, 4) as $i) {
            $form->add(
                sprintf('shape_%s', $i),
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Shape image :number', ['number' => $i]))
                    ->previewImage(Theme::asset()->url("images/newsletter/shape-$i.png"))
            );
        }

        return $form
            ->add(
                'use_air_paper_icon_with_animation',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(__('Use air paper icon with animation'))
                    ->defaultValue(1)
            );
    }

    protected function requiredPlugins(): array
    {
        return ['newsletter'];
    }
}
