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
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('newsletter')) {
        return;
    }

    Shortcode::register('newsletter', __('Newsletter'), __('Newsletter form'), function (ShortcodeCompiler $shortcode) {
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

        return Theme::partial('shortcodes.newsletter.index', compact('shortcode', 'form'));
    });

    Shortcode::setAdminConfig('newsletter', function (array $attributes) {
        $form = ShortcodeForm::createFromArray($attributes)
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
    });
});
