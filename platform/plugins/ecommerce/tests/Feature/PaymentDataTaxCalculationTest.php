<?php

namespace Botble\Ecommerce\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderAddress;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Tax;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

class PaymentDataTaxCalculationTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setSetting('ecommerce_tax_enabled', 1);
    }

    protected function setSetting(string $key, mixed $value): void
    {
        Setting::load(true);
        Setting::set($key, $value);
        Setting::save();
        Setting::load(true);
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createTax(float $percentage = 10): Tax
    {
        return Tax::query()->create([
            'title' => "Tax {$percentage}%",
            'percentage' => $percentage,
            'priority' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function createProduct(float $price, bool $priceIncludesTax = false): Product
    {
        return Product::query()->create([
            'name' => 'Test Product',
            'price' => $price,
            'status' => BaseStatusEnum::PUBLISHED,
            'price_includes_tax' => $priceIncludesTax,
            'quantity' => 100,
            'with_storehouse_management' => false,
        ]);
    }

    protected function createOrderWithProduct(
        Customer $customer,
        Product $product,
        float $productPrice,
        int $qty,
        float $taxAmount,
        float $subTotal,
        float $amount,
        bool $priceIncludesTax,
        float $discountAmount = 0
    ): Order {
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'token' => md5(uniqid()),
        ]);

        OrderAddress::query()->create([
            'order_id' => $order->id,
            'name' => 'Test Address',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'country' => 'US',
            'state' => 'CA',
            'city' => 'Los Angeles',
            'address' => '123 Test St',
            'zip_code' => '90001',
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'qty' => $qty,
            'price' => $productPrice,
            'tax_amount' => $taxAmount,
            'options' => ['price_includes_tax' => $priceIncludesTax],
        ]);

        return $order->fresh(['products', 'address']);
    }

    /**
     * Calculate price_per_order using the same logic as HookServiceProvider
     */
    protected function calculatePricePerOrder(OrderProduct $orderProduct, Order $order): float
    {
        $productTotal = $orderProduct->price * $orderProduct->qty;
        $productPriceIncludesTax = Arr::get($orderProduct->options, 'price_includes_tax', false);
        $productTaxAmount = $orderProduct->tax_amount ?? 0;
        $productTax = $productPriceIncludesTax ? 0 : $productTaxAmount;
        $productNetTotal = $productPriceIncludesTax
            ? ($productTotal - $productTaxAmount)
            : $productTotal;
        $productDiscount = $order->sub_total > 0
            ? ($productNetTotal / $order->sub_total * $order->discount_amount)
            : 0;

        return $productTotal + $productTax - $productDiscount;
    }

    public function test_price_includes_tax_does_not_add_tax_again(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(110, true);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 110,
            qty: 1,
            taxAmount: 10,
            subTotal: 100,
            amount: 110,
            priceIncludesTax: true
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(110, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_price_excludes_tax_adds_tax_amount(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(100, false);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 100,
            qty: 1,
            taxAmount: 10,
            subTotal: 100,
            amount: 110,
            priceIncludesTax: false
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(110, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_price_includes_tax_with_discount(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(220, true);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 220,
            qty: 2,
            taxAmount: 40,
            subTotal: 400,
            amount: 420,
            priceIncludesTax: true,
            discountAmount: 20
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(420, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_price_excludes_tax_with_discount(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(200, false);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 200,
            qty: 2,
            taxAmount: 40,
            subTotal: 400,
            amount: 420,
            priceIncludesTax: false,
            discountAmount: 20
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(420, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_real_scenario_price_excludes_tax(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(197, false);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 197,
            qty: 1,
            taxAmount: 29.55,
            subTotal: 197,
            amount: 226.55,
            priceIncludesTax: false
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(226.55, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_real_scenario_price_includes_tax(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(658.91, true);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 658.91,
            qty: 1,
            taxAmount: 59.90,
            subTotal: 599.01,
            amount: 658.91,
            priceIncludesTax: true
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(658.91, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_multiple_quantity_price_includes_tax(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(55, true);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 55,
            qty: 3,
            taxAmount: 15,
            subTotal: 150,
            amount: 165,
            priceIncludesTax: true
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(165, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }

    public function test_multiple_quantity_price_excludes_tax(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct(50, false);

        $order = $this->createOrderWithProduct(
            customer: $customer,
            product: $product,
            productPrice: 50,
            qty: 3,
            taxAmount: 15,
            subTotal: 150,
            amount: 165,
            priceIncludesTax: false
        );

        $orderProduct = $order->products->first();
        $pricePerOrder = $this->calculatePricePerOrder($orderProduct, $order);

        $this->assertEquals(165, $pricePerOrder);
        $this->assertEquals($order->amount, $pricePerOrder);
    }
}
