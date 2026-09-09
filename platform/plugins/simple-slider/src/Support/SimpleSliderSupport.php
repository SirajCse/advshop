<?php

namespace Botble\SimpleSlider\Support;

use Botble\SimpleSlider\Forms\SimpleSliderItemForm;

class SimpleSliderSupport
{
    public static function registerResponsiveImageSizes(): void
    {
        SimpleSliderItemForm::extend(function (SimpleSliderItemForm $form) {
            $form
                ->addAfter('image', 'tablet_image', 'mediaImage', [
                    'label' => trans('plugins/simple-slider::simple-slider.tablet_image'),
                    'help_block' => [
                        'text' => trans('plugins/simple-slider::simple-slider.tablet_image_helper'),
                    ],
                    'metadata' => true,
                ])
                ->addAfter('tablet_image', 'mobile_image', 'mediaImage', [
                    'label' => trans('plugins/simple-slider::simple-slider.mobile_image'),
                    'help_block' => [
                        'text' => trans('plugins/simple-slider::simple-slider.mobile_image_helper'),
                    ],
                    'metadata' => true,
                ]);

            return $form;
        }, 127);
    }
}
