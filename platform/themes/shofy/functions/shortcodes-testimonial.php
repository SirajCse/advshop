<?php

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\FieldOptions\UiSelectorFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\UiSelectorField;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\FieldOptions\ShortcodeTabsFieldOption;
use Botble\Shortcode\Forms\Fields\ShortcodeTabsField;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Shortcode\ShortcodeField;
use Botble\Testimonial\Models\Testimonial;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Arr;
use Theme\Shofy\Fields\FieldOptions\SliderAutoplayFieldOption;
use Theme\Shofy\Fields\SliderAutoplayField;

app()->booted(function (): void {
    if (! is_plugin_active('testimonial')) {
        return;
    }

    Shortcode::register(
        'testimonials',
        __('Testimonials'),
        __('Testimonials'),
        function (ShortcodeCompiler $shortcode) {
            // Check if we're using the tabs format with selection_type="tabs"
            if ($shortcode->selection_type === 'tabs') {
                // Handle both formats: getTabsData() and flat structure with testimonial_id_1, stars_1, etc.
                $testimonialTabs = Shortcode::fields()->getTabsData(['testimonial_id', 'stars'], $shortcode);

                // If no tabs data found, try to extract from flat structure
                if (empty($testimonialTabs)) {
                    $quantity = (int) $shortcode->quantity ?: 6;
                    $testimonialTabs = [];

                    for ($i = 1; $i <= $quantity; $i++) {
                        $testimonialId = $shortcode->{"testimonial_id_$i"} ?? null;
                        if ($testimonialId) {
                            $stars = $shortcode->{"stars_$i"} ?? null;
                            $testimonialTabs[] = [
                                'testimonial_id' => $testimonialId,
                                'stars' => $stars,
                            ];
                        }
                    }
                }

                if (! empty($testimonialTabs)) {
                    $testimonialIds = collect($testimonialTabs)->pluck('testimonial_id')->filter()->all();

                    if (empty($testimonialIds)) {
                        return null;
                    }

                    $allTestimonials = Testimonial::query()
                        ->wherePublished()
                        ->whereIn('id', $testimonialIds)
                        ->get()
                        ->keyBy('id');

                    if ($allTestimonials->isEmpty()) {
                        return null;
                    }

                    // Create a collection of testimonials with custom star ratings from tabs
                    $testimonials = collect();
                    foreach ($testimonialTabs as $tab) {
                        if (isset($tab['testimonial_id']) && $allTestimonials->has($tab['testimonial_id'])) {
                            $testimonial = clone $allTestimonials[$tab['testimonial_id']];

                            // Override stars if specified in the tab
                            if (isset($tab['stars']) && $tab['stars'] >= 1 && $tab['stars'] <= 5) {
                                $testimonial->shortcode_stars = (int) $tab['stars'];
                            }

                            $testimonials->push($testimonial);
                        }
                    }

                    if ($testimonials->isEmpty()) {
                        return null;
                    }

                    return Theme::partial('shortcodes.testimonials.index', compact('shortcode', 'testimonials'));
                }
            }

            // Fallback to the old format for backward compatibility
            $testimonialIds = Shortcode::fields()->getIds('testimonial_ids', $shortcode);

            if (empty($testimonialIds)) {
                return null;
            }

            $testimonials = Testimonial::query()
                ->wherePublished()
                ->whereIn('id', $testimonialIds)
                ->get();

            if ($testimonials->isEmpty()) {
                return null;
            }

            return Theme::partial('shortcodes.testimonials.index', compact('shortcode', 'testimonials'));
        }
    );

    Shortcode::setAdminConfig('testimonials', function (array $attributes) {
        $testimonials = Testimonial::query()
            ->wherePublished()
            ->select(['id', 'name', 'company'])
            ->get()
            ->mapWithKeys(fn (Testimonial $item) => [$item->getKey() => trim(sprintf('%s - %s', $item->name, $item->company), ' - ')]) // @phpstan-ignore-line
            ->all();

        $styles = [];

        foreach (range(1, 3) as $i) {
            $styles[$i] = [
                'label' => __('Style :number', ['number' => $i]),
                'image' => Theme::asset()->url("images/shortcodes/testimonials/style-$i.png"),
            ];
        }

        return ShortcodeForm::createFromArray($attributes)
            ->withLazyLoading()
            ->add(
                'style',
                UiSelectorField::class,
                UiSelectorFieldOption::make()
                    ->choices($styles)
                    ->selected(Arr::get($attributes, 'style', 1))
            )
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
            )
            ->add(
                'subtitle',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Subtitle'))
            )
            ->add(
                'selection_type',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(__('Selection Type'))
                    ->choices([
                        'list' => __('Select from list (old style)'),
                        'tabs' => __('Select individual testimonials with custom stars'),
                    ])
                    ->defaultValue(Arr::get($attributes, 'selection_type') ?: 'list')
                    ->attributes([
                        'data-bb-toggle' => 'collapse',
                        'data-bb-target' => '.testimonial-selection-type',
                    ])
            )
            ->add(
                'open_list_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(sprintf('<div class="testimonial-selection-type" data-bb-value="list" style="display: %s">', Arr::get($attributes, 'selection_type') !== 'tabs' ? 'block' : 'none'))
            )
            ->add(
                'testimonial_ids',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Testimonials'))
                    ->choices($testimonials)
                    ->multiple()
                    ->searchable()
                    ->selected(ShortcodeField::parseIds(Arr::get($attributes, 'testimonial_ids')))
            )
            ->add('close_list_wrapper', HtmlField::class, HtmlFieldOption::make()->content('</div>'))
            ->add(
                'open_tabs_wrapper',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(sprintf('<div class="testimonial-selection-type" data-bb-value="tabs" style="display: %s">', Arr::get($attributes, 'selection_type') === 'tabs' ? 'block' : 'none'))
            )
            ->add(
                'tabs',
                ShortcodeTabsField::class,
                ShortcodeTabsFieldOption::make()
                    ->fields([
                        'testimonial_id' => [
                            'type' => 'select',
                            'title' => __('Testimonial'),
                            'options' => $testimonials,
                            'required' => true,
                        ],
                        'stars' => [
                            'type' => 'select',
                            'title' => __('Stars'),
                            'options' => [
                                '' => __('Default (from testimonial)'),
                                '5' => '★★★★★ (5 ' . __('Stars') . ')',
                                '4' => '★★★★☆ (4 ' . __('Stars') . ')',
                                '3' => '★★★☆☆ (3 ' . __('Stars') . ')',
                                '2' => '★★☆☆☆ (2 ' . __('Stars') . ')',
                                '1' => '★☆☆☆☆ (1 ' . __('Star') . ')',
                            ],
                        ],
                    ])
                    ->attrs($attributes)
            )
            ->add('close_tabs_wrapper', HtmlField::class, HtmlFieldOption::make()->content('</div>'))
            ->add(
                'filled_color',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Fill yellow color to star icons'))
                    ->choices([
                        'no' => __('No'),
                        'yes' => __('Yes'),
                    ])
            )
            ->add(
                'slider_autoplay',
                SliderAutoplayField::class,
                SliderAutoplayFieldOption::make()
            );
    });
});
