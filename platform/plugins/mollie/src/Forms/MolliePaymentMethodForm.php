<?php

namespace Botble\Mollie\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class MolliePaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(MOLLIE_PAYMENT_METHOD_NAME)
            ->paymentName('Mollie')
            ->paymentDescription(trans('plugins/mollie::mollie.payment_description', ['name' => 'Mollie']))
            ->paymentLogo(url('vendor/core/plugins/mollie/images/mollie.png'))
            ->paymentFeeField(MOLLIE_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://mollie.com')
            ->paymentInstructions(view('plugins/mollie::instructions')->render())
            ->add(
                sprintf('payment_%s_api_key', MOLLIE_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/mollie::mollie.api_key'))
                    ->placeholder('live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('api_key', MOLLIE_PAYMENT_METHOD_NAME))
                    ->helperText(trans('plugins/mollie::mollie.api_key_helper'))
            )
            ->add(
                sprintf('payment_%s_webhook_secret', MOLLIE_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/mollie::mollie.webhook_secret'))
                    ->placeholder('whsec_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('webhook_secret', MOLLIE_PAYMENT_METHOD_NAME))
                    ->helperText(trans('plugins/mollie::mollie.webhook_secret_helper'))
            )
            ->addAvailableCountriesField(MOLLIE_PAYMENT_METHOD_NAME);
    }
}
