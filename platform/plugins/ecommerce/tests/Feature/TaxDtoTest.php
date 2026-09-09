<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Tax\DTOs\TaxComponent;
use Botble\Ecommerce\Tax\DTOs\TaxLineItem;
use Botble\Ecommerce\Tax\DTOs\TaxResult;
use Botble\Ecommerce\Tax\Enums\CustomerTaxClass;
use Botble\Ecommerce\Tax\Enums\ProductTaxClass;
use Botble\Ecommerce\Tax\Enums\TaxCalculationType;

class TaxDtoTest extends BaseTestCase
{
    public function test_tax_component_construct_and_to_array(): void
    {
        $component = new TaxComponent(
            name: 'CGST',
            code: 'cgst',
            rate: 9.0,
            amount: 45.0,
            jurisdiction: 'Maharashtra',
            metadata: ['hsn' => '6109'],
        );

        $this->assertEquals('CGST', $component->name);
        $this->assertEquals('cgst', $component->code);
        $this->assertEquals(9.0, $component->rate);
        $this->assertEquals(45.0, $component->amount);
        $this->assertEquals('Maharashtra', $component->jurisdiction);

        $array = $component->toArray();
        $this->assertEquals('CGST', $array['name']);
        $this->assertEquals(['hsn' => '6109'], $array['metadata']);
    }

    public function test_tax_result_zero(): void
    {
        $result = TaxResult::zero();

        $this->assertEquals(0, $result->total_tax);
        $this->assertEquals(0, $result->tax_rate);
        $this->assertEmpty($result->components);
        $this->assertFalse($result->price_includes_tax);
    }

    public function test_tax_result_merge(): void
    {
        $a = new TaxResult(
            total_tax: 9,
            tax_rate: 9,
            components: [new TaxComponent(name: 'CGST', code: 'cgst', rate: 9, amount: 9)],
        );
        $b = new TaxResult(
            total_tax: 9,
            tax_rate: 9,
            components: [new TaxComponent(name: 'SGST', code: 'sgst', rate: 9, amount: 9)],
        );

        $merged = $a->merge($b);

        $this->assertEquals(18, $merged->total_tax);
        $this->assertEquals(18, $merged->tax_rate);
        $this->assertCount(2, $merged->components);
    }

    public function test_tax_result_to_array(): void
    {
        $result = new TaxResult(
            total_tax: 10,
            tax_rate: 10,
            components: [new TaxComponent(name: 'Tax', code: 'tax', rate: 10, amount: 10)],
            price_includes_tax: true,
        );

        $array = $result->toArray();

        $this->assertEquals(10, $array['total_tax']);
        $this->assertCount(1, $array['components']);
        $this->assertTrue($array['price_includes_tax']);
    }

    public function test_tax_line_item_to_array(): void
    {
        $item = new TaxLineItem(
            product_id: 1,
            price: 100,
            quantity: 2,
            tax_rate: 10,
            tax_amount: 20,
            components: [new TaxComponent(name: 'Tax', code: 'tax', rate: 10, amount: 20)],
        );

        $array = $item->toArray();

        $this->assertEquals(1, $array['product_id']);
        $this->assertEquals(100, $array['price']);
        $this->assertEquals(2, $array['quantity']);
        $this->assertEquals(20, $array['tax_amount']);
        $this->assertCount(1, $array['components']);
    }

    public function test_customer_tax_class_enum(): void
    {
        $this->assertEquals('regular', CustomerTaxClass::REGULAR->value);
        $this->assertEquals('business', CustomerTaxClass::BUSINESS->value);
        $this->assertEquals('tax_exempt', CustomerTaxClass::TAX_EXEMPT->value);
        $this->assertEquals('reseller', CustomerTaxClass::RESELLER->value);
        $this->assertSame(CustomerTaxClass::BUSINESS, CustomerTaxClass::from('business'));
    }

    public function test_product_tax_class_enum(): void
    {
        $this->assertEquals('standard', ProductTaxClass::STANDARD->value);
        $this->assertEquals('reduced', ProductTaxClass::REDUCED->value);
        $this->assertEquals('zero_rated', ProductTaxClass::ZERO_RATED->value);
        $this->assertEquals('exempt', ProductTaxClass::EXEMPT->value);
    }

    public function test_tax_calculation_type_enum(): void
    {
        $this->assertEquals('exclusive', TaxCalculationType::EXCLUSIVE->value);
        $this->assertEquals('inclusive', TaxCalculationType::INCLUSIVE->value);
    }
}
