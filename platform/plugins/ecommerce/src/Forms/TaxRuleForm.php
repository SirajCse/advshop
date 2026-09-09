<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Forms\Concerns\HasSubmitButton;
use Botble\Ecommerce\Http\Requests\TaxRuleRequest;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Models\TaxRule;
use Illuminate\Support\Facades\Request;

class TaxRuleForm extends FormAbstract
{
    use HasLocationFields;
    use HasSubmitButton;

    public function setup(): void
    {
        $this
            ->model(TaxRule::class)
            ->setValidatorClass(TaxRuleRequest::class)
            ->setFormOption('id', 'ecommerce-tax-rule-form')
            ->when(Request::ajax(), function (FormAbstract $form): void {
                $form->contentOnly();
            });

        if (! $this->getModel()->getKey()) {
            $this
                ->when(
                    $taxId = request()->input('tax_id'),
                    fn (FormAbstract $form) => $form->add('tax_id', 'hidden', [
                        'value' => $taxId,
                    ]),
                    function (FormAbstract $form): void {
                        $taxes = Tax::query()->pluck('title', 'id')->all();
                        $form
                            ->add('tax_id', 'customSelect', [
                                'label' => trans('plugins/ecommerce::tax.tax'),
                                'choices' => $taxes,
                                'empty_value' => trans('plugins/ecommerce::tax.rule.tax_placeholder'),
                                'help_block' => [
                                    'text' => trans('plugins/ecommerce::tax.rule.tax_helper'),
                                ],
                            ]);
                    }
                );
        }

        $this
            ->addLocationFields(
                countryAttributes: [
                    'required' => true,
                    'empty_value' => trans('plugins/ecommerce::tax.rule.country_placeholder'),
                    'help_block' => ['text' => trans('plugins/ecommerce::tax.rule.country_helper')],
                ],
                stateAttributes: [
                    'empty_value' => trans('plugins/ecommerce::tax.rule.state_placeholder'),
                    'help_block' => ['text' => trans('plugins/ecommerce::tax.rule.state_helper')],
                ],
                cityAttributes: [
                    'empty_value' => trans('plugins/ecommerce::tax.rule.city_placeholder'),
                    'help_block' => ['text' => trans('plugins/ecommerce::tax.rule.city_helper')],
                ],
            )
            ->remove('address')
            ->add(
                'percentage',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::tax.percentage'))
                    ->helperText(trans('plugins/ecommerce::tax.rule.percentage_helper'))
                    ->attributes([
                        'step' => '0.01',
                        'placeholder' => trans('plugins/ecommerce::tax.rule.percentage_placeholder'),
                    ])
                    ->required()
            )
            ->when($this->request->ajax(), function (TaxRuleForm $form): void {
                $form->addSubmitButton(trans('core/base::forms.save'), 'ti ti-device-floppy', [
                    'wrapper' => [
                        'class' => 'd-grid gap-2',
                    ],
                ]);
            });
    }
}
