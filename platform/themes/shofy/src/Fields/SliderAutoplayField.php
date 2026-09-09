<?php

namespace Theme\Shofy\Fields;

use Botble\Base\Forms\Form;
use Botble\Base\Forms\FormField;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Theme\Shofy\Helpers\SliderAutoplayHelper;

class SliderAutoplayField extends FormField
{
    protected array $autoplayKeys = [];

    protected $collapsibleCondition = null;

    protected string $collapsibleField = 'style';

    public function __construct($name, $type, Form $parent, array $options = [])
    {
        parent::__construct($name, $type, $parent);

        $default = [
            'is_autoplay' => 'is_autoplay',
            'autoplay_speed' => 'autoplay_speed',
            'is_loop' => 'is_loop',
        ];

        $this->autoplayKeys = array_filter(array_merge($default, Arr::get($options, 'autoplayKeys', [])));
        $this->collapsibleCondition = Arr::get($options, 'collapsibleCondition');
        $this->collapsibleField = Arr::get($options, 'collapsibleField', 'style');

        $this->name = $name;
        $this->type = $type;
        $this->parent = $parent;
        $this->formHelper = $this->parent->getFormHelper();

        $this->setTemplate();
        $this->setDefaultOptions($options);
        $this->setupValue();
        $this->initFilters();
    }

    protected function getConfig($key = null, $default = null)
    {
        return $this->parent->getConfig($key, $default);
    }

    protected function setTemplate(): void
    {
        $this->template = $this->getConfig($this->getTemplate(), $this->getTemplate());
    }

    protected function setupValue(): void
    {
        $values = $this->getOption($this->valueProperty);
        foreach ($this->autoplayKeys as $k => $v) {
            $value = Arr::get($values, $k);
            if ($value === null) {
                $value = old($v, $this->getModelValueAttribute($this->parent->getModel(), $v));
            }

            $values[$k] = $value;
        }
        $this->setValue($values);
    }

    public function getAutoplayOptions(): array
    {
        $autoplayKey = Arr::get($this->autoplayKeys, 'is_autoplay');
        $value = Arr::get($this->getValue(), 'is_autoplay', 'no');

        return array_merge([
            'label' => __('Is autoplay?'),
            'attr' => array_merge($this->getOption('attr', []), [
                'id' => $autoplayKey,
                'class' => 'form-control',
            ]),
            'choices' => SliderAutoplayHelper::getAutoplayChoices(),
            'selected' => $value,
            'empty_value' => null,
        ], $this->getOption('attrs.is_autoplay', []));
    }

    public function getAutoplaySpeedOptions(): array
    {
        $speedKey = Arr::get($this->autoplayKeys, 'autoplay_speed');
        $value = Arr::get($this->getValue(), 'autoplay_speed', 3000);

        return array_merge([
            'label' => __('Autoplay speed (if autoplay enabled)'),
            'attr' => array_merge($this->getOption('attr', []), [
                'id' => $speedKey,
                'class' => 'form-control',
            ]),
            'choices' => SliderAutoplayHelper::getAutoplaySpeedChoices(),
            'selected' => $value,
            'empty_value' => null,
        ], $this->getOption('attrs.autoplay_speed', []));
    }

    public function getLoopOptions(): array
    {
        $loopKey = Arr::get($this->autoplayKeys, 'is_loop');
        $value = Arr::get($this->getValue(), 'is_loop', 'yes');

        return array_merge([
            'label' => __('Loop?'),
            'attr' => array_merge($this->getOption('attr', []), [
                'id' => $loopKey,
                'class' => 'form-control',
            ]),
            'choices' => SliderAutoplayHelper::getLoopChoices(),
            'selected' => $value,
            'empty_value' => null,
        ], $this->getOption('attrs.is_loop', []));
    }

    public function render(
        array $options = [],
        $showLabel = true,
        $showField = true,
        $showError = true
    ): HtmlString|string {
        $html = '';

        $this->prepareOptions($options);

        if ($showField) {
            $this->rendered = true;
        }

        if (! $this->needsLabel()) {
            $showLabel = false;
        }

        if ($showError) {
            $showError = $this->parent->haveErrorsEnabled();
        }

        $data = $this->getRenderData();

        foreach ($this->autoplayKeys as $k => $v) {
            $options = [];
            switch ($k) {
                case 'is_autoplay':
                    $options = $this->getAutoplayOptions();

                    break;
                case 'autoplay_speed':
                    $options = $this->getAutoplaySpeedOptions();

                    break;
                case 'is_loop':
                    $options = $this->getLoopOptions();

                    break;
            }

            $options = array_merge($this->options, $options);

            $html .= $this->formHelper->getView()->make(
                $this->getViewTemplate(),
                $data + [
                    'name' => $v,
                    'nameKey' => $v,
                    'type' => $this->type,
                    'options' => $options,
                    'showLabel' => $showLabel,
                    'showField' => $showField,
                    'showError' => $showError,
                    'errorBag' => $this->parent->getErrorBag(),
                    'translationTemplate' => $this->parent->getTranslationTemplate(),
                ]
            )->render();
        }

        $wrapperClass = ($this->getOption('wrapperClassName') ?: 'mb-3 row') . ' slider-autoplay-fields';

        return new HtmlString('<div class="' . $wrapperClass . '">' . $html . '</div>');
    }

    protected function getTemplate(): string
    {
        return 'core/base::forms.fields.custom-select';
    }
}
