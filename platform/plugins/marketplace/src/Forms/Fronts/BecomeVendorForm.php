<?php

namespace Botble\Marketplace\Forms\Fronts;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\ButtonFieldOption;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\PhoneNumberFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\Fronts\BecomeVendorRequest;
use Botble\Theme\Facades\Theme;

class BecomeVendorForm extends FormAbstract
{
    public function setup(): void
    {
        Theme::asset()
            ->container('footer')
            ->add('marketplace-register', 'vendor/core/plugins/marketplace/js/customer-register.js', ['jquery']);

        $requireDocumentsForVerification = MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true);

        if ($requireDocumentsForVerification) {
            Theme::asset()
                ->add('dropzone', 'vendor/core/core/base/libraries/dropzone/dropzone.css');

            Theme::asset()
                ->container('footer')
                ->add('dropzone', 'vendor/core/core/base/libraries/dropzone/dropzone.js');
        }

        $this
            ->contentOnly()
            ->setValidatorClass(BecomeVendorRequest::class)
            ->formClass('become-vendor-form')
            ->setUrl(route('marketplace.vendor.become-vendor.post'))
            ->add(
                'is_vendor',
                'hidden',
                TextFieldOption::make()->value(1),
            )
            ->add(
                'shop_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.shop_name'))
                    ->placeholder(trans('plugins/marketplace::store.forms.shop_name_placeholder'))
                    ->required(),
            )
            ->add(
                'shop_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.shop_url'))
                    ->attributes([
                        'data-url' => route('public.ajax.check-store-url'),
                        'style' => 'direction: ltr; text-align: left;',
                    ])
                    ->placeholder(trans('plugins/marketplace::store.forms.shop_url_placeholder'))
                    ->wrapperAttributes(['class' => 'shop-url-wrapper mb-3 position-relative'])
                    ->prepend(
                        sprintf(
                            '<span class="position-absolute top-0 end-0 shop-url-status"></span><div class="input-group"><span class="input-group-text">%s</span>',
                            route('public.store', ['slug' => '/'])
                        )
                    )
                    ->append('</div>')
                    ->helperText(trans('plugins/marketplace::store.forms.shop_url_helper'))
                    ->required(),
            )
            ->add(
                'shop_phone',
                PhoneNumberField::class,
                PhoneNumberFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.shop_phone'))
                    ->placeholder(trans('plugins/marketplace::store.forms.shop_phone_placeholder'))
                    ->required()
                    ->withCountryCodeSelection(),
            )
            ->when($requireDocumentsForVerification, function (): void {
                $this
                    ->add(
                        'certificate_of_incorporation',
                        'html',
                        HtmlFieldOption::make()
                            ->label(trans('plugins/marketplace::marketplace.certificate_of_incorporation'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'certificate_file'])
                            ->content('<div id="certificate-dropzone" class="dropzone" data-placeholder="' . trans('plugins/marketplace::marketplace.drop_certificate_here') . '"></div>'),
                    )
                    ->add(
                        'government_id',
                        'html',
                        HtmlFieldOption::make()
                            ->label(trans('plugins/marketplace::marketplace.government_id'))
                            ->required()
                            ->wrapperAttributes(['class' => 'mb-3 position-relative', 'data-field-name' => 'government_id_file'])
                            ->attributes(['data-placeholder' => ''])
                            ->content('<div id="government-id-dropzone" class="dropzone" data-placeholder="' . trans('plugins/marketplace::marketplace.drop_government_id_here') . '"></div>'),
                    );
            })
            ->add(
                'agree_terms_and_policy',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->when(
                        $privacyPolicyUrl = MarketplaceHelper::getSetting('term_and_privacy_policy_url') ?: Theme::termAndPrivacyPolicyUrl(),
                        function (CheckboxFieldOption $fieldOption, string $url): void {
                            $fieldOption->label(trans('plugins/marketplace::marketplace.i_agree_to_terms', ['link' => Html::link($url, trans('plugins/marketplace::marketplace.terms_and_privacy_policy'), attributes: ['class' => 'text-decoration-underline', 'target' => '_blank'])]));
                        }
                    )
                    ->when(! $privacyPolicyUrl, function (CheckboxFieldOption $fieldOption): void {
                        $fieldOption->label(trans('plugins/marketplace::marketplace.i_agree_to_terms_simple'));
                    })
            )
            ->add(
                'submit',
                'submit',
                ButtonFieldOption::make()
                    ->label(trans('plugins/marketplace::marketplace.register'))
                    ->cssClass('btn btn-primary')
            );
    }
}
