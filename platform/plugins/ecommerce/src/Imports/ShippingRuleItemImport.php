<?php

namespace Botble\Ecommerce\Imports;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Exceptions\ImportShippingRulesException;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;
use Botble\Location\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\RequiredIf;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;

class ShippingRuleItemImport implements
    ToModel,
    WithHeadingRow,
    WithMapping,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithChunkReading
{
    use Importable;
    use SkipsFailures;
    use SkipsErrors;
    use ImportTrait;

    protected string $importType = 'overwrite';

    protected array $availableCountries;

    protected Collection $countries;

    protected Collection $shippingRules;

    protected bool $isLoadFromLocation;

    protected int $rowCurrent = 1; // include header

    protected Request $validatorClass;

    public function __construct(protected Request $request)
    {
        $this->availableCountries = EcommerceHelper::getAvailableCountries();
        $this->isLoadFromLocation = EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation();

        $this->countries = collect();
        $this->shippingRules = collect();
    }

    public function setImportType(string $importType): self
    {
        $this->importType = $importType;

        return $this;
    }

    public function getImportType(): string
    {
        return $this->importType;
    }

    public function model(array $row)
    {
        $importType = $this->getImportType();

        if ($row['shipping_rule_id'] == 0) {
            $shippingRuleRef = $this->shippingRules
                ->where('shipping_rule', $row['shipping_rule'])
                ->where('country', $row['country'])
                ->where('type', $row['type'])
                ->first();

            if ($shippingRuleRef && $shippingRuleRef['shipping_rule_id']) {
                $row['shipping_rule_id'] = $shippingRuleRef['shipping_rule_id'];
            } else {
                $shippingRule = ShippingRule::query()
                    ->create([
                        'name' => $row['shipping_rule'],
                        'type' => ShippingRuleTypeEnum::BASED_ON_LOCATION,
                        'price' => 0,
                        'shipping_id' => $row['shipping_id'],
                    ]);

                if (! $shippingRule->getKey()) {
                    throw new ImportShippingRulesException(sprintf('Please create a shipping rule with name "%s" and type "%s" for country "%s" first.', $row['shipping_rule'], ShippingRuleTypeEnum::getLabel($row['type']), EcommerceHelper::getCountryNameById($row['country'])));
                }

                $this->shippingRules = $this->shippingRules
                    ->map(function ($value) use ($row, $shippingRule) {
                        if ($value['shipping_rule'] == $row['shipping_rule'] &&
                            $value['country'] == $row['country'] &&
                            $value['shipping_rule_id'] == 0) {
                            $value['shipping_rule_id'] = $shippingRule->getKey();
                        }

                        return $value;
                    });

                $row['shipping_rule_id'] = $shippingRule->id;
            }
        }

        $condition = [
            'shipping_rule_id' => $row['shipping_rule_id'],
            'country' => $row['country'],
            'state' => $row['state'],
            'city' => $row['city'],
            'zip_code' => $row['zip_code'],
        ];

        $shippingRuleItem = null;
        $isCreateOrUpdate = false;
        $data = array_merge($condition, [
            'name' => $row['name'] ?? null,
            'adjustment_price' => $row['adjustment_price'],
            'is_enabled' => $row['is_enabled'],
        ]);

        if ($importType == 'add_new') {
            $shippingRuleItem = ShippingRuleItem::query()->create($data);
            $isCreateOrUpdate = true;
        } else {
            $shippingRuleCount = ShippingRuleItem::query()->where($condition)->count();
            if ($shippingRuleCount) {
                if ($importType == 'overwrite') {
                    $shippingRuleItem = ShippingRuleItem::query()
                        ->where($condition)
                        ->create($data);
                    $isCreateOrUpdate = true;
                }
            } else {
                $shippingRuleItem = ShippingRuleItem::query()->create($data);
                $isCreateOrUpdate = true;
            }
        }

        if ($isCreateOrUpdate) {
            $this->onSuccess($shippingRuleItem);
        }

        return $shippingRuleItem;
    }

    public function getShippingRule(string $name, ?string $country, ?string $type): ?ShippingRule
    {
        /**
         * @var ShippingRule $shippingRule
         */
        $shippingRule = ShippingRule::query()
            ->where([
                'name' => $name,
                'type' => $type,
            ])
            ->whereHas('shipping', function ($query) use ($country): void {
                $query->where('country', $country);
            })
            ->first();

        return $shippingRule;
    }

    public function getShipping(?string $country): ?Shipping
    {
        /**
         * @var Shipping $shipping
         */
        $shipping = Shipping::query()
            ->where('country', $country)
            ->first();

        return $shipping;
    }

    /**
     * Change value before insert to model
     *
     * @param array $row
     */
    public function map($row): array
    {
        ++$this->rowCurrent;
        $row = $this->mapLocalization($row);
        $row = $this->setCountryToRow($row);
        $row = $this->setStateToRow($row);
        $row = $this->setCityToRow($row);

        return $this->setShippingRuleToRow($row);
    }

    protected function setCountryToRow(array $row): array
    {
        $row['country'] = trim(Arr::get($row, 'country', ''));
        if ($row['country']) {
            $row['country'] = array_search(strtolower($row['country']), array_map('strtolower', $this->availableCountries));
        }

        return $row;
    }

    protected function setStateToRow(array $row): array
    {
        if ($this->isLoadFromLocation && $row['country']) {
            $stateName = trim(Arr::get($row, 'state', ''));
            $row['state'] = '';
            $country = $this->countries->where('id', $row['country'])->first();

            if (! $country) {
                // In location mode the country column resolves to the ISO code (e.g. "EG"),
                // while a manually selected country may be the numeric ID - match either so
                // the states/cities relations load correctly.
                $country = Country::query()
                    ->when(
                        is_numeric($row['country']),
                        fn ($query) => $query->where('id', $row['country']),
                        fn ($query) => $query->where('code', $row['country'])
                    )
                    ->with(['states', 'states.cities'])
                    ->first();

                $this->countries->push([
                    'id' => $row['country'],
                    'model' => $country,
                ]);
            } else {
                $country = $country['model'];
            }

            if ($country instanceof Country && $country->id) {
                // Match by name (case-insensitive, trimmed - consistent with country matching) or by ID.
                $normalizedStateName = mb_strtolower($stateName);
                $state = $country->states->first(function ($value) use ($stateName, $normalizedStateName) {
                    return mb_strtolower((string) $value->name) === $normalizedStateName
                        || (string) $value->id === $stateName;
                });
                if ($state) {
                    $row['state'] = $state->id;
                } elseif ($stateName !== '') {
                    // Remember the unmatched name so the validation pass can report it
                    // instead of the importer silently leaving the State empty.
                    $row['unmatched_state'] = $stateName;
                }
            }
        }

        return $row;
    }

    protected function setCityToRow(array $row): array
    {
        if ($this->isLoadFromLocation && $row['country']) {
            $cityName = trim(Arr::get($row, 'city', ''));

            $row['city'] = '';
            $country = $this->countries->where('id', $row['country'])->first();
            if ($country) {
                $country = $country['model'];
                if ($country instanceof Country && $country->id) {
                    // Resolve the city within the matched state when the State column matched;
                    // otherwise search every state in the country so a valid city is still linked
                    // to a real record instead of being silently stored as raw text.
                    $states = $row['state']
                        ? $country->states->where('id', $row['state'])
                        : $country->states;

                    $normalizedCityName = mb_strtolower($cityName);
                    $city = null;

                    foreach ($states as $state) {
                        $city = $state->cities->first(function ($value) use ($cityName, $normalizedCityName) {
                            return mb_strtolower((string) $value->name) === $normalizedCityName
                                || (string) $value->id === $cityName;
                        });

                        if ($city) {
                            break;
                        }
                    }

                    if ($city) {
                        $row['city'] = $city->id;

                        // Backfill the state from the matched city when the State column did not
                        // resolve, so checkout can still match the customer's location. The state
                        // is now recovered, so any earlier "unmatched state" flag no longer applies.
                        if (! $row['state']) {
                            $row['state'] = $city->state_id;
                            Arr::forget($row, 'unmatched_state');
                        }
                    } elseif ($cityName !== '') {
                        // Remember the unmatched name so the validation pass can report it
                        // instead of the importer silently leaving the City empty.
                        $row['unmatched_city'] = $cityName;
                    }
                }
            }
        }

        return $row;
    }

    protected function setShippingRuleToRow(array $row): array
    {
        $row['shipping_rule_id'] = 0;

        if (! empty($row['shipping_rule'])) {
            $row['shipping_rule'] = trim($row['shipping_rule']);
            $country = $row['country'];

            $shippingRule = $this->shippingRules
                ->where('shipping_rule', $row['shipping_rule'])
                ->where('country', $country)
                ->where('type', $row['type'])
                ->first();

            $shippingRuleId = 0;
            if ($shippingRule) {
                $shippingRuleId = $shippingRule['shipping_rule_id'];
            } else {
                $shippingRule = $this->getShippingRule($row['shipping_rule'], $country, $row['type']);
                if (! $shippingRule) {
                    $row['shipping_id'] = $this->getShippingId($row['country']);
                } else {
                    $shippingRuleId = $shippingRule->getKey();
                }

                $this->shippingRules->push([
                    'shipping_rule' => $row['shipping_rule'],
                    'country' => $country,
                    'type' => $row['type'],
                    'shipping_rule_id' => $shippingRuleId,
                ]);
            }

            $row['shipping_rule_id'] = $shippingRuleId;
        }

        if (! $row['shipping_rule_id'] && ! isset($row['shipping_id'])) {
            $row['shipping_id'] = $this->getShippingId($row['country']);
        }

        return $row;
    }

    protected function getShippingId(string $country): string|int
    {
        $shipping = $this->getShipping($country);
        if ($shipping) {
            return $shipping->getKey();
        }

        throw new ImportShippingRulesException(sprintf('Shipping not for country "%s" found', EcommerceHelper::getCountryNameById($country)));
    }

    public function mapLocalization(array $row): array
    {
        $row['import_type'] = (string) Arr::get($row, 'import_type');
        if (! in_array($row['import_type'], ['overwrite', 'add_new', 'skip'])) {
            $row['import_type'] = 'overwrite';
        }

        $row['type'] = (string) Arr::get($row, 'type');
        if (! in_array($row['type'], ShippingRuleTypeEnum::keysAllowRuleItems())) {
            $row['type'] = ShippingRuleTypeEnum::BASED_ON_ZIPCODE;
        }

        $this->setValues($row, [
            ['key' => 'name', 'type' => 'string'],
            ['key' => 'shipping_rule', 'type' => 'string'],
            ['key' => 'country', 'type' => 'string'],
            ['key' => 'state', 'type' => 'string'],
            ['key' => 'city', 'type' => 'string'],
            ['key' => 'zip_code', 'type' => 'string'],
            ['key' => 'adjustment_price', 'type' => 'number'],
            ['key' => 'is_enabled', 'type' => 'bool'],
        ]);

        return $row;
    }

    protected function setValues(array &$row, array $attributes = []): self
    {
        foreach ($attributes as $attribute) {
            $this->setValue(
                $row,
                Arr::get($attribute, 'key'),
                Arr::get($attribute, 'type', 'array'),
                Arr::get($attribute, 'default'),
                Arr::get($attribute, 'from')
            );
        }

        return $this;
    }

    protected function setValue(array &$row, string $key, string $type = 'array', $default = null, $from = null): self
    {
        $value = Arr::get($row, $from ?: $key, $default);

        switch ($type) {
            case 'array':
                $value = $value ? explode(',', $value) : [];

                break;
            case 'bool':
                if (Str::lower($value) == 'false' || $value == '0' || Str::lower($value) == 'no') {
                    $value = false;
                }
                $value = $value ? 1 : 0;

                break;
            case 'datetime':
                if ($value) {
                    if (in_array(gettype($value), ['integer', 'double'])) {
                        $value = $this->transformDate($value);
                    } else {
                        $value = $this->getDate($value);
                    }
                }

                break;
            case 'string':
                $value = (string) $value;

                break;
        }

        Arr::set($row, $key, $value);

        return $this;
    }

    public function rules(): array
    {
        $rules = method_exists($this->getValidatorClass(), 'rules') ? $this->getValidatorClass()->rules() : [];

        if ($rules) {
            if (is_array(Arr::get($rules, 'shipping_rule_id'))) {
                foreach ($rules['shipping_rule_id'] as $key => $value) {
                    if ($value instanceof Exists) {
                        Arr::forget($rules, 'shipping_rule_id.' . $key);
                    }
                }
            }

            if (is_array(Arr::get($rules, 'zip_code'))) {
                foreach ($rules['zip_code'] as $key => $value) {
                    if ($value instanceof RequiredIf) {
                        Arr::forget($rules, 'zip_code.' . $key);
                    }
                }
            }

            if (is_array(Arr::get($rules, 'city'))) {
                foreach ($rules['city'] as $key => $value) {
                    if ($value instanceof RequiredIf) {
                        Arr::forget($rules, 'zip_code.' . $key);
                    }
                }
            }
        }

        return $rules;
    }

    public function getValidatorClass(): Request
    {
        return $this->validatorClass;
    }

    public function setValidatorClass(Request $validatorClass): self
    {
        $this->validatorClass = $validatorClass;

        return $this;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
