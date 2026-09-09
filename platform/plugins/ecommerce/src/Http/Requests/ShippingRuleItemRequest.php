<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShippingRuleItemRequest extends Request
{
    protected function isBasedOnLocationRule(): bool
    {
        return ShippingRule::query()
            ->where([
                'id' => $this->input('shipping_rule_id'),
                'type' => ShippingRuleTypeEnum::BASED_ON_LOCATION,
            ])
            ->exists();
    }

    protected function isBasedOnZipCodeRule(): bool
    {
        return ShippingRule::query()
            ->where('id', $this->input('shipping_rule_id'))
            ->whereIn('type', [
                ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT,
            ])
            ->exists();
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('zip_code_from')) {
            $this->merge([
                'zip_code_from' => preg_replace('/\D/', '', $this->input('zip_code_from')),
            ]);
        }

        if ($this->filled('zip_code_to')) {
            $this->merge([
                'zip_code_to' => preg_replace('/\D/', '', $this->input('zip_code_to')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'shipping_rule_id' => [
                'required',
                Rule::exists(ShippingRule::class, 'id')->where(function ($query) {
                    return $query->whereIn('type', ShippingRuleTypeEnum::keysAllowRuleItems());
                }),
            ],
            'country' => ['required'],
            'state' => array_filter([
                'sometimes',
                Rule::requiredIf(function () {
                    return ShippingRule::query()
                        ->where([
                            'id' => $this->input('shipping_rule_id'),
                            'type' => ShippingRuleTypeEnum::BASED_ON_LOCATION,
                        ])
                        ->exists();
                }),
                $this->isBasedOnLocationRule() ? Rule::exists('states', 'id') : null,
            ]),
            'city' => array_filter([
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isBasedOnLocationRule() && ! $this->filled('state');
                }),
                ...(
                    EcommerceHelper::useCityFieldAsTextField() || ! $this->isBasedOnLocationRule()
                    ? []
                    : [Rule::exists('cities', 'id')]
                ),
            ]),
            'zip_code' => ['nullable', 'string', 'max:20'],
            'zip_code_from' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn () => $this->isBasedOnZipCodeRule()),
            ],
            'zip_code_to' => ['nullable', 'string', 'max:20'],
            'adjustment_price' => ['nullable', 'numeric', 'min:-100000000000', 'max:100000000000'],
            'is_enabled' => Rule::in(['0', '1']),
        ];
    }
}
