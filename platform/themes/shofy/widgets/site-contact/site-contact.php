<?php

use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;

class SiteContactWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Site Contact'),
            'description' => __('Display site contact information.'),
            'phone' => null,
            'phone_label' => null,
            'email' => null,
            'enable_email_link' => true,
            'address' => null,
            'enable_address_link' => true,
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'name',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Name'))
            )
            ->add(
                'phone_label',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Phone label'))
            )
            ->add(
                'phone',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Phone number'))
                    ->helperText(__('If you need multiple phones, please use slash (/) to separate them. e.g: 012345566/0345678923'))
            )
            ->add(
                'email',
                EmailField::class,
                EmailFieldOption::make()
                    ->label(__('Email address'))
                    ->helperText(__('If you need multiple emails, please use slash (/) to separate them. e.g: contact@demo.com/hello@abc.com'))
            )
            ->add(
                'enable_email_link',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable email link'))
                    ->helperText(__('If disabled, the email address will be displayed as plain text without a mailto: link.'))
                    ->defaultValue(true)
            )
            ->add(
                'address',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Address'))
            )
            ->add(
                'enable_address_link',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(__('Enable address link'))
                    ->helperText(__('If disabled, the address will be displayed as plain text without a Google Maps link.'))
                    ->defaultValue(true)
            );
    }
}
