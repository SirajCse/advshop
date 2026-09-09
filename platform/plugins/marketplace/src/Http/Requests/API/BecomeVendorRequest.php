<?php

namespace Botble\Marketplace\Http\Requests\API;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class BecomeVendorRequest extends Request
{
    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'min:2', 'max:200'],
            'shop_phone' => ['required', 'string', ...BaseHelper::getPhoneValidationRule(true)],
            'shop_url' => ['required', 'string', 'max:200', 'alpha_dash'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'shop_name' => [
                'description' => 'Store/shop name',
                'example' => 'John\'s Electronics',
            ],
            'shop_phone' => [
                'description' => 'Store contact phone',
                'example' => '+1234567890',
            ],
            'shop_url' => [
                'description' => 'Store URL slug (alphanumeric, dashes allowed)',
                'example' => 'johns-electronics',
            ],
        ];
    }
}
