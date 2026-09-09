<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\PendingOrderSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\PendingOrderSettingRequest;

class PendingOrderSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.pending_orders.name'));

        return PendingOrderSettingForm::create()->renderForm();
    }

    public function update(PendingOrderSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
