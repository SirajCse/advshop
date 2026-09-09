<?php

namespace Botble\Ecommerce\Forms\Concerns;

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\InputFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Arr;

trait HasLocationFields
{
    public function addLocationFields(
        array $countryAttributes = [],
        array $stateAttributes = [],
        array $cityAttributes = [],
        array $addressAttributes = [],
        array $zipCodeAttributes = [],
        array $hiddenFields = []
    ): static {
        $loadLocationsFromPluginLocation = EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation();

        if ($loadLocationsFromPluginLocation) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');

            if (request()->ajax()) {
                $this->add(
                    'locationScript',
                    HtmlField::class,
                    HtmlFieldOption::make()
                        ->content(Html::script('vendor/core/plugins/location/js/location.js')->toHtml())
                );
            }

            if (! AdminHelper::isInAdmin() && class_exists(Theme::class)) {
                Theme::asset()
                    ->container('footer')
                    ->add('location-js', 'vendor/core/plugins/location/js/location.js', ['jquery']);
            }
        }

        $isMultipleCountries = EcommerceHelper::isUsingInMultipleCountries();

        $isZipcodeEnabled = EcommerceHelper::isZipCodeEnabled();

        $countryFieldName = Arr::get($countryAttributes, 'name', 'country');
        $stateFieldName = Arr::get($stateAttributes, 'name', 'state');
        $cityFieldName = Arr::get($cityAttributes, 'name', 'city');
        $addressFieldName = Arr::get($addressAttributes, 'name', 'address');
        $zipCodeFieldName = Arr::get($zipCodeAttributes, 'name', 'zip_code');

        $countryAttributes = Arr::except($countryAttributes, ['name']);

        $model = $this->getModel();
        $modelCountry = is_array($model) ? Arr::get($model, 'country') : (is_object($model) ? ($model->country ?? null) : null);
        $countryAttributes['selected'] = old($countryFieldName, $modelCountry) ?: Arr::get($countryAttributes, 'value');

        $stateAttributes = Arr::except($stateAttributes, ['name']);

        $modelState = is_array($model) ? Arr::get($model, 'state') : (is_object($model) ? ($model->state ?? null) : null);
        $stateAttributes['selected'] = old($stateFieldName, $modelState) ?: Arr::get($stateAttributes, 'value');

        $cityAttributes = Arr::except($cityAttributes, ['name']);

        $modelCity = is_array($model) ? Arr::get($model, 'city') : (is_object($model) ? ($model->city ?? null) : null);
        $cityAttributes['selected'] = old($cityFieldName, $modelCity) ?: Arr::get($cityAttributes, 'value');

        $addressAttributes = Arr::except($addressAttributes, ['name']);

        $zipCodeAttributes = Arr::except($zipCodeAttributes, ['name']);

        $countryPlaceholder = Arr::pull($countryAttributes, 'empty_value', __('Select country...'));
        $statePlaceholder = Arr::pull($stateAttributes, 'empty_value', __('Select state...'));
        $cityPlaceholder = Arr::pull($cityAttributes, 'empty_value', __('Select city...'));

