<?php

namespace Botble\Setting\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class PhoneNumberSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'phone_number_enable_country_code' => [new OnOffRule()],
            'phone_number_available_countries' => ['nullable', 'array'],
            'phone_number_available_countries.*' => ['required', 'string'],
            'phone_number_min_length' => ['required', 'integer', 'min:1', 'max:20'],
            'phone_number_max_length' => ['required', 'integer', 'min:1', 'max:30', 'gte:phone_number_min_length'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_number_min_length' => trans('core/setting::setting.phone_number.min_length'),
            'phone_number_max_length' => trans('core/setting::setting.phone_number.max_length'),
        ];
    }
}
