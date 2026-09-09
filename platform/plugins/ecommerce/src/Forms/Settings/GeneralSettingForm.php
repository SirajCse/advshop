<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\FieldOptions\RepeaterFieldOption;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\Settings\GeneralSettingRequest;
use Botble\Setting\Forms\SettingForm;

class GeneralSettingForm extends SettingForm
{
    use HasLocationFields;

    public function setup(): void
    {
        parent::setup();

        $adminNotificationEmail = get_ecommerce_setting('admin_notification_email');
        $adminNotificationEmail = $adminNotificationEmail
            ? (is_array($adminNotificationEmail) ? $adminNotificationEmail : json_decode($adminNotificationEmail, true))
            : [];
        $adminNotificationEmailValues = array_map(fn ($email) => [['value' => $email]], $adminNotificationEmail);

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.general.name'))
            ->setSectionDescription(trans('plugins/ecommerce::store-locator.description'))
            ->setValidatorClass(GeneralSettingRequest::class)
            ->columns(6)
            ->add(
                'store_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.store_name'))
                    ->placeholder(trans('plugins/ecommerce::setting.general.form.store_name_placeholder'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.store_name_helper'))
                    ->value(get_ecommerce_setting('store_name'))
                    ->colspan(3)
            )
            ->add(
                'store_company',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.store_company'))
                    ->placeholder(trans('plugins/ecommerce::setting.general.form.store_company_placeholder'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.store_company_helper'))
                    ->value(get_ecommerce_setting('store_company'))
                    ->colspan(3)
            )
            ->add(
                'store_phone',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.store_phone'))
                    ->placeholder(trans('plugins/ecommerce::setting.general.form.store_phone_placeholder'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.store_phone_helper'))
                    ->value(get_ecommerce_setting('store_phone'))
                    ->colspan(3)
            )
            ->add(
                'store_email',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.store_email'))
                    ->placeholder(trans('plugins/ecommerce::setting.general.form.store_email_placeholder'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.store_email_helper'))
                    ->value(get_ecommerce_setting('store_email'))
                    ->colspan(3)
            )
            ->add(
                'admin_notification_email[]',
                RepeaterField::class,
                RepeaterFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.admin_notification_email'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.admin_notification_email_helper'))
                    ->fields([
                        [
                            'type' => 'email',
                            'label' => trans('core/base::forms.email'),
                            'attributes' => [
                                'name' => 'value',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                    'placeholder' => trans('plugins/ecommerce::setting.general.form.admin_notification_email_placeholder'),
                                ],
                            ],
                        ],
                    ])
                    ->value($adminNotificationEmailValues)
                    ->colspan(6)
            )
            ->addLocationFields(
                countryAttributes: [
                    'name' => 'store_country',
                    'value' => get_ecommerce_setting('store_country'),
                ],
                stateAttributes: [
                    'name' => 'store_state',
                    'value' => get_ecommerce_setting('store_state'),
                ],
                cityAttributes: [
                    'name' => 'store_city',
                    'value' => get_ecommerce_setting('store_city'),
                ],
                addressAttributes: [
                    'name' => 'store_address',
                    'value' => get_ecommerce_setting('store_address'),
                ],
                zipCodeAttributes: [
                    'name' => 'store_zip_code',
                    'value' => get_ecommerce_setting('store_zip_code'),
                ]
            )
            ->add(
                'store_vat_number',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.general.form.store_vat_number'))
                    ->placeholder(trans('plugins/ecommerce::setting.general.form.store_vat_number_placeholder'))
                    ->helperText(trans('plugins/ecommerce::setting.general.form.store_vat_number_helper'))
                    ->value(get_ecommerce_setting('store_vat_number'))
                    ->colspan(EcommerceHelper::isUsingInMultipleCountries() && EcommerceHelper::isZipCodeEnabled() ? 3 : 2)
            );
    }
}
