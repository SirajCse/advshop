<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Http\Requests\Concerns\HasPhoneFieldValidation;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;

class AddressRequest extends Request
{
    use HasPhoneFieldValidation;

    protected function prepareForValidation(): void
    {
        $this->preparePhoneForValidation();

        if (! EcommerceHelper::isUsingInMultipleCountries()) {
            $this->merge(['country' => EcommerceHelper::getFirstCountryId()]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'is_default' => ['sometimes', 'boolean'],
        ];

        return array_merge($rules, EcommerceHelper::getCustomerAddressValidationRules());
    }
}
