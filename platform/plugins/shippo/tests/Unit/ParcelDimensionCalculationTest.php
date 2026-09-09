<?php

namespace Botble\Shippo\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ParcelDimensionCalculationTest extends TestCase
{
    public function test_single_item_single_quantity(): void
    {
        $items = [
            ['length' => 20, 'wide' => 15, 'height' => 10, 'qty' => 1],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(20, $result['length']);
        $this->assertEquals(15, $result['width']);
        $this->assertEquals(10, $result['height']);
    }

    public function test_single_item_multiple_quantity_stacks_height_only(): void
    {
        $items = [
            ['length' => 20, 'wide' => 15, 'height' => 10, 'qty' => 5],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(20, $result['length']);
        $this->assertEquals(15, $result['width']);
        $this->assertEquals(50, $result['height']);
    }

    public function test_multiple_items_takes_max_length_and_width(): void
    {
        $items = [
            ['length' => 20, 'wide' => 15, 'height' => 10, 'qty' => 1],
            ['length' => 30, 'wide' => 10, 'height' => 5, 'qty' => 1],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(30, $result['length']);
        $this->assertEquals(15, $result['width']);
        $this->assertEquals(15, $result['height']);
    }

    public function test_multiple_items_with_multiple_quantities(): void
    {
        $items = [
            ['length' => 20, 'wide' => 15, 'height' => 10, 'qty' => 3],
            ['length' => 25, 'wide' => 12, 'height' => 8, 'qty' => 2],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(25, $result['length']);
        $this->assertEquals(15, $result['width']);
        $this->assertEquals(46, $result['height']);
    }

    public function test_empty_items_returns_zero_dimensions(): void
    {
        $result = $this->calculateParcelDimensions([]);

        $this->assertEquals(0, $result['length']);
        $this->assertEquals(0, $result['width']);
        $this->assertEquals(0, $result['height']);
    }

    public function test_large_quantity_does_not_inflate_length_or_width(): void
    {
        $items = [
            ['length' => 10, 'wide' => 10, 'height' => 5, 'qty' => 100],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(10, $result['length']);
        $this->assertEquals(10, $result['width']);
        $this->assertEquals(500, $result['height']);
    }

    public function test_mixed_sizes_with_varying_quantities(): void
    {
        $items = [
            ['length' => 50, 'wide' => 30, 'height' => 2, 'qty' => 10],
            ['length' => 15, 'wide' => 15, 'height' => 15, 'qty' => 1],
            ['length' => 40, 'wide' => 25, 'height' => 5, 'qty' => 3],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(50, $result['length']);
        $this->assertEquals(30, $result['width']);
        $this->assertEquals(20 + 15 + 15, $result['height']);
    }

    public function test_single_item_qty_one_dimensions_unchanged(): void
    {
        $items = [
            ['length' => 100, 'wide' => 80, 'height' => 60, 'qty' => 1],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(100, $result['length']);
        $this->assertEquals(80, $result['width']);
        $this->assertEquals(60, $result['height']);
    }

    public function test_zero_dimension_items(): void
    {
        $items = [
            ['length' => 0, 'wide' => 0, 'height' => 0, 'qty' => 5],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEquals(0, $result['length']);
        $this->assertEquals(0, $result['width']);
        $this->assertEquals(0, $result['height']);
    }

    public function test_fractional_dimensions(): void
    {
        $items = [
            ['length' => 10.5, 'wide' => 7.3, 'height' => 2.5, 'qty' => 4],
        ];

        $result = $this->calculateParcelDimensions($items);

        $this->assertEqualsWithDelta(10.5, $result['length'], 0.01);
        $this->assertEqualsWithDelta(7.3, $result['width'], 0.01);
        $this->assertEqualsWithDelta(10.0, $result['height'], 0.01);
    }

    /**
     * Mirrors the parcel dimension logic in Shippo::prepareParcelInfo().
     */
    protected function calculateParcelDimensions(array $items): array
    {
        $length = 0;
        $width = 0;
        $height = 0;

        foreach ($items as $item) {
            $length = max($length, $item['length']);
            $width = max($width, $item['wide']);
            $height += $item['height'] * $item['qty'];
        }

        return compact('length', 'width', 'height');
    }
}
