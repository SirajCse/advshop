<?php

namespace Botble\Ecommerce\Imports;

use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Maatwebsite\Excel\Validators\Failure;

class ValidateShippingRuleItemImport extends ShippingRuleItemImport
{
    public function model(array $row)
    {
        $failures = [];

        // Report location names that could not be matched against the Location plugin so the
        // importer points at the exact row/value instead of silently blanking the field.
        if (! empty($row['unmatched_state'])) {
            $failures[] = $this->makeLocationFailure('State', $row['unmatched_state']);
        }

        if (! empty($row['unmatched_city'])) {
            $failures[] = $this->makeLocationFailure('City', $row['unmatched_city']);
        }

        if ($row['shipping_rule_id'] == 0 && $row['type'] == ShippingRuleTypeEnum::BASED_ON_ZIPCODE) {
            if (! $row['zip_code']) {
                $failures[] = new Failure(
                    $this->rowCurrent,
                    'Zip Code',
                    [trans('validation.required', ['attribute' => 'Zip Code'])],
                    []
                );
            }

            // Skip the generic "City required" when a specific "city not found" message was added.
            if (! $row['city'] && empty($row['unmatched_city'])) {
                $failures[] = new Failure(
                    $this->rowCurrent,
                    'City',
                    [trans('validation.required', ['attribute' => 'City'])],
                    []
                );
            }
        }

        if ($failures) {
            if (method_exists($this, 'onFailure')) {
                $this->onFailure(...$failures);
            }
        } else {
            $this->onSuccess(collect($row));
        }

        return null;
    }

    protected function makeLocationFailure(string $attribute, string $value): Failure
    {
        return new Failure(
            $this->rowCurrent,
            $attribute,
            [trans('plugins/ecommerce::bulk-import.location_not_matched', compact('attribute', 'value'))],
            []
        );
    }
}
