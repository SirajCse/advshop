<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\AbandonedCartSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\AbandonedCartSettingRequest;

class AbandonedCartSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.abandoned_cart.name'));

        return AbandonedCartSettingForm::create()->renderForm();
    }

    public function update(AbandonedCartSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
