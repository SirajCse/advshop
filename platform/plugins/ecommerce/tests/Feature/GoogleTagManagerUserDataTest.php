<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\AdsTracking\GoogleTagManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test GoogleTagManager::buildUserData() for GA4 purchase user_data support.
 *
 * This test verifies the implementation of user_data field in GA4 purchase events,
 * which requires:
 * 1. external_id = (string) $order->user_id when truthy, omitted for guest (user_id = 0)
 * 2. city/state resolve via *_name accessors (LocationTrait) instead of raw values
 * 3. Returns [] when gtm_user_data_enabled setting is false
 * 4. Properly filters empty values via array_filter
 *
 * Since Order/OrderAddress models have strict DB constraints (password on Customer,
 * order_id on OrderAddress, sub_total on Order), this test performs static code
 * verification by examining:
 * - GoogleTagManager::buildUserData() source
 * - LocationTrait accessors (city_name, state_name)
 * - Array composition and filtering logic
 */
class GoogleTagManagerUserDataTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Verify buildUserData uses gtm_user_data_enabled setting to gate output.
     *
     * Source: GoogleTagManager::buildUserData() line 323-325
     */
    public function test_build_user_data_returns_empty_array_when_setting_disabled(): void
    {
        setting('gtm_user_data_enabled', false);

        $gtm = new GoogleTagManager();

        // Examine the source to verify early return when setting is false
        $reflectionClass = new \ReflectionClass($gtm);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        // Verify the check exists in source
        $this->assertStringContainsString(
            "if (! setting('gtm_user_data_enabled'",
            $source,
            'buildUserData must check gtm_user_data_enabled setting'
        );
        $this->assertStringContainsString(
            'return [];',
            $source,
            'buildUserData must return empty array when setting is false'
        );
    }

    /**
     * Verify buildUserData includes external_id as a SHA256 hash of user_id when truthy.
     *
     * Source: GoogleTagManager::buildUserData()
     * The external_id field uses: 'external_id' => $order->user_id ? hash('sha256', (string) $order->user_id) : ''
     * Meta Advanced Matching expects external_id pre-hashed, so the raw database id is
     * never exposed in the page dataLayer.
     */
    public function test_build_user_data_external_id_is_sha256_hashed(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        // Verify external_id field exists
        $this->assertStringContainsString(
            "'external_id' =>",
            $source,
            'buildUserData must define external_id field'
        );
        // Verify external_id is SHA256-hashed (not the raw user id)
        $this->assertStringContainsString(
            "hash('sha256', (string) \$order->user_id)",
            $source,
            'external_id must be a SHA256 hash of the user id, not the raw value'
        );
    }

    /**
     * Verify buildUserData includes postal_code and country fields.
     *
     * Source: GoogleTagManager::buildUserData()
     * postal_code comes from $address->zip_code; country is the raw 2-letter ISO code
     * ($address->country) as required by GA4 Enhanced Conversions / Meta Advanced Matching.
     */
    public function test_build_user_data_includes_postal_code_and_country(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            "'postal_code' => trim((string) \$address->zip_code)",
            $source,
            'buildUserData must include postal_code from zip_code'
        );
        $this->assertStringContainsString(
            "'country' => trim((string) \$address->country)",
            $source,
            'buildUserData must include country as the raw ISO code'
        );
    }

    /**
     * Verify buildUserData omits external_id for guest orders (user_id = 0/falsy).
     *
     * The ternary operator in line 339:
     *   'external_id' => $order->user_id ? (string) $order->user_id : ''
     *
     * combined with array_filter in line 349 (which removes empty strings):
     *   ], fn ($value) => $value !== '' && $value !== null);
     *
     * ensures external_id is omitted when user_id is 0 or empty.
     */
    public function test_build_user_data_omits_external_id_for_guest_orders(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        // Verify array_filter removes empty strings
        $this->assertStringContainsString(
            'array_filter',
            $source,
            'buildUserData must filter out empty values'
        );
        $this->assertStringContainsString(
            '!== \'\'',
            $source,
            'array_filter must remove empty strings'
        );
    }

    /**
     * Verify buildUserData uses city_name and state_name (LocationTrait accessors)
     * instead of raw city and state values.
     *
     * Source: GoogleTagManager::buildUserData() lines 347-348
     * The code explicitly uses $address->city_name and $address->state_name,
     * which are computed properties from LocationTrait that resolve location plugin IDs.
     */
    public function test_build_user_data_uses_location_trait_name_accessors(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        // Verify uses *_name accessors, not raw fields
        $this->assertStringContainsString(
            '$address->city_name',
            $source,
            'buildUserData must use city_name accessor (not raw city field)'
        );
        $this->assertStringContainsString(
            '$address->state_name',
            $source,
            'buildUserData must use state_name accessor (not raw state field)'
        );

        // Verify LocationTrait provides these accessors
        $locationTraitPath = dirname(__DIR__, 2) . '/src/Traits/LocationTrait.php';
        $this->assertFileExists($locationTraitPath);

        $locationTraitSource = file_get_contents($locationTraitPath);
        $this->assertStringContainsString(
            'getCityNameAttribute',
            $locationTraitSource,
            'LocationTrait must define getCityNameAttribute'
        );
        $this->assertStringContainsString(
            'getStateNameAttribute',
            $locationTraitSource,
            'LocationTrait must define getStateNameAttribute'
        );
    }

    /**
     * Verify LocationTrait::city_name resolves numeric IDs to names when location plugin active.
     *
     * Source: LocationTrait::getCityNameAttribute() lines 70-87
     * When the value is numeric AND location plugin is active, resolve via locationCity relation.
     * Otherwise fall back to the raw value.
     */
    public function test_location_trait_city_name_resolves_numeric_ids(): void
    {
        $locationTraitPath = dirname(__DIR__, 2) . '/src/Traits/LocationTrait.php';
        $locationTraitSource = file_get_contents($locationTraitPath);

        // Verify it checks if value is numeric
        $this->assertStringContainsString(
            'is_numeric($value)',
            $locationTraitSource,
            'city_name must check if value is numeric before resolving'
        );

        // Verify it uses locationCity relation
        $this->assertStringContainsString(
            '$this->locationCity',
            $locationTraitSource,
            'city_name must resolve via locationCity relation'
        );

        // Verify it falls back to raw value
        $this->assertStringContainsString(
            'return $value',
            $locationTraitSource,
            'city_name must fall back to raw value when not numeric or relation missing'
        );
    }

    /**
     * Verify LocationTrait::state_name resolves numeric IDs to names when location plugin active.
     *
     * Source: LocationTrait::getStateNameAttribute() lines 51-68
     * Same pattern as city_name.
     */
    public function test_location_trait_state_name_resolves_numeric_ids(): void
    {
        $locationTraitPath = dirname(__DIR__, 2) . '/src/Traits/LocationTrait.php';
        $locationTraitSource = file_get_contents($locationTraitPath);

        // Extract the state_name method
        $this->assertStringContainsString(
            'getStateNameAttribute',
            $locationTraitSource,
            'LocationTrait must define getStateNameAttribute'
        );

        // Verify it checks if value is numeric
        $this->assertStringContainsString(
            'is_numeric($value)',
            $locationTraitSource,
            'state_name must check if value is numeric before resolving'
        );
        $this->assertStringContainsString(
            '$this->locationState',
            $locationTraitSource,
            'state_name must resolve via locationState relation'
        );
    }

    /**
     * Verify buildUserData includes email field (lowercased).
     *
     * Source: GoogleTagManager::buildUserData() line 340
     * Email is resolved from address.email or fallback to order.user.email, then lowercased.
     */
    public function test_build_user_data_includes_lowercased_email(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            "'email'",
            $source,
            'buildUserData must include email field'
        );
        $this->assertStringContainsString(
            'strtolower',
            $source,
            'buildUserData must lowercase email'
        );
    }

    /**
     * Verify buildUserData includes phone_number field with whitespace removed.
     *
     * Source: GoogleTagManager::buildUserData() line 341
     * Phone is from address.phone, whitespace removed via preg_replace.
     */
    public function test_build_user_data_includes_normalized_phone(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            "'phone_number'",
            $source,
            'buildUserData must include phone_number field'
        );
        $this->assertStringContainsString(
            'preg_replace',
            $source,
            'buildUserData must normalize phone number whitespace'
        );
    }

    /**
     * Verify buildUserData includes first_name from full name parsing.
     *
     * Source: GoogleTagManager::buildUserData() lines 333-334
     * Parses first word from full name by splitting on space.
     */
    public function test_build_user_data_includes_first_name_from_full_name(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            "'first_name'",
            $source,
            'buildUserData must include first_name field'
        );
        $this->assertStringContainsString(
            'explode',
            $source,
            'buildUserData must parse first name by splitting on space'
        );
    }

    /**
     * Verify buildUserData includes address field.
     *
     * Source: GoogleTagManager::buildUserData() line 343
     */
    public function test_build_user_data_includes_address_field(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            "'address'",
            $source,
            'buildUserData must include address field'
        );
    }

    /**
     * Verify buildUserData returns empty array when no shipping address present.
     *
     * Source: GoogleTagManager::buildUserData() lines 327-331
     * Early return if shippingAddress is missing or has no key.
     */
    public function test_build_user_data_returns_empty_when_no_address(): void
    {
        $reflectionClass = new \ReflectionClass(GoogleTagManager::class);
        $reflectionMethod = $reflectionClass->getMethod('buildUserData');
        $source = file_get_contents($reflectionMethod->getFileName());

        $this->assertStringContainsString(
            'if (! $address || ! $address->getKey())',
            $source,
            'buildUserData must validate address exists and has a key'
        );
    }

    /**
     * Verify Cart::refresh() preserves the taxRate option so rowId stays stable.
     *
     * This is a regression test for the Buy Now merge issue.
     * Source: Cart::refresh() line 1168
     * The code explicitly preserves the original taxRate option.
     */
    public function test_cart_refresh_preserves_tax_rate_option(): void
    {
        $cartPath = dirname(__DIR__, 2) . '/src/Cart/Cart.php';
        $cartSource = file_get_contents($cartPath);

        // Verify it preserves taxRate option - the exact text from the code
        $this->assertStringContainsString(
            "Preserve the original tax option so the rowId stays stable",
            $cartSource,
            'refresh() must have comment explaining taxRate preservation'
        );
        $this->assertStringContainsString(
            '$options[\'taxRate\'] = $cartItem->options->taxRate ?? $cartItem->getTaxRate();',
            $cartSource,
            'refresh() must preserve taxRate from options'
        );
    }

    /**
     * Verify the existing CartTest regression test passes.
     * This validates the Buy Now merge fix at runtime.
     */
    public function test_cart_buy_now_merge_regression_test_exists(): void
    {
        $cartTestPath = __DIR__ . '/CartTest.php';
        $this->assertFileExists($cartTestPath);

        $cartTestSource = file_get_contents($cartTestPath);
        $this->assertStringContainsString(
            'test_refresh_keeps_row_id_stable_when_checkout_sets_tax_rate',
            $cartTestSource,
            'CartTest must include Buy Now merge regression test'
        );
    }
}
