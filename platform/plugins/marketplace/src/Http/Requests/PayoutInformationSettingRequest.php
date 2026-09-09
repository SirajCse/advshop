<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Marketplace\Enums\PayoutPaymentMethodsEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Arr;

class PayoutInformationSettingRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $channel = $this->input('payout_payment_method');

        $this->merge(['bank_info' => [$channel => Arr::get($this->input('bank_info'), $channel)]]);
    }

    public function rules(): array
    {
        return PayoutPaymentMethodsEnum::getRules('bank_info');
    }

    public function attributes(): array
    {
        return array_merge([
            'bank_info' => trans('plugins/marketplace::withdrawal.payout_info'),
        ], PayoutPaymentMethodsEnum::getAttributes('bank_info'));
    }

    protected function passedValidation(): void
    {
        $channel = $this->input('payout_payment_method');

        $bankInfo = Arr::get($this->input('bank_info'), $channel);

        $this->merge(['bank_info' => BaseHelper::sanitizeUtf8($bankInfo)]);
    }
}
