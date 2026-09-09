<?php

namespace Botble\Ecommerce\Tests\Unit;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\SpecificationAttributeFieldType;
use Botble\Ecommerce\Models\SpecificationAttribute;

class SpecificationAttributeHelpersTest extends BaseTestCase
{
    // --- generateOptionId ---

    public function test_generate_option_id_returns_8_char_hex_string(): void
    {
        $id = SpecificationAttribute::generateOptionId();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $id);
    }

    public function test_generate_option_id_produces_unique_values(): void
    {
        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = SpecificationAttribute::generateOptionId();
        }

        $this->assertCount(100, array_unique($ids));
    }

    // --- hasOptions ---

    public function test_has_options_returns_true_for_select_type(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT);

        $this->assertTrue($attribute->hasOptions());
    }

    public function test_has_options_returns_true_for_radio_type(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::RADIO);

        $this->assertTrue($attribute->hasOptions());
    }

    public function test_has_options_returns_false_for_text_type(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::TEXT);

        $this->assertFalse($attribute->hasOptions());
    }

    public function test_has_options_returns_false_for_textarea_type(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::TEXTAREA);

        $this->assertFalse($attribute->hasOptions());
    }

    public function test_has_options_returns_false_for_checkbox_type(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::CHECKBOX);

        $this->assertFalse($attribute->hasOptions());
    }

    // --- hasIdBasedOptions ---

    public function test_has_id_based_options_returns_true_for_id_based_format(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
            ['id' => 'def67890', 'value' => 'Glossy'],
        ]);

        $this->assertTrue($attribute->hasIdBasedOptions());
    }

    public function test_has_id_based_options_returns_false_for_flat_strings(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            'Matte',
            'Glossy',
        ]);

        $this->assertFalse($attribute->hasIdBasedOptions());
    }

    public function test_has_id_based_options_returns_false_for_empty_array(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, []);

        $this->assertFalse($attribute->hasIdBasedOptions());
    }

    public function test_has_id_based_options_returns_false_for_null(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, null);

        $this->assertFalse($attribute->hasIdBasedOptions());
    }

    // --- getIdBasedOptions ---

    public function test_get_id_based_options_returns_existing_id_based_options_as_is(): void
    {
        $options = [
            ['id' => 'abc12345', 'value' => 'Matte'],
            ['id' => 'def67890', 'value' => 'Glossy'],
        ];
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, $options);

        $result = $attribute->getIdBasedOptions();

        $this->assertEquals($options, $result);
    }

    public function test_get_id_based_options_converts_flat_strings_to_id_based(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            'Matte',
            'Glossy',
        ]);

        $result = $attribute->getIdBasedOptions();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertEquals('Matte', $result[0]['value']);
        $this->assertEquals('Glossy', $result[1]['value']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $result[0]['id']);
    }

    public function test_get_id_based_options_returns_empty_for_null_options(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, null);

        $this->assertEquals([], $attribute->getIdBasedOptions());
    }

    public function test_get_id_based_options_returns_empty_for_empty_array(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, []);

        $this->assertEquals([], $attribute->getIdBasedOptions());
    }

    // --- getOptionValueById ---

    public function test_get_option_value_by_id_returns_matching_value(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
            ['id' => 'def67890', 'value' => 'Glossy'],
        ]);

        $this->assertEquals('Matte', $attribute->getOptionValueById('abc12345'));
        $this->assertEquals('Glossy', $attribute->getOptionValueById('def67890'));
    }

    public function test_get_option_value_by_id_returns_null_for_unknown_id(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
        ]);

        $this->assertNull($attribute->getOptionValueById('unknown99'));
    }

    public function test_get_option_value_by_id_returns_null_for_empty_options(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, []);

        $this->assertNull($attribute->getOptionValueById('abc12345'));
    }

    // --- getOptionIdByValue ---

    public function test_get_option_id_by_value_returns_matching_id(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
            ['id' => 'def67890', 'value' => 'Glossy'],
        ]);

        $this->assertEquals('abc12345', $attribute->getOptionIdByValue('Matte'));
        $this->assertEquals('def67890', $attribute->getOptionIdByValue('Glossy'));
    }

    public function test_get_option_id_by_value_returns_null_for_unknown_value(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
        ]);

        $this->assertNull($attribute->getOptionIdByValue('Satin'));
    }

    public function test_get_option_id_by_value_is_case_sensitive(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'abc12345', 'value' => 'Matte'],
        ]);

        $this->assertNull($attribute->getOptionIdByValue('matte'));
        $this->assertEquals('abc12345', $attribute->getOptionIdByValue('Matte'));
    }

    // --- Backward compatibility: legacy flat arrays ---

    public function test_get_option_id_by_value_works_with_legacy_flat_strings(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            'Matte',
            'Glossy',
        ]);

        // Should still find the value via on-the-fly conversion
        $id = $attribute->getOptionIdByValue('Matte');
        $this->assertNotNull($id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $id);
    }

    public function test_get_option_value_by_id_returns_null_for_legacy_format(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            'Matte',
            'Glossy',
        ]);

        // Legacy format generates random IDs each call, so looking up a specific ID won't match
        $this->assertNull($attribute->getOptionValueById('abc12345'));
    }

    // --- Edge cases ---

    public function test_has_id_based_options_with_array_missing_id_key(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['value' => 'Matte'],
            ['value' => 'Glossy'],
        ]);

        $this->assertFalse($attribute->hasIdBasedOptions());
    }

    public function test_get_option_value_by_id_with_multiple_options_same_value(): void
    {
        $attribute = $this->makeAttribute(SpecificationAttributeFieldType::SELECT, [
            ['id' => 'id1', 'value' => 'Same'],
            ['id' => 'id2', 'value' => 'Same'],
        ]);

        // getOptionIdByValue returns first match
        $this->assertEquals('id1', $attribute->getOptionIdByValue('Same'));
        // Both IDs resolve correctly
        $this->assertEquals('Same', $attribute->getOptionValueById('id1'));
        $this->assertEquals('Same', $attribute->getOptionValueById('id2'));
    }

    // --- Helper ---

    private function makeAttribute(
        string $type,
        ?array $options = null,
    ): SpecificationAttribute {
        $attribute = new SpecificationAttribute();
        $attribute->type = $type;
        $attribute->options = $options;

        return $attribute;
    }
}
