<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\ShippingRuleItemRequest;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Illuminate\Database\Eloquent\Builder;

class ShippingRuleItemForm extends FormAbstract
{
    use HasLocationFields;

    public function setup(): void
    {
        Assets::addScriptsDirectly(['vendor/core/plugins/ecommerce/js/shipping.js'])
            ->addScripts(['input-mask']);

        $rules = ShippingRule::query()
            ->whereIn('type', ShippingRuleTypeEnum::keysAllowRuleItems())
            ->whereHas('shipping', function (Builder $query): void {
                $query->whereNotNull('country');
            })
            ->get();

        $optionAttrs = [
            0 => [
                'data-country' => '',
            ],
        ];

        $country = $this->getModel() ? $this->getModel()->country : '';
        $shippingRuleId = 0;
        if (request()->ajax()) {
            if (! $this->getModel() && request()->input('shipping_rule_id')) {
                $shippingRuleId = request()->input('shipping_rule_id');
            } elseif ($this->getModel()) {
                $shippingRuleId = $this->getModel()->shippingRule->id;
            }
        }

        $country = old('country', $country);

        $choices = [];
        foreach ($rules as $rule) {
            $choices[$rule->id] = $rule->name . ' - ' . format_price(
                $rule->price
            ) . ' / ' . $rule->shipping->country_name;
            $optionAttrs[$rule->id] = ['data-country' => $rule->shipping->country];
            if ($shippingRuleId && $shippingRuleId == $rule->id) {
                $country = $rule->shipping->country;
            }
        }

        $choices = [0 => trans('plugins/ecommerce::shipping.rule.item.forms.no_shipping_rule')] + $choices;

        $isRequiredState = false;
        if ($shippingRuleId) {
            $rule = $rules->firstWhere('id', $shippingRuleId);
            if ($rule) {
                $isRequiredState = $rule->type->getValue() == ShippingRuleTypeEnum::BASED_ON_LOCATION;
            }
        }

        $this
            ->model(ShippingRuleItem::class)
            ->setValidatorClass(ShippingRuleItemRequest::class)
            ->add('shipping_rule_id', 'customSelect', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.shipping_rule'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control shipping-rule-id',
                ],
                'optionAttrs' => $optionAttrs,
                'choices' => $choices,
                'default_value' => $shippingRuleId,
                'wrapper' => [
                    'class' => $this->formHelper->getConfig(
                        'defaults.wrapper_class'
                    ) . ($shippingRuleId ? ' d-none' : ''),
                ],
            ])
            ->add(
                'name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.rule.item.forms.name'))
                    ->helperText(trans('plugins/ecommerce::shipping.rule.item.forms.name_helper'))
                    ->attributes([
                        'class' => 'form-control',
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.name_placeholder'),
                    ])
            );

        $isZipCodeType = false;
        if ($shippingRuleId) {
            $currentRule = $rules->firstWhere('id', $shippingRuleId);
            if ($currentRule) {
                $isZipCodeType = in_array($currentRule->type->getValue(), [
                    ShippingRuleTypeEnum::BASED_ON_ZIPCODE,
                    ShippingRuleTypeEnum::BASED_ON_ZIPCODE_AND_WEIGHT,
                ]);
            }
        }

        $this
            ->addLocationFields(
                countryAttributes: [
                    'selected' => $country,
                ],
                stateAttributes: [
                    'required' => $isRequiredState,
                ]
            )
            ->remove('address')
            ->add(
                'zip_code_from',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_from'))
                    ->attributes([
                        'class' => 'form-control',
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_from_placeholder'),
                    ])
                    ->wrapperAttributes([
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($isZipCodeType ? '' : ' d-none'),
                    ])
            )
            ->add(
                'zip_code_to',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_to'))
                    ->helperText(trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_to_helper'))
                    ->attributes([
                        'class' => 'form-control',
                        'placeholder' => trans('plugins/ecommerce::shipping.rule.item.forms.zip_code_to_placeholder'),
                    ])
                    ->wrapperAttributes([
                        'class' => $this->formHelper->getConfig('defaults.wrapper_class') . ($isZipCodeType ? '' : ' d-none'),
                    ])
            )
            ->add(
                'adjustment_price',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.rule.item.forms.adjustment_price'))
                    ->helperText(trans('plugins/ecommerce::shipping.rule.item.forms.adjustment_price_helper'))
                    ->attributes([
                        'class' => 'form-control input-mask-number',
                        'placeholder' => trans(
                            'plugins/ecommerce::shipping.rule.item.forms.adjustment_price_placeholder'
                        ),
                        'data-placeholder' => '',
                    ])
                    ->defaultValue(0)
            )
            ->add('is_enabled', 'onOff', [
                'label' => trans('plugins/ecommerce::shipping.rule.item.forms.is_enabled'),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'default_value' => '1',
            ])
            ->setBreakFieldPoint('is_enabled');
    }
}
