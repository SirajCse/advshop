<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Base\Models\BaseModel;
use Botble\Marketplace\Forms\Concerns\HasSubmitButton;
use Botble\Marketplace\Http\Requests\TaxInformationSettingRequest;
use Illuminate\Support\Arr;

class TaxInformationForm extends FormAbstract
{
    use HasSubmitButton;

    public function setup(): void
    {
        $customer = $this->getModel();

        $this
            ->model(BaseModel::class)
            ->setValidatorClass(TaxInformationSettingRequest::class)
            ->contentOnly()
            ->add('tax_info[business_name]', 'text', [
                'label' => trans('plugins/marketplace::marketplace.business_name'),
                'value' => Arr::get($customer->tax_info, 'business_name'),
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::marketplace.business_name'),
                ],
            ])
            ->add('tax_info[tax_id]', 'text', [
                'label' => trans('plugins/marketplace::marketplace.tax_id'),
                'value' => Arr::get($customer->tax_info, 'tax_id'),
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::marketplace.tax_id'),
                ],
            ])
            ->add('tax_info[address]', 'text', [
                'label' => trans('plugins/marketplace::marketplace.address'),
                'value' => Arr::get($customer->tax_info, 'address'),
                'attr' =>
                    ['placeholder' => trans('plugins/marketplace::marketplace.address'),
                ],
            ])
            ->addSubmitButton(trans('plugins/marketplace::marketplace.save_settings'));
    }
}
