<?php

namespace Botble\Ecommerce\AdsTracking;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\SeoHelper\Facades\SeoHelper;

class FacebookPixel
{
    protected array $events = [];
    protected array $eventIds = [];
    protected FacebookPixelEnhanced $enhanced;

    protected const NO_OFFSET_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW',
        'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF',
        'XOF', 'XPF',
    ];

    public function __construct()
    {
        $this->enhanced = new FacebookPixelEnhanced();
    }

    public static function formatValue(?float $value, ?string $currency = null): float
    {
        if ($value === null) {
            return 0.0;
        }

        $currencyCode = $currency ?: get_application_currency()->title;

        if (in_array(strtoupper($currencyCode), self::NO_OFFSET_CURRENCIES)) {
            return round($value, 0);
        }

        return round($value, 2);
    }

    protected function formatValueForFacebook(?float $value, ?string $currency = null): float
    {
        return static::formatValue($value, $currency);
    }

    public function view(Product $product): static
    {
        if ($this->enhanced->isEnabled()) {
            $this->enhanced->viewContent($product);

            return $this;
        }

        $currency = get_application_currency()->title;

        $this->pushEvent('ViewContent', [$product], [
            'content_category' => $product->categories()->first()->name ?? '',
            'content_name' => $product->name,
            'content_type' => 'product',
            'contents' => [
                [
                    'id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'currency' => $currency,
            'value' => $this->formatValueForFacebook($product->price, $currency),
        ]);

        return $this;
    }

    public function checkout(array $items, float $value): static
    {
        if ($this->enhanced->isEnabled()) {
            $this->enhanced->initiateCheckout($items, $value);

            return $this;
        }

        $currency = get_application_currency()->title;

        $this->pushEvent('InitiateCheckout', $items, [
            'content_name' => 'Checkout',
            'contents' => array_map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->cartItem->qty,
            ], $items),
            'currency' => $currency,
            'num_items' => count($items),
            'value' => $this->formatValueForFacebook($value, $currency),
        ]);

        return $this;
    }

    public function purchase(Order $order): static
    {
        if ($this->enhanced->isEnabled()) {
            $this->enhanced->purchase($order);

            return $this;
        }

        $products = $order->getOrderProducts()->all();
        $currency = get_application_currency()->title;

        $this->pushEvent('Purchase', $products, [
            'content_name' => 'Purchase',
            'content_type' => 'product',
            'contents' => $order->products->map(fn ($item) => [
                'id' => $item->product_id,
                'quantity' => $item->qty,
            ])->values()->all(),
            'currency' => $currency,
            'value' => $this->formatValueForFacebook($order->sub_total, $currency),
        ], $order->code);

        return $this;
    }

    public function addToCart(Product $product, int $quantity, float $value): self
    {
        if ($this->enhanced->isEnabled()) {
            $this->enhanced->addToCart($product, $quantity, $value);

            return $this;
        }

        $currency = get_application_currency()->title;

        $this->pushEvent('AddToCart', [$product], [
            'content_name' => 'Add to Cart',
            'content_type' => 'product',
            'contents' => [
                [
                    'id' => $product->id,
                    'quantity' => $quantity,
                ],
            ],
            'currency' => $currency,
            'value' => $this->formatValueForFacebook($value, $currency),
        ]);

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enhanced->isEnabled();
    }

    public function pushEvent(string $event, array $items = [], array $data = [], ?string $eventId = null): void
    {
        $this->events[$event] = [
            ...$data,
            'content_ids' => array_map(fn ($item) => (string) $item->id, $items),
        ];

        if ($eventId !== null) {
            $this->eventIds[$event] = $eventId;
        }
    }

    public function render(): string
    {
        if ($this->enhanced->isEnabled()) {
            return $this->enhanced->render();
        }

        if (empty($this->events)) {
            return '';
        }

        $content = '';

        foreach ($this->events as $event => $data) {
            $eventIdSuffix = '';

            if (! empty($this->eventIds[$event])) {
                $eventIdSuffix = ', ' . json_encode(['eventID' => $this->eventIds[$event]]);
            }

            $content .= "fbq('track', '$event', " . json_encode($data) . $eventIdSuffix . ');';
        }

        return <<<HTML
            <script>
                if (typeof fbq !== "undefined") {
                    $content
                }
            </script>
        HTML;
    }

    public function pushScriptsToFooter(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        add_filter(THEME_FRONT_FOOTER, function (?string $html) {
            return $html . view(EcommerceHelper::viewPath('includes.facebook-pixel-script'))->render() . $this->render();
        }, 999);

        add_filter('ecommerce_checkout_footer', function (?string $html) {
            return $html . SeoHelper::meta()->getAnalytics()->render() . $this->render();
        }, 999);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->enhanced, $method)) {
            $this->enhanced->$method(...$parameters);

            return $this;
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
