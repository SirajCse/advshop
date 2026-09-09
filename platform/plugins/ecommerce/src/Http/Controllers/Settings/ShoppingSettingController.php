<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\ShoppingSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\ShoppingSettingRequest;

class ShoppingSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.shopping.name'));

        return ShoppingSettingForm::create()->renderForm();
    }

    public function update(ShoppingSettingRequest $request)
    {
        $data = $request->validated();

        if (isset($data['payment_proof_payment_methods'])) {
            $data['payment_proof_payment_methods'] = json_encode($data['payment_proof_payment_methods']);
        }

        return $this->performUpdate($data);
    }
}
