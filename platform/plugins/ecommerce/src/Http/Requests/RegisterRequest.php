<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Requests\Concerns\HasPhoneFieldValidation;
use Botble\Base\Rules\EmailRule;
use Botble\Base\Rules\UniquePhoneRule;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class RegisterRequest extends Request
{
    use HasPhoneFieldValidation;

    protected function prepareForValidation(): void
    {
        $this->preparePhoneForValidation();
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120', 'min:2'],
            'email' => [
                'nullable',
                'string',
                Rule::requiredIf(! EcommerceHelper::isLoginUsingPhone()),
                new EmailRule(),
                Rule::unique((new Customer())->getTable()),
            ],
            'phone' => [
                'nullable',
                'string',
                Rule::requiredIf(EcommerceHelper::isLoginUsingPhone() || get_ecommerce_setting('make_customer_phone_number_required', false)),
                ...explode('|', BaseHelper::getPhoneValidationRule()),
                UniquePhoneRule::make(Customer::class),
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'agree_terms_and_policy' => ['sometimes', 'accepted:1'],
        ];

        return apply_filters('ecommerce_customer_registration_form_validation_rules', $rules);
    }

    public function attributes(): array
    {
        return apply_filters('ecommerce_customer_registration_form_validation_attributes', [
            'name' => __('Name'),
            'email' => __('Email'),
            'password' => __('Password'),
            'phone' => __('Phone'),
            'agree_terms_and_policy' => __('Term and Policy'),
        ]);
    }

    public function messages(): array
    {
        return apply_filters('ecommerce_customer_registration_form_validation_messages', []);
    }
}
