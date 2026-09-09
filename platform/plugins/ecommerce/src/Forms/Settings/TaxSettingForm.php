<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\Settings\TaxSettingRequest;
use Botble\Setting\Forms\SettingForm;

class TaxSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.tax.tax_setting'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.tax.tax_setting_description'))
            ->contentOnly()
            ->setValidatorClass(TaxSettingRequest::class)
            ->add('ecommerce_tax_enabled', OnOffCheckboxField::class, [
                'label' => trans('plugins/ecommerce::setting.tax.form.enable_tax'),
                'value' => EcommerceHelper::isTaxEnabled(),
                'wrapper' => [
                    'class' => 'mb-0',
                ],
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.tax-settings',
                ],
            ])
            ->add('open_fieldset_tax_settings', HtmlField::class, [
                'html' => sprintf(
                    '<fieldset class="form-fieldset mt-3 tax-settings" style="display: %s;" data-bb-value="1">',
                    EcommerceHelper::isTaxEnabled() ? 'block' : 'none'
                ),
            ])
            ->add('tax_management_info', HtmlField::class, [
                'html' => sprintf(
                    '<div class="alert alert-info d-flex align-items-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                        <div>%s <a href="%s" class="alert-link">%s</a></div>
                    </div>',
                    trans('plugins/ecommerce::setting.tax.manage_taxes_info'),
                    route('tax.index'),
                    trans('plugins/ecommerce::setting.tax.go_to_taxes')
                ),
            ])
            ->add(
                'display_product_price_including_taxes',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.tax.form.display_product_price_including_taxes'))
                    ->value(EcommerceHelper::isDisplayProductIncludingTaxes())
                    ->helperText(trans('plugins/ecommerce::setting.tax.form.display_product_price_including_taxes_helper'))
            )
            ->add(
                'display_tax_fields_at_checkout_page',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.tax.form.display_company_invoice_information_fields_at_checkout_page'))
                    ->value(EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage())
                    ->helperText(trans('plugins/ecommerce::setting.tax.form.display_company_invoice_information_fields_at_checkout_page_helper'))
            )
            ->add(
                'display_checkout_tax_information',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.tax.form.display_checkout_tax_information'))
                    ->value(EcommerceHelper::isDisplayCheckoutTaxInformation())
                    ->helperText(trans('plugins/ecommerce::setting.tax.form.display_checkout_tax_information_helper'))
            )
            ->add(
                'display_item_tax_at_checkout',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.tax.form.display_item_tax_at_checkout'))
                    ->value(EcommerceHelper::isDisplayItemTaxAtCheckout())
                    ->helperText(trans('plugins/ecommerce::setting.tax.form.display_item_tax_at_checkout_helper'))
            )
            ->add('display_tax_description', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.tax.display_tax_description'),
                'value' => get_ecommerce_setting('display_tax_description', false),
                'help_block' => [
                    'text' => trans('plugins/ecommerce::setting.tax.display_tax_description_help'),
                ],
            ])
            ->add(
                'tax_on_shipping_fee',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.tax.form.tax_on_shipping_fee'))
                    ->value(EcommerceHelper::isTaxOnShippingFeeEnabled())
                    ->helperText(trans('plugins/ecommerce::setting.tax.form.tax_on_shipping_fee_helper'))
            )
            ->add('close_fieldset_tax_settings', 'html', [
                'html' => '</fieldset>',
            ]);
    }
}
