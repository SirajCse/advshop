<?php

namespace Botble\Marketplace\Http\Requests\API;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class RegisterVendorRequest extends Request
{
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'last_name' => ['nullable', 'required_without:name', 'string', 'max:120', 'min:2'],
            'name' => ['nullable', 'required_without:first_name', 'string', 'max:120', 'min:2'],
            'email' => ['required', 'email', Rule::unique(Customer::class), 'max:60', 'min:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', ...BaseHelper::getPhoneValidationRule(true)],
            'shop_name' => ['required', 'string', 'min:2', 'max:200'],
            'shop_phone' => ['required', 'string', ...BaseHelper::getPhoneValidationRule(true)],
            'shop_url' => ['required', 'string', 'max:200', 'alpha_dash'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'first_name' => [
                'description' => 'Customer first name (required if name not provided)',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'Customer last name (required if name not provided)',
                'example' => 'Doe',
            ],
            'name' => [
                'description' => 'Customer full name (can be used instead of first_name/last_name)',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'Customer email address',
                'example' => 'john@example.com',
            ],
            'password' => [
                'description' => 'Account password (min 6 characters)',
                'example' => 'secret123',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation (must match password)',
                'example' => 'secret123',
            ],
            'phone' => [
                'description' => 'Customer phone (optional)',
                'example' => '+1234567890',
            ],
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
