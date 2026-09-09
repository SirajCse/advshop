<?php

namespace Botble\Base\Forms\FieldOptions;

class PhoneNumberFieldOption extends TextFieldOption
{
    protected bool $withCountryCodeSelection = false;

    public static function make(): static
    {
        return parent::make()
            ->maxLength(25);
    }

    public function withCountryCodeSelection(bool $withCountryCodeSelection = true): static
    {
        $this->withCountryCodeSelection = $withCountryCodeSelection;

        return $this;
    }

    public function hasCountryCodeSelection(): bool
    {
        return $this->withCountryCodeSelection;
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if ($this->withCountryCodeSelection) {
            $data['with_country_code_selection'] = true;
        }

        return $data;
    }
}
