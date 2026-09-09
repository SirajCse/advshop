<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Imports\ShippingRuleItemImport;
use Botble\Ecommerce\Imports\ValidateShippingRuleItemImport;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use ReflectionMethod;

/**
 * Covers the "Based on location" shipping import mapping when countries/states/cities
 * are loaded from the Location plugin (get_ecommerce_setting load_..._from_location_plugin = 1).
 */
class ShippingRuleItemImportLocationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Country $country;

    protected State $state;

    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::query()->create([
            'name' => 'Egypt',
            'code' => 'EG',
            'status' => 'published',
        ]);

        $this->state = State::query()->create([
            'name' => 'Alexandria',
            'country_id' => $this->country->id,
            'status' => 'published',
        ]);

        $this->city = City::query()->create([
            'name' => 'Smouha',
            'state_id' => $this->state->id,
            'country_id' => $this->country->id,
            'status' => 'published',
        ]);

        setting()->set('ecommerce_load_countries_states_cities_from_location_plugin', 1)->save();
    }

    protected function mapLocation(array $overrides = []): array
    {
        $row = array_merge([
            'country' => 'Egypt',
            'state' => 'Alexandria',
            'city' => 'Smouha',
        ], $overrides);

        $import = new ShippingRuleItemImport(new Request());

        foreach (['setCountryToRow', 'setStateToRow', 'setCityToRow'] as $method) {
            $ref = new ReflectionMethod($import, $method);
            $ref->setAccessible(true);
            $row = $ref->invoke($import, $row);
        }

        return $row;
    }

    public function test_exact_names_resolve_country_state_and_city(): void
    {
        $row = $this->mapLocation();

        $this->assertEquals('EG', $row['country']);
        $this->assertEquals($this->state->id, $row['state']);
        $this->assertEquals($this->city->id, $row['city']);
    }

    public function test_state_matching_is_case_insensitive(): void
    {
        $row = $this->mapLocation(['state' => 'aLeXaNdRiA', 'city' => 'sMoUhA']);

        $this->assertEquals($this->state->id, $row['state']);
        $this->assertEquals($this->city->id, $row['city']);
    }

    public function test_state_is_backfilled_from_matched_city_when_state_name_wrong(): void
    {
        // State name does not exist, but the city does - state should be recovered from the city.
        $row = $this->mapLocation(['state' => 'Wrong State', 'city' => 'Smouha']);

        $this->assertEquals($this->state->id, $row['state']);
        $this->assertEquals($this->city->id, $row['city']);
        $this->assertArrayNotHasKey('unmatched_state', $row);
    }

    public function test_unmatched_state_and_city_are_flagged(): void
    {
        $row = $this->mapLocation(['state' => 'Nowhere', 'city' => 'Ghosttown']);

        $this->assertSame('', $row['state']);
        $this->assertSame('', $row['city']);
        $this->assertEquals('Nowhere', $row['unmatched_state']);
        $this->assertEquals('Ghosttown', $row['unmatched_city']);
    }

    public function test_unmatched_city_is_never_stored_as_raw_text(): void
    {
        $row = $this->mapLocation(['state' => 'Alexandria', 'city' => 'NotACity']);

        // Previously the raw text "NotACity" leaked into the city column; now it is blanked + flagged.
        $this->assertSame('', $row['city']);
        $this->assertEquals('NotACity', $row['unmatched_city']);
    }

    public function test_validation_pass_reports_unmatched_location_names(): void
    {
        $validate = new ValidateShippingRuleItemImport(new Request());

        $model = new ReflectionMethod($validate, 'model');
        $model->setAccessible(true);
        $model->invoke($validate, [
            'shipping_rule_id' => 1,
            'type' => 'based_on_location',
            'zip_code' => '',
            'city' => '',
            'unmatched_state' => 'Nowhere',
            'unmatched_city' => 'Ghosttown',
        ]);

        $failures = $validate->failures();

        $this->assertCount(2, $failures);
        $attributes = $failures->map(fn ($failure) => $failure->attribute())->all();
        $this->assertContains('State', $attributes);
        $this->assertContains('City', $attributes);
    }
}
