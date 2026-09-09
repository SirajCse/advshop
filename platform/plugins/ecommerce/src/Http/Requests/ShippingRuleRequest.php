<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShippingRuleRequest extends Request
{
    public function rules(): array
    {
        $ruleItems = [];

        foreach ($this->input('shipping_rule_items', []) as $key => $item) {
            $ruleItems['shipping_rule_items.' . $key . '.adjustment_price'] = 'required|numeric';
        }

        $ruleItems = [
            'name' => ['required', 'string', 'max:120'],
            'from' => ['required', 'numeric'],
            'to' => ['nullable', 'numeric', 'gt:from'],
            'price' => ['required', 'numeric'],
            'type' => Rule::in(array_keys(ShippingRuleTypeEnum::availableLabels())),
        ] + $ruleItems;

        if (request()->isMethod('POST')) {
            $ruleItems['shipping_id'] = [
                'required',
                Rule::exists('ec_shipping', 'id')->where(function ($query) {
                    if (in_array($this->input('type'), [
                        ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                        ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT,
                        ShippingRuleTypeEnum::BASED_ON_LOCATION,
                    ])) {
                        return $query->whereNotNull('country');
                    }

                    return $query;
                }),
            ];
        }

        return $ruleItems;
    }

    public function messages(): array
    {
        return [
            'shipping_id.exists' => trans('plugins/ecommerce::shipping.rule.cannot_create_rule_type_for_this_location', [
                'type' => ShippingRuleTypeEnum::getLabel($this->input('type')),
            ]),
        ];
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach ($this->input('shipping_rule_items', []) as $key => $item) {
            $attributes['shipping_rule_items.' . $key . '.adjustment_price'] = trans(
                'plugins/ecommerce::shipping.adjustment_price_of',
                $key
            );
        }

        return $attributes;
    }
}
