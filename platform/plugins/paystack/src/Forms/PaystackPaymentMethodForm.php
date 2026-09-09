<?php

namespace Botble\Paystack\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class PaystackPaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(PAYSTACK_PAYMENT_METHOD_NAME)
            ->paymentName('Paystack')
            ->paymentDescription(trans('plugins/paystack::paystack.payment_description', ['name' => 'Paystack']))
            ->paymentLogo(url('vendor/core/plugins/paystack/images/paystack.png'))
            ->paymentFeeField(PAYSTACK_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://paystack.com')
            ->paymentInstructions(view('plugins/paystack::instructions')->render())
            ->add(
                sprintf('payment_%s_public', PAYSTACK_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/paystack::paystack.public_key'))
                    ->placeholder('pk_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/paystack::paystack.public_key_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('public', PAYSTACK_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_secret', PAYSTACK_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/paystack::paystack.secret_key'))
                    ->placeholder('sk_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/paystack::paystack.secret_key_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('secret', PAYSTACK_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_webhook_secret', PAYSTACK_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/paystack::paystack.webhook_secret'))
                    ->placeholder('whsec_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/paystack::paystack.webhook_secret_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('webhook_secret', PAYSTACK_PAYMENT_METHOD_NAME))
            )
            ->addAvailableCountriesField(PAYSTACK_PAYMENT_METHOD_NAME);
    }
}
