<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\Concerns\HasSubmitButton;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;

class StoreForm extends FormAbstract
{
    use HasLocationFields;
    use HasSubmitButton;

    public function setup(): void
    {
        Assets::addScriptsDirectly('vendor/core/plugins/marketplace/js/store.js');

        $this
            ->model(Store::class)
            ->setValidatorClass(StoreRequest::class)
            ->columns(6)
            ->template('core/base::forms.form-no-wrap')
            ->hasFiles()
            ->add('name', TextField::class, NameFieldOption::make()->required()->colspan(6))
            ->add(
                'slug',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(view('plugins/marketplace::stores.partials.shop-url-field', ['store' => $this->getModel()])->render())
                    ->colspan(3)
            )
            ->add('email', EmailField::class, EmailFieldOption::make()->required()->colspan(3))
            ->add('phone', TextField::class, [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                ],
                'colspan' => 6,
            ])
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->colspan(6))
            ->add('content', EditorField::class, ContentFieldOption::make()->colspan(6))
            ->addLocationFields()
            ->add(
                'company',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.company'))
                    ->placeholder(trans('plugins/marketplace::store.forms.company_placeholder'))
                    ->maxLength(255)
                    ->colspan(3)
            )
            ->add(
                'tax_id',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.tax_id'))
                    ->colspan(3)
                    ->maxLength(255)
            )
            ->add(
                'tax_country',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.tax_country'))
                    ->helperText(trans('plugins/marketplace::store.forms.tax_country_helper'))
                    ->colspan(3)
                    ->maxLength(120)
            )
            ->add(
                'tax_state',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.tax_state'))
                    ->helperText(trans('plugins/marketplace::store.forms.tax_state_helper'))
                    ->colspan(3)
                    ->maxLength(120)
            )
            ->add(
                'images_break',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('')
                    ->colspan(6)
            )
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.logo'))
                    ->colspan(2)
            )
            ->add(
                'logo_square',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.logo_square'))
                    ->helperText(trans('plugins/marketplace::store.forms.logo_square_helper'))
                    ->colspan(2)
            )
            ->add(
                'cover_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.cover_image'))
                    ->colspan(2)
            )
            ->add('status', SelectField::class, [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
                'help_block' => [
                    TextField::class => trans('plugins/marketplace::marketplace.helpers.store_status', [
                        'customer' => CustomerStatusEnum::LOCKED()->label(),
                        'status' => BaseStatusEnum::PUBLISHED()->label(),
                    ]),
                ],
                'colspan' => 3,
            ])
            ->add('customer_id', SelectField::class, [
                'label' => trans('plugins/marketplace::store.forms.store_owner'),
                'required' => true,
                'choices' => [0 => trans('plugins/marketplace::store.forms.select_store_owner')] + Customer::query()
                    ->where('is_vendor', true)
                    ->pluck('name', 'id')
                    ->all(),
                'colspan' => 3,
            ])
            ->add(
                'vacation_mode',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.vacation_mode'))
                    ->helperText(trans('plugins/marketplace::store.forms.vacation_mode_helper'))
                    ->colspan(6)
            )
            ->add(
                'vacation_message',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.vacation_message'))
                    ->helperText(trans('plugins/marketplace::store.forms.vacation_message_helper'))
                    ->placeholder(trans('plugins/marketplace::store.forms.vacation_message_placeholder'))
                    ->rows(3)
                    ->colspan(6)
            )
            ->when(! MarketplaceHelper::hideStoreSocialLinks(), function (): void {
                $this
                    ->add('extended_info_content', HtmlField::class, [
                        'html' => view('plugins/marketplace::partials.extra-content', ['model' => $this->getModel()]),
                    ]);
            });
    }
}
