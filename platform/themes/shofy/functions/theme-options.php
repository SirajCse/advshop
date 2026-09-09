<?php

use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Facades\ThemeOption;
use Botble\Theme\ThemeOption\Fields\ColorField;
use Botble\Theme\ThemeOption\Fields\MediaImageField;
use Botble\Theme\ThemeOption\Fields\SelectField;
use Botble\Theme\ThemeOption\Fields\TextField;
use Botble\Theme\ThemeOption\Fields\ToggleField;
use Botble\Theme\ThemeOption\Fields\UiSelectorField;

app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
    $productItemStyles = [];
    $headerStyles = [];
    $sectionShapes = [];

    foreach (range(1, 5) as $i) {
        $productItemStyles[$i] = [
            'label' => __('Style :number', ['number' => $i]),
            'image' => Theme::asset()->url(sprintf('images/product-item-styles/product-item-%s.png', $i)),
        ];
    }

    foreach (range(1, 5) as $i) {
        $headerStyles[$i] = [
            'label' => __('Header :number', ['number' => $i]),
            'image' => Theme::asset()->url(sprintf('images/header-styles/header-%s.png', $i)),
        ];
    }

    foreach (range(1, 3) as $i) {
        $sectionShapes["style-$i"] = [
            'label' => __('Style :number', ['number' => $i]),
            'image' => Theme::asset()->url(sprintf('images/section-title-shape/style-%s.png', $i)),
        ];
    }

    ThemeOption::getFacadeRoot()
        ->setField(
            TextField::make()
                ->sectionId('opt-text-subsection-general')
                ->name('hotline')
                ->label(__('Hotline'))
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-styles')
                ->name('sticky_header_enabled')
                ->label(__('Enable sticky header'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-styles')
                ->name('sticky_header_mobile_enabled')
                ->label(__('Enable sticky header on mobile'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            UiSelectorField::make()
                ->sectionId('opt-text-subsection-styles')
                ->name('section_title_shape_decorated')
                ->label(__('Enable section title shape decorated'))
                ->numberItemsPerRow(4)
                ->options([
                    ...$sectionShapes,
                    'none' => [
                        'label' => __('None'),
                        'image' => Theme::asset()->url('images/section-title-shape/none.png'),
                    ],
                ])
                ->defaultValue('style-1')
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-styles')
                ->name('back_to_top_button_enabled')
                ->label(__('Enable back to top button'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            MediaImageField::make()
                ->sectionId('opt-text-subsection-logo')
                ->name('logo_light')
                ->label(__('Logo light'))
                ->shared()
        )
        ->setField(
            MediaImageField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_image')
                ->label(__('404 page image'))
                ->shared()
        )
        ->setField(
            TextField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_title')
                ->label(__('404 page title'))
                ->defaultValue('Oops! Page not found')
                ->shared()
        )
        ->setField(
            TextField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_description')
                ->label(__('404 page description'))
                ->defaultValue("The page you were looking for doesn't exist or has been moved.")
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_search_enabled')
                ->label(__('Show search bar on 404 page'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_categories_enabled')
                ->label(__('Show categories on 404 page'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_products_enabled')
                ->label(__('Show featured products on 404 page'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-page')
                ->name('404_page_posts_enabled')
                ->label(__('Show recent posts on 404 page'))
                ->defaultValue(true)
                ->shared()
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-blog')
                ->name('blog_posts_layout')
                ->label(__('Default blog posts layout'))
                ->options([
                    'list' => __('List'),
                    'grid' => __('Grid'),
                ])
                ->defaultValue('grid')
                ->shared()
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('enabled_header_categories_dropdown')
                ->label(__('Enable header categories dropdown?'))
                ->options([
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ])
                ->defaultValue('yes')
                ->shared()
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('enabled_header_categories_dropdown_on_mobile')
                ->label(__('Enable header categories dropdown on mobile?'))
                ->options([
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ])
                ->defaultValue('yes')
                ->shared()
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('enabled_mega_menu_in_product_categories_dropdown')
                ->label(__('Enable mega menu in product categories dropdown?'))
                ->options([
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ])
                ->defaultValue('yes')
                ->shared()
        )
        ->setField([
            'id' => 'ecommerce_products_page_layout',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Products listing page layout'),
            'attributes' => [
                'name' => 'ecommerce_products_page_layout',
                'list' => [
                    'left-sidebar' => __('Left sidebar'),
                    'right-sidebar' => __('Right sidebar'),
                    'no-sidebar' => __('No sidebar'),
                ],
            ],
        ])
        ->setField([
            'id' => 'ecommerce_product_item_layout',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Product item layout'),
            'attributes' => [
                'name' => 'ecommerce_product_item_layout',
                'list' => [
                    'grid' => __('Grid'),
                    'list' => __('List'),
                ],
            ],
        ])
        ->setField([
            'id' => 'ecommerce_product_item_style',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'uiSelector',
            'shared' => true,
            'label' => __('Product item style'),
            'attributes' => [
                'name' => 'ecommerce_product_item_style',
                'value' => 1,
                'choices' => $productItemStyles,
            ],
        ])
        ->setField([
            'id' => 'number_of_products_per_row',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Number of products per row'),
            'attributes' => [
                'name' => 'number_of_products_per_row',
                'list' => [
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                ],
                'value' => 4,
                'options' => [
                    'class' => 'form-select',
                ],
            ],
        ])
        ->setField([
            'id' => 'ecommerce_products_per_row_mobile',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Number of products per row on mobile'),
            'attributes' => [
                'name' => 'ecommerce_products_per_row_mobile',
                'list' => [
                    1 => 1,
                    2 => 2,
                ],
                'value' => 2,
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ])
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('ecommerce_products_pagination_type')
                ->label(__('Products listing pagination type'))
                ->options([
                    'default' => __('Default (Pagination)'),
                    'infinite' => __('Infinite scroll'),
                ])
                ->defaultValue('default')
                ->helperText(__('Choose how products are loaded on the listing page. Infinite scroll will automatically load more products as user scrolls down.'))
                ->shared()
        )
        ->setField([
            'id' => 'breadcrumb_style',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Breadcrumb style'),
            'attributes' => [
                'name' => 'breadcrumb_style',
                'list' => [
                    'none' => __('None'),
                    'align-start' => __('Align start'),
                    'align-center' => __('Align center'),
                    'without-title' => __('Without title'),
                ],
                'value' => 'align-start',
            ],
        ])
        ->setField([
            'id' => 'breadcrumb_hide_title',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Hide title?'),
            'attributes' => [
                'name' => 'breadcrumb_hide_title',
                'list' => [
                    'no' => __('No'),
                    'yes' => __('Yes'),
                ],
                'value' => 'no',
            ],
        ])
        ->setField([
            'id' => 'breadcrumb_background_color',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'customColor',
            'shared' => true,
            'label' => __('Breadcrumb background color'),
            'attributes' => [
                'name' => 'breadcrumb_background_color',
                'value' => 'rgba(245, 245, 245, 0)',
            ],
        ])
        ->setField([
            'id' => 'breadcrumb_background_image',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'mediaImage',
            'shared' => true,
            'label' => __('Breadcrumb background image'),
            'attributes' => [
                'name' => 'breadcrumb_background_image',
            ],
            'helper' => __('If you select an image, the background color will be ignored.'),
        ])
        ->setField([
            'id' => 'breadcrumb_height',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'number',
            'shared' => true,
            'label' => __('Breadcrumb height (px)'),
            'attributes' => [
                'name' => 'breadcrumb_height',
                'value' => null,
                'options' => [
                    'class' => 'form-control',
                ],
            ],
            'helper' => __('Leave empty to use default height.'),
        ])
        ->setField([
            'id' => 'breadcrumb_reduce_length_on_mobile',
            'section_id' => 'opt-text-subsection-breadcrumb',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Breadcrumb reduce length on mobile'),
            'attributes' => [
                'name' => 'breadcrumb_reduce_length_on_mobile',
                'list' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'value' => 'yes',
            ],
        ])
        ->setField(
            MediaImageField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('ecommerce_empty_cart_image')
                ->label(__('Empty cart image'))
                ->priority(90)
                ->shared()
        )
        ->setField([
            'id' => 'ecommerce_hide_rating_star_when_is_zero',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Hide rating star when is zero?'),
            'attributes' => [
                'name' => 'ecommerce_hide_rating_star_when_is_zero',
                'list' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'value' => 'no',
            ],
            'priority' => 999,
        ])
        ->setField([
            'id' => 'ecommerce_auto_open_mini_cart',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Auto open mini cart when product is added to cart?'),
            'attributes' => [
                'name' => 'ecommerce_auto_open_mini_cart',
                'list' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'value' => 'yes',
            ],
            'priority' => 1000,
        ])
        ->setField([
            'id' => 'ecommerce_product_name_lines',
            'section_id' => 'opt-text-subsection-ecommerce',
            'type' => 'customSelect',
            'shared' => true,
            'label' => __('Product name display lines'),
            'attributes' => [
                'name' => 'ecommerce_product_name_lines',
                'list' => [
                    '1' => __('1 line'),
                    '2' => __('2 lines'),
                ],
                'value' => '2',
            ],
            'priority' => 1001,
        ])
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('product_specification_display_style')
                ->label(__('Product specification display style'))
                ->options([
                    'table' => __('Table'),
                    'rows_columns' => __('Rows & Columns'),
                ])
                ->defaultValue('rows_columns')
                ->helperText(__('Choose how product specifications are displayed on the product detail page.'))
                ->shared()
        )
        ->setField(
            UiSelectorField::make()
                ->sectionId('opt-text-subsection-ecommerce')
                ->name('product_listing_review_style')
                ->label(__('Product listing review style'))
                ->defaultValue('default')
                ->numberItemsPerRow(2)
                ->withoutAspectRatio()
                ->options([
                    'default' => [
                        'label' => __('Default'),
                        'image' => Theme::asset()->url('images/product-listing-review/default.png'),
                    ],
                    'minimal' => [
                        'label' => __('Minimal'),
                        'image' => Theme::asset()->url('images/product-listing-review/minimal.png'),
                    ],
                ])
                ->shared()
        )
        ->setSection([
            'title' => __('Styles'),
            'id' => 'opt-text-subsection-styles',
            'subsection' => true,
            'icon' => 'ti ti-palette',
            'shared' => true,
            'fields' => [
                ColorField::make()
                    ->name('primary_color')
                    ->label(__('Primary color'))
                    ->defaultValue('#0989ff'),
                ColorField::make()
                    ->name('secondary_color')
                    ->label(__('Secondary color'))
                    ->defaultValue('#821f40'),
                ColorField::make()
                    ->name('header_top_background_color')
                    ->label(__('Header top background color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('header_top_text_color')
                    ->label(__('Header top text color'))
                    ->defaultValue('#fff'),
                ColorField::make()
                    ->name('header_main_background_color')
                    ->label(__('Header main background color'))
                    ->defaultValue('#fff'),
                ColorField::make()
                    ->name('header_main_text_color')
                    ->label(__('Header main text color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('header_main_text_hover_color')
                    ->label(__('Header main text hover color'))
                    ->defaultValue('#0989ff'),
                ColorField::make()
                    ->name('header_menu_background_color')
                    ->label(__('Header menu background color'))
                    ->helperText(__('This option is only applied for header style 1'))
                    ->defaultValue('#fff'),
                ColorField::make()
                    ->name('header_menu_text_color')
                    ->label(__('Header menu text color'))
                    ->helperText(__('This option is only applied for header style 1'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('header_menu_text_hover_color')
                    ->label(__('Header menu text hover color'))
                    ->helperText(__('This option is only applied for header style 1'))
                    ->defaultValue('#0989ff'),
                ColorField::make()
                    ->name('header_sticky_background_color')
                    ->label(__('Header sticky background color'))
                    ->defaultValue('#fff'),
                ColorField::make()
                    ->name('header_sticky_text_color')
                    ->label(__('Header sticky text color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('header_sticky_text_hover_color')
                    ->label(__('Header sticky text hover color'))
                    ->defaultValue('#0989ff'),
                SelectField::make()
                    ->name('header_sticky_logo_type')
                    ->label(__('Header sticky logo type'))
                    ->helperText(__('Choose which logo to display in sticky header. Use "Logo light" if your sticky header has a dark background, or "Default logo" if it has a light background.'))
                    ->options([
                        'default' => __('Default logo'),
                        'light' => __('Logo light'),
                    ])
                    ->defaultValue('default'),
                ColorField::make()
                    ->name('header_border_color')
                    ->label(__('Header border color'))
                    ->helperText(__('This option is only applied for header style 1'))
                    ->defaultValue('rgba(1, 15, 28, 0.1)'),
                UiSelectorField::make()
                    ->label(__('Header style'))
                    ->name('header_style')
                    ->numberItemsPerRow(1)
                    ->withoutAspectRatio()
                    ->options($headerStyles)
                    ->defaultValue(1),
                ColorField::make()
                    ->name('footer_background_color')
                    ->label(__('Footer background color'))
                    ->defaultValue('#fff'),
                ColorField::make()
                    ->name('footer_text_color')
                    ->label(__('Footer text color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('footer_title_color')
                    ->label(__('Footer title color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('footer_link_color')
                    ->label(__('Footer link color'))
                    ->defaultValue('#010f1c'),
                ColorField::make()
                    ->name('footer_link_hover_color')
                    ->label(__('Footer link hover color'))
                    ->defaultValue('#0989ff'),
                ColorField::make()
                    ->name('footer_border_color')
                    ->label(__('Footer border color'))
                    ->defaultValue('#e5e6e8'),
            ],
        ])
        ->setField(
            ToggleField::make()
                ->sectionId('opt-text-subsection-bottom-bar-menu')
                ->name('enabled_bottom_menu_bar_on_mobile')
                ->label(__('Enable bottom menu bar on mobile'))
                ->helperText(__('Show a fixed navigation bar at the bottom of the screen on mobile devices for easy access to main features'))
                ->defaultValue(true)
                ->priority(1)
                ->shared()
        )
        ->setSection([
            'title' => __('Bottom Bar Menu'),
            'id' => 'opt-text-subsection-bottom-bar-menu',
            'subsection' => true,
            'icon' => 'ti ti-category-2',
            'fields' => [
                [
                    'id' => 'bottom_bar_menu_show_text',
                    'type' => 'customSelect',
                    'shared' => true,
                    'label' => __('Show menu text'),
                    'attributes' => [
                        'name' => 'bottom_bar_menu_show_text',
                        'list' => [
                            'yes' => __('Yes'),
                            'no' => __('No'),
                        ],
                        'value' => 'yes',
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ],
                    'helper' => __('Display text labels below icons in the bottom menu bar. When disabled, only icons will be shown'),
                ],
                [
                    'id' => 'bottom_bar_menu_text_font_size',
                    'type' => 'number',
                    'shared' => true,
                    'label' => __('Menu text font size'),
                    'attributes' => [
                        'name' => 'bottom_bar_menu_text_font_size',
                        'value' => 13,
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ],
                    'helper' => __('Set the font size (in pixels) for text labels in the bottom menu bar'),
                ],
            ],
        ])
        ->setField([
            'id' => 'preloader_icon',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'mediaImage',
            'shared' => true,
            'label' => __('Preloader icon (optional)'),
            'attributes' => [
                'name' => 'preloader_icon',
            ],
            'helper' => __('Just used when preloader is enabled and version is Theme build-in'),
            'priority' => 45,
        ]);
});
