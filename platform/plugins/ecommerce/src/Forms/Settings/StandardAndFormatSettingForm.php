<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\LabelFieldOption;
use Botble\Base\Forms\Fields\LabelField;
use Botble\Ecommerce\Http\Requests\Settings\StandardAndFormatSettingRequest;
use Botble\Setting\Forms\SettingForm;

class StandardAndFormatSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/setting.js');

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.standard_and_format.name'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.standard_and_format.description'))
            ->setValidatorClass(StandardAndFormatSettingRequest::class)
            ->columns()
            ->add(
                'section_title',
                LabelField::class,
                LabelFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.standard_and_format.form.change_order_format'))
                    ->colspan(2)
            )
            ->add('section_subtitle', 'html', [
                'html' => sprintf(
                    '<p class="text-muted small">%s</p>',
                    trans('plugins/ecommerce::setting.standard_and_format.form.change_order_format_description', ['number' => get_ecommerce_setting('default_order_start_number', config('plugins.ecommerce.order.default_order_start_number'))])
                ),
                'colspan' => 2,
            ])
            ->add('store_order_prefix', 'text', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.start_with'),
                'value' => $prefix = get_ecommerce_setting('store_order_prefix'),
                'attr' => [
                    'placeholder' => 'DH',
                ],
                'group-flat' => true,
                'help_block' => [
                    'text' => trans('plugins/ecommerce::setting.standard_and_format.form.order_will_be_shown')
                        . sprintf(
                            '<span class="sample-order-code ms-1">#</span>' .
                            '<span class="sample-order-code-prefix">%s</span>%s' .
                            '<span class="sample-order-code-suffix">%s</span>',
                            $prefix ? $prefix . '-' : '',
                            get_ecommerce_setting('default_order_start_number', config('plugins.ecommerce.order.default_order_start_number')),
                            ($suffix = get_ecommerce_setting('store_order_suffix')) ? '-' . $suffix : '',
                        )
                    ,
                ],
            ])
            ->add('store_order_suffix', 'text', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.end_with'),
                'value' => $suffix,
                'attr' => [
                    'placeholder' => 'A',
                ],
            ])
            ->add('default_order_start_number', 'number', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.start_number'),
                'value' => get_ecommerce_setting('default_order_start_number', config('plugins.ecommerce.order.default_order_start_number')),
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                ],
                'help_block' => [
                    'text' => trans('plugins/ecommerce::setting.standard_and_format.form.start_number_description'),
                ],
            ])
            ->add('order_number_length', 'number', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.order_number_length'),
                'value' => get_ecommerce_setting('order_number_length', 8),
                'attr' => [
                    'min' => 1,
                    'max' => 20,
                    'step' => 1,
                ],
                'help_block' => [
                    'text' => trans('plugins/ecommerce::setting.standard_and_format.form.order_number_length_description'),
                ],
            ])
            ->add('store_weight_unit', 'customSelect', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.weight_unit'),
                'selected' => get_ecommerce_setting('store_weight_unit', 'g'),
                'choices' => [
                    'g' => trans('plugins/ecommerce::setting.standard_and_format.form.weight_unit_gram'),
                    'kg' => trans('plugins/ecommerce::setting.standard_and_format.form.weight_unit_kilogram'),
                    'lb' => trans('plugins/ecommerce::setting.standard_and_format.form.weight_unit_lb'),
                    'oz' => trans('plugins/ecommerce::setting.standard_and_format.form.weight_unit_oz'),
                ],
            ])
            ->add('store_width_height_unit', 'customSelect', [
                'label' => trans('plugins/ecommerce::setting.standard_and_format.form.height_unit'),
                'selected' => get_ecommerce_setting('store_width_height_unit', 'cm'),
                'choices' => [
                    'cm' => trans('plugins/ecommerce::setting.standard_and_format.form.height_unit_cm'),
                    'm' => trans('plugins/ecommerce::setting.standard_and_format.form.height_unit_m'),
                    'inch' => trans('plugins/ecommerce::setting.standard_and_format.form.height_unit_inch'),
                ],
            ]);

    }
}
