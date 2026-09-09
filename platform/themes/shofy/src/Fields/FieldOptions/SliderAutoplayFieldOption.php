<?php

namespace Theme\Shofy\Fields\FieldOptions;

use Botble\Base\Forms\FormFieldOptions;

class SliderAutoplayFieldOption extends FormFieldOptions
{
    public static function make(): static
    {
        return new static();
    }

    public function collapsibleCondition($condition): static
    {
        return $this->addAttribute('collapsibleCondition', $condition);
    }

    public function collapsibleField(string $field): static
    {
        return $this->addAttribute('collapsibleField', $field);
    }

    public function autoplayKeys(array $keys): static
    {
        return $this->addAttribute('autoplayKeys', $keys);
    }
}
