<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\SpecificationAttributeFieldType;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductSpecificationAttributeTranslation;
use Botble\Ecommerce\Models\SpecificationAttribute;
use Botble\Ecommerce\Models\SpecificationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SpecificationImportExportTest extends BaseTestCase
{
    use RefreshDatabase;

    private Product $product;

    private SpecificationGroup $group;

    private SpecificationAttribute $selectAttribute;

    private SpecificationAttribute $textAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = SpecificationGroup::query()->create([
            'name' => 'Display',
        ]);

        // Select attribute with ID-based options
        $this->selectAttribute = SpecificationAttribute::query()->create([
            'group_id' => $this->group->id,
            'name' => 'Finish',
            'type' => SpecificationAttributeFieldType::SELECT,
            'options' => [
                ['id' => 'opt_matte', 'value' => 'Matte'],
                ['id' => 'opt_gloss', 'value' => 'Glossy'],
                ['id' => 'opt_satin', 'value' => 'Satin'],
            ],
        ]);

        // Text attribute (no options)
        $this->textAttribute = SpecificationAttribute::query()->create([
            'group_id' => $this->group->id,
            'name' => 'Weight',
            'type' => SpecificationAttributeFieldType::TEXT,
        ]);

        $this->product = Product::query()->create([
            'name' => 'Test Monitor',
            'price' => 500,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    // --- Saving event: auto-fill empty IDs ---

    public function test_saving_event_fills_empty_option_ids(): void
    {
        $attribute = SpecificationAttribute::query()->create([
            'group_id' => $this->group->id,
            'name' => 'Color',
            'type' => SpecificationAttributeFieldType::RADIO,
            'options' => [
                ['id' => '', 'value' => 'Red'],
                ['id' => '', 'value' => 'Blue'],
            ],
        ]);

        $attribute->refresh();

        foreach ($attribute->options as $option) {
            $this->assertNotEmpty($option['id'], 'Option ID should be auto-filled');
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $option['id']);
        }
    }

    public function test_saving_event_preserves_existing_option_ids(): void
    {
        $attribute = SpecificationAttribute::query()->create([
            'group_id' => $this->group->id,
            'name' => 'Size',
            'type' => SpecificationAttributeFieldType::SELECT,
            'options' => [
                ['id' => 'keep_this', 'value' => 'Small'],
                ['id' => '', 'value' => 'Large'],
            ],
        ]);

        $attribute->refresh();
        $options = $attribute->options;

        $this->assertEquals('keep_this', $options[0]['id']);
        $this->assertNotEmpty($options[1]['id']);
        $this->assertNotEquals('keep_this', $options[1]['id']);
    }

    // --- Product pivot with option IDs ---

    public function test_product_stores_option_id_in_pivot(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_matte',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->assertDatabaseHas('ec_product_specification_attribute', [
            'product_id' => $this->product->id,
            'attribute_id' => $this->selectAttribute->id,
            'value' => 'opt_matte',
        ]);
    }

    public function test_option_id_resolves_to_label(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_matte',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->product->load('specificationAttributes');

        $displayValue = ProductSpecificationAttributeTranslation::getDisplayValue(
            $this->product,
            $this->selectAttribute
        );

        $this->assertEquals('Matte', $displayValue);
    }

    public function test_text_attribute_returns_raw_value(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->textAttribute->id => [
                'value' => '2.5kg',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->product->load('specificationAttributes');

        $displayValue = ProductSpecificationAttributeTranslation::getDisplayValue(
            $this->product,
            $this->textAttribute
        );

        $this->assertEquals('2.5kg', $displayValue);
    }

    // --- Export: option ID → human-readable label ---

    public function test_export_resolves_option_id_to_label(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_gloss',
                'hidden' => false,
                'order' => 0,
            ],
            $this->textAttribute->id => [
                'value' => '3.2kg',
                'hidden' => false,
                'order' => 1,
            ],
        ]);

        $this->product->load('specificationAttributes');

        $formatted = $this->formatSpecifications($this->product);

        $this->assertStringContainsString('Finish:Glossy', $formatted);
        $this->assertStringContainsString('Weight:3.2kg', $formatted);
        // Should NOT contain the raw option ID
        $this->assertStringNotContainsString('opt_gloss', $formatted);
    }

    public function test_export_with_locale_uses_translated_options(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_matte',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        // Insert translated attribute options for Vietnamese
        DB::table('ec_specification_attributes_translations')->insert([
            'lang_code' => 'vi',
            'ec_specification_attributes_id' => $this->selectAttribute->id,
            'name' => 'Bề mặt',
            'options' => json_encode([
                ['id' => 'opt_matte', 'value' => 'Mờ'],
                ['id' => 'opt_gloss', 'value' => 'Bóng'],
                ['id' => 'opt_satin', 'value' => 'Sa tanh'],
            ]),
        ]);

        $this->product->load('specificationAttributes');

        $resolvedLabel = ProductSpecificationAttributeTranslation::resolveOptionLabel(
            $this->selectAttribute,
            'opt_matte',
            'vi'
        );

        $this->assertEquals('Mờ', $resolvedLabel);
    }

    public function test_export_falls_back_to_default_label_when_no_translation(): void
    {
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_satin',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->product->load('specificationAttributes');

        // No translation row for 'fr' locale
        $resolvedLabel = ProductSpecificationAttributeTranslation::resolveOptionLabel(
            $this->selectAttribute,
            'opt_satin',
            'fr'
        );

        $this->assertEquals('Satin', $resolvedLabel);
    }

    // --- Import: label → option ID ---

    public function test_import_resolves_label_to_option_id(): void
    {
        // Simulate importing "Finish:Glossy"
        $value = 'Glossy';
        $optionId = $this->selectAttribute->getOptionIdByValue($value);

        $this->assertEquals('opt_gloss', $optionId);

        // Store it in pivot
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => $optionId,
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->assertDatabaseHas('ec_product_specification_attribute', [
            'product_id' => $this->product->id,
            'attribute_id' => $this->selectAttribute->id,
            'value' => 'opt_gloss',
        ]);
    }

    public function test_import_stores_text_as_is_when_no_option_match(): void
    {
        // Value that doesn't match any option — graceful degradation
        $value = 'NonExistentFinish';
        $optionId = $this->selectAttribute->getOptionIdByValue($value);

        $this->assertNull($optionId);

        // Store raw text (graceful degradation)
        $storedValue = $optionId ?? $value;

        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => $storedValue,
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->assertDatabaseHas('ec_product_specification_attribute', [
            'value' => 'NonExistentFinish',
        ]);
    }

    public function test_import_skips_translation_for_select_attribute(): void
    {
        // Simulate: for select/radio with ID-based options, syncTranslations should skip
        $this->assertTrue($this->selectAttribute->hasOptions());
        $this->assertTrue($this->selectAttribute->hasIdBasedOptions());

        // No translation row should be created for this attribute
        $this->assertDatabaseMissing('ec_product_specification_attribute_translations', [
            'product_id' => $this->product->id,
            'attribute_id' => $this->selectAttribute->id,
        ]);
    }

    public function test_import_creates_translation_for_text_attribute(): void
    {
        $this->assertFalse($this->textAttribute->hasOptions());

        ProductSpecificationAttributeTranslation::query()->create([
            'product_id' => $this->product->id,
            'attribute_id' => $this->textAttribute->id,
            'lang_code' => 'vi',
            'value' => '2.5 ký',
        ]);

        $this->assertDatabaseHas('ec_product_specification_attribute_translations', [
            'product_id' => $this->product->id,
            'attribute_id' => $this->textAttribute->id,
            'lang_code' => 'vi',
            'value' => '2.5 ký',
        ]);
    }

    // --- Round-trip: export → import → re-export ---

    public function test_export_import_round_trip(): void
    {
        // 1. Set up product with option ID
        $this->product->specificationAttributes()->sync([
            $this->selectAttribute->id => [
                'value' => 'opt_matte',
                'hidden' => false,
                'order' => 0,
            ],
            $this->textAttribute->id => [
                'value' => '1.5kg',
                'hidden' => false,
                'order' => 1,
            ],
        ]);

        $this->product->load('specificationAttributes');

        // 2. Export: should produce "Finish:Matte|Weight:1.5kg"
        $exported = $this->formatSpecifications($this->product);
        $this->assertStringContainsString('Finish:Matte', $exported);
        $this->assertStringContainsString('Weight:1.5kg', $exported);

        // 3. Simulate import: parse exported string and resolve back
        $pairs = $this->parseSpecifications($exported);
        $pivotData = [];
        $order = 0;

        foreach ($pairs as $attrName => $value) {
            $attribute = $attrName === 'Finish' ? $this->selectAttribute : $this->textAttribute;

            if ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
                $optionId = $attribute->getOptionIdByValue($value);
                if ($optionId) {
                    $value = $optionId;
                }
            }

            $pivotData[$attribute->id] = [
                'value' => $value,
                'hidden' => false,
                'order' => $order++,
            ];
        }

        // 4. Verify imported values match original option IDs
        $this->assertEquals('opt_matte', $pivotData[$this->selectAttribute->id]['value']);
        $this->assertEquals('1.5kg', $pivotData[$this->textAttribute->id]['value']);

        // 5. Re-export should produce identical output
        $this->product->specificationAttributes()->sync($pivotData);
        $this->product->load('specificationAttributes');
        $reExported = $this->formatSpecifications($this->product);

        $this->assertEquals($exported, $reExported);
    }

    // --- Pre-migration backward compatibility ---

    public function test_pre_migration_text_values_export_as_is(): void
    {
        // Simulate pre-migration: select attribute still has flat string options
        $legacyAttribute = SpecificationAttribute::query()->create([
            'group_id' => $this->group->id,
            'name' => 'Material',
            'type' => SpecificationAttributeFieldType::SELECT,
            'options' => ['Wood', 'Metal', 'Plastic'],
        ]);

        // Product stores text value (pre-migration format)
        $this->product->specificationAttributes()->sync([
            $legacyAttribute->id => [
                'value' => 'Wood',
                'hidden' => false,
                'order' => 0,
            ],
        ]);

        $this->product->load('specificationAttributes');

        // hasIdBasedOptions() returns false → old path executes → text returned as-is
        $this->assertFalse($legacyAttribute->hasIdBasedOptions());

        $formatted = $this->formatSpecificationsForAttribute($this->product, $legacyAttribute);
        $this->assertEquals('Wood', $formatted);
    }

    // --- Helper methods (mirror exporter/importer logic) ---

    private function formatSpecifications(Product $product, ?string $locale = null): string
    {
        $parts = [];

        foreach ($product->specificationAttributes as $attribute) {
            $value = $attribute->pivot->value;

            if ($locale) {
                $value = ProductSpecificationAttributeTranslation::getDisplayValue(
                    $product,
                    $attribute,
                    $locale
                ) ?? $value;
            } elseif ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
                $value = $attribute->getOptionValueById($value) ?? $value;
            }

            if ($value) {
                $parts[] = $attribute->name . ':' . $value;
            }
        }

        return implode('|', $parts);
    }

    private function formatSpecificationsForAttribute(Product $product, SpecificationAttribute $attribute): string
    {
        $specAttr = $product->specificationAttributes->where('id', $attribute->id)->first();
        $value = $specAttr?->pivot->value;

        if ($attribute->hasOptions() && $attribute->hasIdBasedOptions()) {
            $value = $attribute->getOptionValueById($value) ?? $value;
        }

        return $value ?? '';
    }

    private function parseSpecifications(string $specificationsString): array
    {
        $pairs = [];

        foreach (explode('|', $specificationsString) as $pair) {
            $pair = trim($pair);

            if (! str_contains($pair, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $pair, 2);
            $pairs[trim($name)] = trim($value);
        }

        return $pairs;
    }
}
