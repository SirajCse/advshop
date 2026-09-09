<?php

namespace Botble\Razorpay\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class RazorpayPaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(RAZORPAY_PAYMENT_METHOD_NAME)
            ->paymentName('Razorpay')
            ->paymentDescription(trans('plugins/razorpay::razorpay.payment_description', ['name' => 'Razorpay']))
            ->paymentLogo(url('vendor/core/plugins/razorpay/images/razorpay.svg'))
            ->paymentFeeField(RAZORPAY_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://razorpay.com')
            ->paymentInstructions(view('plugins/razorpay::instructions')->render())
            ->add(
                sprintf('payment_%s_key', RAZORPAY_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/razorpay::razorpay.key'))
                    ->placeholder('rzp_live_xxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/razorpay::razorpay.key_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('key', RAZORPAY_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_secret', RAZORPAY_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/razorpay::razorpay.secret'))
                    ->placeholder('xxxxxxxxxxxxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/razorpay::razorpay.secret_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('secret', RAZORPAY_PAYMENT_METHOD_NAME))
            )
            ->add(
                'payment_' . RAZORPAY_PAYMENT_METHOD_NAME . '_payment_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/razorpay::razorpay.payment_type'))
                    ->choices([
                        'hosted_checkout' => trans('plugins/razorpay::razorpay.payment_type_embedded'),
                        'website_embedded' => trans('plugins/razorpay::razorpay.payment_type_standard'),
                    ])
                    ->selected(get_payment_setting(
                        'payment_type',
                        RAZORPAY_PAYMENT_METHOD_NAME,
                        'hosted_checkout',
                    ))
            )
            ->add(
                sprintf('payment_%s_webhook_secret', RAZORPAY_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/razorpay::razorpay.webhook_secret'))
                    ->placeholder('whsec_xxxxxxxxxxxxxxxxxxxxxxxx')
                    ->helperText(trans('plugins/razorpay::razorpay.webhook_secret_helper'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('webhook_secret', RAZORPAY_PAYMENT_METHOD_NAME))
            )
            ->addAvailableCountriesField(RAZORPAY_PAYMENT_METHOD_NAME);
    }
}
