<?php

use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;

class BlogAboutMeWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Blog About Me'),
            'description' => __('Display about me widget'),
            'author_url' => null,
            'author_avatar' => null,
            'author_name' => null,
            'author_role' => null,
            'author_description' => null,
            'author_signature' => null,
        ]);
    }

    protected function settingForm(): WidgetForm|string|null
    {
        return WidgetForm::createFromArray($this->getConfig())
            ->add('name', TextField::class, TextFieldOption::make()->label(__('Name')))
            ->add('author_url', TextField::class, TextFieldOption::make()->label(__('Author URL')))
            ->add('author_avatar', MediaImageField::class, MediaImageFieldOption::make()->label(__('Author Avatar')))
            ->add('author_name', TextField::class, TextFieldOption::make()->label(__('Author Name')))
            ->add('author_role', TextField::class, TextFieldOption::make()->label(__('Author Role')))
            ->add('author_description', TextareaField::class, TextareaFieldOption::make()->label(__('Author Description')))
            ->add('author_signature', MediaImageField::class, MediaImageFieldOption::make()->label(__('Author Signature')));
    }

    protected function requiredPlugins(): array
    {
        return ['blog'];
    }
}
