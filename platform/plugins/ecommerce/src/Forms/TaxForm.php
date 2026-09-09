<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Forms\Concerns\HasSubmitButton;
use Botble\Ecommerce\Http\Requests\TaxRequest;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Tables\TaxRuleTable;
use Illuminate\Support\Facades\Request;

class TaxForm extends FormAbstract
{
    use HasSubmitButton;

    public function setup(): void
    {
        $this
            ->model(Tax::class)
            ->setValidatorClass(TaxRequest::class)
            ->setFormOption('id', 'ecommerce-tax-form')
            ->when(Request::ajax(), function (FormAbstract $form): void {
                $form->contentOnly();
            })
            ->add('title', 'text', [
                'label' => trans('plugins/ecommerce::tax.title'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::tax.title_placeholder'),
                    'data-counter' => 120,
                ],
                'help_block' => [
                    'text' => trans('plugins/ecommerce::tax.title_helper'),
                ],
            ])
            ->add(
                'percentage',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::tax.percentage'))
                    ->helperText(trans('plugins/ecommerce::tax.percentage_helper'))
                    ->attributes([
                        'step' => '0.01',
                        'placeholder' => trans('plugins/ecommerce::tax.percentage_placeholder'),
                    ])
                    ->required()
            )
            ->add('priority', 'number', [
                'label' => trans('plugins/ecommerce::tax.priority'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/ecommerce::tax.priority_placeholder'),
                    'data-counter' => 120,
                ],
                'help_block' => [
                    'text' => trans('plugins/ecommerce::tax.priority_helper'),
                ],
            ])
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->when(Request::ajax(), function (TaxForm $form): void {
                $form->addSubmitButton(trans('core/base::forms.save'), 'ti ti-device-floppy', [
                    'wrapper' => [
                        'class' => 'd-grid gap-2',
                    ],
                ]);
            })
            ->when(! Request::ajax(), function (FormAbstract $form): void {
                $form->setBreakFieldPoint('status');
            })
            ->when(
                $this->getModel()->id && ! Request::ajax(),
                fn (FormAbstract $form) => $form->addMetaBoxes([
                    'tax_rules' => [
                        'title' => trans('plugins/ecommerce::tax.rule.name'),
                        'content' => app(TaxRuleTable::class)
                            ->setView('core/table::base-table')
                            ->setAjaxUrl(route('tax.rule.index', $this->getModel()->getKey() ?: 0))->renderTable(),
                        'has_table' => true,
                        'wrap' => true,
                    ],
                ])
            );
    }
}