        $this
            ->when($isMultipleCountries && ! in_array('country', $hiddenFields), function (FormAbstract $form) use ($countryFieldName, $countryAttributes, $countryPlaceholder): void {
                $form->add(
                    $countryFieldName,
                    SelectField::class,
                    [
                        ...SelectFieldOption::make()
                            ->label(trans('plugins/ecommerce::addresses.country'))
                            ->attributes([
                                'data-type' => 'country',
                            ])
                            ->choices(['' => $countryPlaceholder] + EcommerceHelper::getAvailableCountries())
                            ->colspan(3)
                            ->toArray(),
                        ...$countryAttributes,
                    ]
                );
            }, function (FormAbstract $form) use ($countryFieldName, $countryAttributes, $isMultipleCountries, $hiddenFields): void {
                if (! $isMultipleCountries || in_array('country', $hiddenFields)) {
                    $form->add(
                        $countryFieldName,
                        'hidden',
                        [
                            ...InputFieldOption::make()
                                ->value(EcommerceHelper::getFirstCountryId())
                                ->toArray(),
                            ...$countryAttributes,
                        ]
                    );
                }
            })
            ->when($loadLocationsFromPluginLocation && ! in_array('state', $hiddenFields), function (FormAbstract $form) use (
                $countryAttributes,
                $stateFieldName,
                $stateAttributes,
                $statePlaceholder,
                $isMultipleCountries
            ): void {

                $form->add(
                    $stateFieldName,
                    SelectField::class,
                    [
                        ...SelectFieldOption::make()
                            ->choices(
                                ['' => $statePlaceholder] + EcommerceHelper::getAvailableStatesByCountry(
                                    $countryAttributes['selected']
                                )
                            )
                            ->attributes([
                                'data-type' => 'state',
                                'data-url' => route('ajax.states-by-country'),
                            ])
                            ->colspan($isMultipleCountries ? 2 : 3)
                            ->label(trans('plugins/ecommerce::addresses.state'))
                            ->toArray(),
                        ...$stateAttributes,
                    ]
                );
            }, function (FormAbstract $form) use ($loadLocationsFromPluginLocation, $stateFieldName, $stateAttributes, $statePlaceholder, $hiddenFields): void {
                if (! in_array('state', $hiddenFields)) {
                    $form->add(
                        $stateFieldName,
                        TextField::class,
                        [
                            ...TextFieldOption::make()
                                ->label(trans('plugins/ecommerce::addresses.state'))
                                ->placeholder($statePlaceholder)
                                ->colspan($loadLocationsFromPluginLocation ? 2 : 3)
                                ->toArray(),
                            ...$stateAttributes,
                        ]
                    );
                }
            })
            ->when(EcommerceHelper::useCityFieldAsTextField() && ! in_array('city', $hiddenFields), function (FormAbstract $form) use (
                $loadLocationsFromPluginLocation,
                $cityFieldName,
                $cityAttributes,
                $cityPlaceholder
            ): void {
                $form->add(
                    $cityFieldName,
                    TextField::class,
                    [
                        ...TextFieldOption::make()
                            ->label(trans('plugins/ecommerce::addresses.city'))
                            ->placeholder($cityPlaceholder)
                            ->colspan($loadLocationsFromPluginLocation ? 2 : 3)
                            ->toArray(),
                        ...$cityAttributes,
                    ]
                );
            }, function (FormAbstract $form) use ($stateAttributes, $cityFieldName, $cityAttributes, $cityPlaceholder, $isMultipleCountries, $hiddenFields): void {
                if (! in_array('city', $hiddenFields)) {
                    $form->add(
                        $cityFieldName,
                        SelectField::class,
                        [
                            ...SelectFieldOption::make()
                                ->label(trans('plugins/ecommerce::addresses.city'))
                                ->attributes([
                                    'data-type' => 'city',
                                    'data-url' => route('ajax.cities-by-state'),
                                ])
                                ->colspan($isMultipleCountries ? 2 : 3)
                                ->choices(
                                    ['' => $cityPlaceholder] + EcommerceHelper::getAvailableCitiesByState(
                                        $stateAttributes['selected']
                                    )
                                )
                                ->toArray(),
                            ...$cityAttributes,
                        ]
                    );
                }
            })
            ->when(! in_array('address', $hiddenFields), function (FormAbstract $form) use (
                $addressFieldName,
                $addressAttributes,
                $isZipcodeEnabled,
                $isMultipleCountries,
                $hiddenFields
            ): void {
                $form->add(
                    $addressFieldName,
                    TextField::class,
                    [
                        ...TextFieldOption::make()
                            ->label(trans('plugins/ecommerce::addresses.address'))
                            ->placeholder(trans('plugins/ecommerce::addresses.address_placeholder'))
                            ->colspan($isZipcodeEnabled && ! in_array('zip_code', $hiddenFields) ? ($isMultipleCountries ? 3 : 2) : 3)
                            ->toArray(),
                        ...$addressAttributes,
                    ]
                );
            })
            ->when($isZipcodeEnabled && ! in_array('zip_code', $hiddenFields), function (FormAbstract $form) use (
                $isMultipleCountries,
                $zipCodeAttributes,
                $zipCodeFieldName
            ): void {
                $form->add(
                    $zipCodeFieldName,
                    TextField::class,
                    [
                        ...TextFieldOption::make()
                            ->placeholder(trans('plugins/ecommerce::addresses.zip_placeholder'))
                            ->label(trans('plugins/ecommerce::addresses.zip'))
                            ->colspan($isMultipleCountries ? 3 : 2)
                            ->toArray(),
                        ...$zipCodeAttributes,
                    ]
                );
            });

        return $this;
    }
}
