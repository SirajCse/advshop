<?php

namespace Botble\SalePopup\Forms\Settings;

use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Language\Forms\Fields\LanguageSwitcherField;
use Botble\SalePopup\Http\Requests\Settings\SalePopupSettingRequest;
use Botble\SalePopup\Support\SalePopupHelper;
use Botble\Setting\Forms\SettingForm;

class SalePopupSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $salePopupHelper = app(SalePopupHelper::class);

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.sale_popup.name'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.sale_popup.description'))
            ->setValidatorClass(SalePopupSettingRequest::class)
            ->when($this->getRequest()->isNotFilled('ref_lang'), function (FormAbstract $form) use ($salePopupHelper): void {
                $form->add(
                    'enabled',
                    OnOffCheckboxField::class,
                    CheckboxFieldOption::make()
                        ->label(trans('plugins/sale-popup::sale-popup.enable'))
                        ->value($salePopupHelper->getSetting('enabled', true))
                        ->helperText(trans('plugins/sale-popup::sale-popup.enable_helper'))
                        ->attributes([
                            'data-bb-toggle' => 'collapse',
                            'data-bb-target' => '#sale-popup-setting',
                        ])
                );
            })
            ->add(
                'openWrapper',
                HtmlField::class,
                [
                    'html' => sprintf('<div id="sale-popup-setting" style="display: %s">', $salePopupHelper->getSetting('enabled', true) ? 'block' : 'none'),
                ]
            )
            ->when($this->getRequest()->isNotFilled('ref_lang'), function (FormAbstract $form) use ($salePopupHelper): void {
                $form
                    ->add(
                        'collection_id',
                        SelectField::class,
                        [
                            'label' => trans('plugins/sale-popup::sale-popup.load_products_from'),
                            'choices' => ['featured_products' => trans('plugins/sale-popup::sale-popup.featured_products')] +
                                get_product_collections()
                                    ->pluck('name', 'id')
                                    ->all(),
                            'selected' => $salePopupHelper->getSetting('collection_id'),
                            'help_block' => [
                                'text' => trans('plugins/sale-popup::sale-popup.collection_id_helper'),
                            ],
                        ]
                    )
                    ->add(
                        'show_time_ago_suggest',
                        OnOffCheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/sale-popup::sale-popup.show_time_ago_suggest'))
                            ->value($salePopupHelper->getSetting('show_time_ago_suggest', true))
                            ->helperText(trans('plugins/sale-popup::sale-popup.show_time_ago_suggest_helper'))
                    );
            })
            ->add(
                'purchased_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/sale-popup::sale-popup.purchased_text'))
                    ->required()
                    ->value($salePopupHelper->getSetting('purchased_text', 'purchased'))
                    ->helperText(trans('plugins/sale-popup::sale-popup.purchased_text_helper'))
            )
            ->add(
                'verified_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/sale-popup::sale-popup.verified_text'))
                    ->required()
                    ->value($salePopupHelper->getSetting('verified_text', 'Verified'))
                    ->helperText(trans('plugins/sale-popup::sale-popup.verified_text_helper'))
            )
            ->add(
                'quick_view_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/sale-popup::sale-popup.quick_view_text'))
                    ->required()
                    ->value($salePopupHelper->getSetting('quick_view_text', 'Quick view'))
                    ->helperText(trans('plugins/sale-popup::sale-popup.quick_view_text_helper'))
            )
            ->add(
                'list_users_purchased',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/sale-popup::sale-popup.list_users_purchased'))
                    ->required()
                    ->value($salePopupHelper->getSetting(
                        'list_users_purchased',
                        'Nathan (California) | Alex (Texas) | Henry (New York) | Kiti (Ohio) | Daniel (Washington) | Hau (California) | Van (Ohio) | Sara (Montana)  | Kate (Georgia)',
                    ))
                    ->attributes([
                        'rows' => 3,
                    ])
                    ->helperText(trans('plugins/sale-popup::sale-popup.user_separator'))
            )
            ->add(
                'list_sale_time',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/sale-popup::sale-popup.list_sale_time'))
                    ->required()
                    ->value($salePopupHelper->getSetting(
                        'list_sale_time',
                        '4 hours ago | 2 hours ago | 45 minutes ago | 1 day ago | 8 hours ago | 10 hours ago | 25 minutes ago | 2 day ago | 5 hours ago | 40 minutes ago',
                    ))
                    ->attributes([
                        'rows' => 3,
                    ])
                    ->helperText(trans('plugins/sale-popup::sale-popup.time_separator'))
            )
            ->when($this->getRequest()->isNotFilled('ref_lang'), function (FormAbstract $form) use ($salePopupHelper): void {
                $displayPages = json_decode($salePopupHelper->getSetting('display_pages', '["public.index"]'), true);

                $form
                    ->add(
                        'limit_products',
                        NumberField::class,
                        [
                            'label' => trans('plugins/sale-popup::sale-popup.limit_products'),
                            'value' => $salePopupHelper->getSetting('limit_products', 20),
                            'help_block' => [
                                'text' => trans('plugins/sale-popup::sale-popup.limit_products_helper'),
                            ],
                        ]
                    )
                    ->add(
                        'show_verified',
                        OnOffCheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/sale-popup::sale-popup.show_verified'))
                            ->value($salePopupHelper->getSetting('show_verified', true))
                            ->helperText(trans('plugins/sale-popup::sale-popup.show_verified_helper'))
                    )
                    ->add(
                        'show_close_button',
                        OnOffCheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/sale-popup::sale-popup.show_close_button'))
                            ->value($salePopupHelper->getSetting('show_close_button', true))
                            ->helperText(trans('plugins/sale-popup::sale-popup.show_close_button_helper'))
                    )
                    ->add(
                        'show_quick_view_button',
                        OnOffCheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/sale-popup::sale-popup.show_quick_view_button'))
                            ->value($salePopupHelper->getSetting('show_quick_view_button', true))
                            ->helperText(trans('plugins/sale-popup::sale-popup.show_quick_view_button_helper'))
                    )
                    ->add(
                        'show_on_mobile',
                        OnOffCheckboxField::class,
                        CheckboxFieldOption::make()
                            ->label(trans('plugins/sale-popup::sale-popup.show_on_mobile'))
                            ->value($salePopupHelper->getSetting('show_on_mobile', false))
                            ->helperText(trans('plugins/sale-popup::sale-popup.show_on_mobile_helper'))
                    )
                    ->add(
                        'display_pages[]',
                        MultiCheckListField::class,
                        [
                            'label' => trans('plugins/sale-popup::sale-popup.select_pages_to_display'),
                            'choices' => $salePopupHelper->displayPages(),
                            'value' => old('display_pages', $displayPages),
                            'inline' => true,
                        ]
                    );
            })
            ->add(
                'closeWrapper',
                HtmlField::class,
                [
                    'html' => '</div>',
                ]
            )
            ->when(
                is_plugin_active('language'),
                fn (FormAbstract $form) => $form->add('languageSwitcher', LanguageSwitcherField::class)
            );
    }
}
