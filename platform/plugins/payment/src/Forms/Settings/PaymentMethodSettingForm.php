<?php

namespace Botble\Payment\Forms\Settings;

use Botble\Base\Facades\Assets;
use Botble\Payment\Http\Requests\Settings\PaymentMethodSettingRequest;
use Botble\Setting\Forms\SettingForm;

class PaymentMethodSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        Assets::addStylesDirectly('vendor/core/plugins/payment/css/payment-setting.css');

        $this
            ->contentOnly()
            ->setSectionTitle(trans('plugins/payment::payment.payment_methods'))
            ->setSectionDescription(trans('plugins/payment::payment.payment_methods_description'))
            ->setValidatorClass(PaymentMethodSettingRequest::class);
    }
}
