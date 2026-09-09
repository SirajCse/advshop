<?php

namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Forms\Concerns\HasSubmitButton;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\ShipmentRequest;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\StoreLocator;

class ShipmentInfoForm extends FormAbstract
{
    use HasSubmitButton;

    public function setup(): void
    {
        $storeLocators = StoreLocator::query()
            ->where('is_shipping_location', true)
            ->pluck('name', 'id')
            ->all();

        $this
            ->model(Shipment::class)
            ->setValidatorClass(ShipmentRequest::class)
            ->contentOnly();

        if (count($storeLocators) > 1) {
            $this->add(
                'store_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.warehouse'))
                    ->choices($storeLocators)
            );
        }

        $this->add(
            'shipping_company_name',
            TextField::class,
            TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.shipping_company_name'))
                    ->placeholder(trans('plugins/ecommerce::shipping.shipping_company_name_placeholder'))
        )
            ->add(
                'tracking_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.tracking_id'))
                    ->placeholder(trans('plugins/ecommerce::shipping.tracking_id_placeholder'))
            )
            ->add(
                'tracking_link',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.tracking_link'))
                    ->placeholder(trans('plugins/ecommerce::shipping.tracking_link_placeholder'))
            )
            ->add(
                'estimate_date_shipped',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.estimate_date_shipped'))
            )
            ->add(
                'note',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/ecommerce::shipping.note'))
                    ->placeholder(trans('plugins/ecommerce::shipping.add_note'))
                    ->rows(3)
                    ->maxLength(10000)
            )
            ->addSubmitButton(trans('core/base::forms.save_and_continue'), 'ti ti-circle-check');
    }
}
