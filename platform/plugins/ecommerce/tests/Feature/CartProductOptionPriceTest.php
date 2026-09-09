<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Cart\CartItem;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\GlobalOption;
use Botble\Ecommerce\Models\Option;
use Botble\Ecommerce\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartProductOptionPriceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();

        parent::tearDown();
    }

    protected function makeProductOptionData(float $affectPrice, int $affectType = 0, string $optionType = 'select'): array
    {
        return [
            'optionCartValue' => [
                1 => [
                    [
                        'option_value' => 'Size XL',
                        'affect_price' => $affectPrice,
                        'affect_type' => $affectType,
                        'option_type' => $optionType,
                    ],
                ],
            ],
            'optionInfo' => [
                1 => 'Size',
            ],
        ];
    }

    protected function makeMultiOptionData(array $options): array
    {
        $optionCartValue = [];
        $optionInfo = [];

        foreach ($options as $index => $option) {
            $key = $index + 1;
            $optionCartValue[$key] = [
                [
                    'option_value' => $option['value'],
                    'affect_price' => $option['affect_price'],
                    'affect_type' => $option['affect_type'] ?? 0,
                    'option_type' => $option['option_type'] ?? 'select',
                ],
            ];
            $optionInfo[$key] = $option['name'];
        }

        return [
            'optionCartValue' => $optionCartValue,
            'optionInfo' => $optionInfo,
        ];
    }

    public function test_positional_add_includes_option_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            10.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $this->assertEquals(15.00, $cartItem->price);
        $this->assertEquals(15.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_array_add_includes_option_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(15.00, $cartItem->price);
        $this->assertEquals(15.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_array_add_with_multiple_quantity_calculates_correct_subtotal(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 3,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(15.00, $cartItem->price);
        $this->assertEquals(45.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_positional_add_with_multiple_quantity_calculates_correct_subtotal(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(8.00);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            4,
            20.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $this->assertEquals(28.00, $cartItem->price);
        $this->assertEquals(112.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_array_add_applies_percentage_based_option_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10, 1);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(110.00, $cartItem->price);
    }

    public function test_positional_add_applies_percentage_based_option_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 200.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(25, 1);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            200.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $this->assertEquals(250.00, $cartItem->price);
    }

    public function test_array_add_applies_multiple_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 50.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeMultiOptionData([
            ['name' => 'Size', 'value' => 'XL', 'affect_price' => 5.00],
            ['name' => 'Color', 'value' => 'Gold', 'affect_price' => 3.00],
        ]);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 50.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(58.00, $cartItem->price);
    }

    public function test_array_add_applies_mixed_fixed_and_percentage_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeMultiOptionData([
            ['name' => 'Size', 'value' => 'XL', 'affect_price' => 10.00, 'affect_type' => 0],
            ['name' => 'Finish', 'value' => 'Premium', 'affect_price' => 20, 'affect_type' => 1],
        ]);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // 100 + 10 (fixed) + 20 (20% of 100) = 130
        $this->assertEquals(130.00, $cartItem->price);
    }

    public function test_array_add_sets_correct_tax_rate(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100.00,
            'options' => [
                'taxRate' => 20,
            ],
        ]);

        $this->assertEquals(20, $cartItem->getTaxRate());
    }

    public function test_positional_add_sets_correct_tax_rate(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'taxRate' => 15,
            ]
        );

        $this->assertEquals(15, $cartItem->getTaxRate());
    }

    public function test_array_add_without_options_uses_base_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => 25.00,
            'options' => [],
        ]);

        $this->assertEquals(25.00, $cartItem->price);
        $this->assertEquals(50.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_positional_and_array_add_produce_same_price(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        $cartItemPositional = Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            1,
            10.00,
            [
                'options' => $productOptions,
                'taxRate' => 10,
            ]
        );

        $cartItemArray = Cart::instance('cart')->add([
            'id' => $product2->id,
            'name' => $product2->name,
            'qty' => 1,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 10,
            ],
        ]);

        $this->assertEquals($cartItemPositional->price, $cartItemArray->price);
        $this->assertEquals($cartItemPositional->getTaxRate(), $cartItemArray->getTaxRate());
    }

    public function test_positional_and_array_add_produce_same_price_with_multiple_options(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 50.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 50.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeMultiOptionData([
            ['name' => 'Size', 'value' => 'XL', 'affect_price' => 5.00],
            ['name' => 'Color', 'value' => 'Gold', 'affect_price' => 3.00],
        ]);

        $cartItemPositional = Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            2,
            50.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $cartItemArray = Cart::instance('cart')->add([
            'id' => $product2->id,
            'name' => $product2->name,
            'qty' => 2,
            'price' => 50.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(58.00, $cartItemPositional->price);
        $this->assertEquals($cartItemPositional->price, $cartItemArray->price);
    }

    public function test_refresh_preserves_option_prices(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
            'is_variation' => false,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            2,
            10.00,
            [
                'image' => 'test.jpg',
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $this->assertEquals(30.00, Cart::instance('cart')->rawSubTotal());

        Cart::instance('cart')->refresh();

        $refreshedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($refreshedItem);
        $this->assertEquals(15.00, $refreshedItem->price);
        $this->assertEquals(30.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_option_price_preserved_in_cart_content(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $content = Cart::instance('cart')->content();
        $this->assertCount(1, $content);

        $item = $content->first();
        $this->assertEquals(15.00, $item->price);
        $this->assertEquals(1, $item->qty);
    }

    public function test_option_with_zero_affect_price_does_not_change_price(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(0);

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals(10.00, $cartItem->price);
    }

    public function test_multiple_items_with_different_options_in_cart(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 20.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $options1 = $this->makeProductOptionData(5.00);
        $options2 = $this->makeProductOptionData(8.00);

        Cart::instance('cart')->add([
            'id' => $product1->id,
            'name' => $product1->name,
            'qty' => 1,
            'price' => 10.00,
            'options' => [
                'options' => $options1,
                'taxRate' => 0,
            ],
        ]);

        Cart::instance('cart')->add([
            'id' => $product2->id,
            'name' => $product2->name,
            'qty' => 2,
            'price' => 20.00,
            'options' => [
                'options' => $options2,
                'taxRate' => 0,
            ],
        ]);

        // Product A: 10 + 5 = 15, qty 1 = 15
        // Product B: 20 + 8 = 28, qty 2 = 56
        // Total: 15 + 56 = 71
        $this->assertEquals(71.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_get_price_by_options_with_fixed_price(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select'],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertIsArray($result);
        $this->assertEquals(15.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_get_price_by_options_with_percentage_price(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Premium', 'affect_price' => 20, 'affect_type' => 1, 'option_type' => 'select'],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(100.00, $options);

        $this->assertEquals(120.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_get_price_by_options_with_multiple_values(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select'],
                ],
                2 => [
                    ['option_value' => 'Gold', 'affect_price' => 3.00, 'affect_type' => 0, 'option_type' => 'select'],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertEquals(18.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_get_price_by_options_skips_field_type(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Custom text', 'affect_price' => 0, 'affect_type' => 0, 'option_type' => 'field'],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertEquals(10.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_get_price_by_options_with_empty_options(): void
    {
        $result = Cart::instance('cart')->getPriceByOptions(10.00, []);

        $this->assertEquals(10.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_get_price_by_options_with_empty_option_cart_value(): void
    {
        $options = ['optionCartValue' => []];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertEquals(10.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_per_product_option_price_applied_once(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertEquals(10.00, $result['price']);
        $this->assertEquals(5.00, $result['option_price_once']);
    }

    public function test_per_product_option_with_multiple_quantity(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 3,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // base price stays 10.00 (not increased by per-product option)
        $this->assertEquals(10.00, $cartItem->price);
        // subtotal = 10 * 3 + 5 = 35
        $this->assertEquals(35.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_mixed_per_qty_and_per_product_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Size XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => false],
                ],
                2 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 3.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Size', 2 => 'Wrapping'],
        ];

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // price includes per-qty option: 10 + 5 = 15
        $this->assertEquals(15.00, $cartItem->price);
        // subtotal = 15 * 2 + 3 = 33
        $this->assertEquals(33.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_per_product_percentage_option(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Premium', 'affect_price' => 10, 'affect_type' => 1, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(100.00, $options);

        // 10% of 100 = 10, applied once
        $this->assertEquals(100.00, $result['price']);
        $this->assertEquals(10.00, $result['option_price_once']);
    }

    public function test_per_product_option_in_cart_item_subtotal(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 20.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 8.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 4,
            'price' => 20.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $cartItem = Cart::instance('cart')->content()->first();

        // subtotal = 20 * 4 + 8 = 88
        $this->assertEquals(88.00, $cartItem->subtotal);
    }

    public function test_positional_add_with_per_product_option(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        $cartItem = Cart::instance('cart')->add(
            $product->id,
            $product->name,
            3,
            10.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // per-product option does not increase unit price
        $this->assertEquals(10.00, $cartItem->price);
        // subtotal = 10 * 3 + 5 = 35
        $this->assertEquals(35.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_positional_and_array_add_produce_same_result_with_per_product_option(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        $cartItemPositional = Cart::instance('cart')->add(
            $product1->id,
            $product1->name,
            2,
            10.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        $cartItemArray = Cart::instance('cart')->add([
            'id' => $product2->id,
            'name' => $product2->name,
            'qty' => 2,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $this->assertEquals($cartItemPositional->price, $cartItemArray->price);
        $this->assertEquals(10.00, $cartItemPositional->price);
        // Both items: (10*2 + 5) * 2 = 50
        $this->assertEquals(50.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_per_product_option_with_qty_one_same_as_per_qty(): void
    {
        $optionsPerProduct = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $optionsPerQty = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => false],
                ],
            ],
        ];

        $resultPerProduct = Cart::instance('cart')->getPriceByOptions(10.00, $optionsPerProduct);
        $resultPerQty = Cart::instance('cart')->getPriceByOptions(10.00, $optionsPerQty);

        // With qty=1, effective total is the same: base(10) + option(5) = 15
        $effectivePerProduct = $resultPerProduct['price'] * 1 + $resultPerProduct['option_price_once'];
        $effectivePerQty = $resultPerQty['price'] * 1 + $resultPerQty['option_price_once'];

        $this->assertEquals($effectivePerProduct, $effectivePerQty);
        $this->assertEquals(15.00, $effectivePerProduct);
    }

    public function test_all_options_per_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 3.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
                2 => [
                    ['option_value' => 'Insurance', 'affect_price' => 7.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping', 2 => 'Insurance'],
        ];

        $cartItem = Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 5,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // unit price unchanged
        $this->assertEquals(10.00, $cartItem->price);
        // subtotal = 10*5 + 3 + 7 = 60
        $this->assertEquals(60.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_option_price_once_stored_in_cart_item_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $cartItem = Cart::instance('cart')->content()->first();
        $this->assertEquals(5.00, $cartItem->options->get('option_price_once'));
    }

    public function test_no_per_product_options_stores_zero_option_price_once(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 2,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $cartItem = Cart::instance('cart')->content()->first();
        $this->assertEquals(0, $cartItem->options->get('option_price_once'));
    }

    public function test_per_product_option_with_zero_affect_price(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Free wrap', 'affect_price' => 0, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertEquals(10.00, $result['price']);
        $this->assertEquals(0, $result['option_price_once']);
    }

    public function test_multiple_cart_items_with_different_per_product_settings(): void
    {
        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 20.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Product A: per-qty option +5
        $options1 = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select'],
                ],
            ],
            'optionInfo' => [1 => 'Size'],
        ];

        // Product B: per-product option +8
        $options2 = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 8.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product1->id,
            'name' => $product1->name,
            'qty' => 2,
            'price' => 10.00,
            'options' => [
                'options' => $options1,
                'taxRate' => 0,
            ],
        ]);

        Cart::instance('cart')->add([
            'id' => $product2->id,
            'name' => $product2->name,
            'qty' => 3,
            'price' => 20.00,
            'options' => [
                'options' => $options2,
                'taxRate' => 0,
            ],
        ]);

        // Product A: (10+5)*2 = 30
        // Product B: 20*3 + 8 = 68
        // Total: 30 + 68 = 98
        $this->assertEquals(98.00, Cart::instance('cart')->rawSubTotal());
    }

    public function test_per_product_option_cart_item_total_without_tax(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 6.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 3,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $cartItem = Cart::instance('cart')->content()->first();

        // total (no tax) = 10*3 + 6 = 36
        $this->assertEquals(36.00, $cartItem->total);
    }

    public function test_per_product_option_raw_total(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 4.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 3,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // rawTotal = 10*3 + 4 = 34
        $this->assertEquals(34.00, Cart::instance('cart')->rawTotal());
    }

    public function test_per_product_option_raw_total_by_items(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Gift Wrap', 'affect_price' => 4.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
            'optionInfo' => [1 => 'Wrapping'],
        ];

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 3,
            'price' => 10.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        $content = Cart::instance('cart')->content();

        // rawTotalByItems = 10*3 + 4 = 34
        $this->assertEquals(34.00, Cart::instance('cart')->rawTotalByItems($content));
    }

    public function test_per_product_option_difference_vs_per_qty(): void
    {
        // Verify the core business case: per-product gives different subtotal than per-qty at qty > 1
        $optionsPerProduct = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $optionsPerQty = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'Wrap', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => false],
                ],
            ],
        ];

        $qty = 3;
        $basePrice = 10.00;

        $resultPerProduct = Cart::instance('cart')->getPriceByOptions($basePrice, $optionsPerProduct);
        $resultPerQty = Cart::instance('cart')->getPriceByOptions($basePrice, $optionsPerQty);

        $subtotalPerProduct = $resultPerProduct['price'] * $qty + $resultPerProduct['option_price_once'];
        $subtotalPerQty = $resultPerQty['price'] * $qty + $resultPerQty['option_price_once'];

        // Per-product: 10*3 + 5 = 35
        $this->assertEquals(35.00, $subtotalPerProduct);
        // Per-qty: (10+5)*3 = 45
        $this->assertEquals(45.00, $subtotalPerQty);
        // They differ
        $this->assertNotEquals($subtotalPerProduct, $subtotalPerQty);
    }

    public function test_mixed_percentage_per_product_and_fixed_per_qty(): void
    {
        $options = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 10.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => false],
                ],
                2 => [
                    ['option_value' => 'Premium', 'affect_price' => 20, 'affect_type' => 1, 'option_type' => 'select', 'price_per_product' => true],
                ],
            ],
        ];

        $result = Cart::instance('cart')->getPriceByOptions(100.00, $options);

        // Per-qty fixed: 100 + 10 = 110
        $this->assertEquals(110.00, $result['price']);
        // Per-product percentage: 20% of 100 = 20
        $this->assertEquals(20.00, $result['option_price_once']);
    }

    public function test_per_product_option_explicit_false_same_as_default(): void
    {
        $optionsExplicitFalse = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select', 'price_per_product' => false],
                ],
            ],
        ];

        $optionsNoFlag = [
            'optionCartValue' => [
                1 => [
                    ['option_value' => 'XL', 'affect_price' => 5.00, 'affect_type' => 0, 'option_type' => 'select'],
                ],
            ],
        ];

        $resultExplicit = Cart::instance('cart')->getPriceByOptions(10.00, $optionsExplicitFalse);
        $resultDefault = Cart::instance('cart')->getPriceByOptions(10.00, $optionsNoFlag);

        $this->assertEquals($resultExplicit, $resultDefault);
        $this->assertEquals(15.00, $resultExplicit['price']);
        $this->assertEquals(0, $resultExplicit['option_price_once']);
    }

    public function test_model_fillable_includes_price_per_product(): void
    {
        $option = new Option();
        $this->assertContains('price_per_product', $option->getFillable());

        $globalOption = new GlobalOption();
        $this->assertContains('price_per_product', $globalOption->getFillable());
    }

    public function test_global_option_can_store_price_per_product(): void
    {
        $option = GlobalOption::query()->create([
            'name' => 'Wrapping',
            'option_type' => 'Botble\\Ecommerce\\Option\\OptionType\\Checkbox',
            'required' => false,
            'price_per_product' => true,
        ]);

        $this->assertTrue((bool) $option->price_per_product);

        $fresh = GlobalOption::query()->find($option->id);
        $this->assertTrue((bool) $fresh->price_per_product);
    }

    public function test_global_option_price_per_product_defaults_to_false(): void
    {
        $option = GlobalOption::query()->create([
            'name' => 'Size',
            'option_type' => 'Botble\\Ecommerce\\Option\\OptionType\\Dropdown',
            'required' => false,
        ]);

        $fresh = GlobalOption::query()->find($option->id);
        $this->assertFalse((bool) $fresh->price_per_product);
    }

    public function test_option_can_store_price_per_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $option = Option::query()->create([
            'name' => 'Wrapping',
            'option_type' => 'Botble\\Ecommerce\\Option\\OptionType\\Checkbox',
            'required' => false,
            'price_per_product' => true,
            'product_id' => $product->id,
            'order' => 0,
        ]);

        $fresh = Option::query()->find($option->id);
        $this->assertTrue((bool) $fresh->price_per_product);
    }

    public function test_get_price_by_options_returns_array_not_scalar(): void
    {
        $options = $this->makeProductOptionData(5.00);

        $result = Cart::instance('cart')->getPriceByOptions(10.00, $options);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('option_price_once', $result);
        $this->assertIsFloat($result['price']);
    }

    public function test_cart_item_from_attributes_with_get_price_by_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(5.00);
        $basePrice = $product->price;

        $priceResult = Cart::instance('cart')->getPriceByOptions($basePrice, $productOptions);
        $price = $priceResult['price'];

        $options = [
            'options' => $productOptions,
            'taxRate' => 0,
            'option_price_once' => $priceResult['option_price_once'],
        ];

        $cartItem = CartItem::fromAttributes(
            $product->id,
            $product->name,
            $price,
            $options
        );

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals(15.00, $cartItem->price);
        $this->assertEquals($product->id, $cartItem->id);
    }

    public function test_cart_item_from_attributes_with_empty_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $priceResult = Cart::instance('cart')->getPriceByOptions($product->price, []);
        $price = $priceResult['price'];

        $cartItem = CartItem::fromAttributes(
            $product->id,
            $product->name,
            $price,
            ['option_price_once' => $priceResult['option_price_once']]
        );

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals(25.00, $cartItem->price);
        $this->assertEquals(0, $cartItem->options['option_price_once']);
    }

    public function test_cart_item_from_attributes_with_per_product_options(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 10.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = [
            'optionCartValue' => [
                1 => [
                    [
                        'option_value' => 'Gift Wrap',
                        'affect_price' => 5.00,
                        'affect_type' => 0,
                        'option_type' => 'checkbox',
                        'price_per_product' => true,
                    ],
                ],
            ],
        ];

        $priceResult = Cart::instance('cart')->getPriceByOptions($product->price, $productOptions);
        $price = $priceResult['price'];

        $cartItem = CartItem::fromAttributes(
            $product->id,
            $product->name,
            $price,
            [
                'options' => $productOptions,
                'option_price_once' => $priceResult['option_price_once'],
            ]
        );

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals(10.00, $price);
        $this->assertEquals(5.00, $priceResult['option_price_once']);
    }
}
