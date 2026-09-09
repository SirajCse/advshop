<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Helper;
use Botble\Setting\Forms\PhoneNumberSettingForm;
use Botble\Setting\Http\Requests\PhoneNumberSettingRequest;
use Illuminate\Support\Facades\Validator;

class PhoneNumberSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('core/setting::setting.phone_number.title'));

        return PhoneNumberSettingForm::create()->renderForm();
    }

    public function update(PhoneNumberSettingRequest $request): BaseHttpResponse
    {
        $validCountries = array_keys(Helper::countries());

        $validator = Validator::make([
            'phone_number_available_countries' => $request->input('phone_number_available_countries'),
        ], [
            'phone_number_available_countries' => ['nullable', 'array'],
            'phone_number_available_countries.*' => ['required', 'string', 'in:' . implode(',', $validCountries)],
        ], attributes: [
            'phone_number_available_countries.*' => 'Available countries',
        ]);

        if ($validator->fails()) {
            return $this
                ->httpResponse()
                ->setError()
                ->withInput()
                ->setMessage($validator->errors()->first());
        }

        $data = $request->validated();

        if (isset($data['phone_number_available_countries'])) {
            $data['phone_number_available_countries'] = json_encode($data['phone_number_available_countries']);
        }

        return $this->performUpdate($data);
    }
}
