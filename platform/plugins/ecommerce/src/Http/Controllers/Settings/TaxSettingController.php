<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\TaxSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\TaxSettingRequest;

class TaxSettingController extends SettingController
{
    public function index()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.tax.name'));

        $form = TaxSettingForm::create();

        return view('plugins/ecommerce::settings.tax', compact('form'));
    }

    public function update(TaxSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
