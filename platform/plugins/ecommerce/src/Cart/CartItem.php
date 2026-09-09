<?php

namespace Botble\Ecommerce\Cart;

use Botble\Ecommerce\Cart\Contracts\Buyable;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @property string $created_at
 * @property string $updated_at
 * @property float $priceTax
 * @property-read float $subtotal
 * @property-read float $total
 * @property-read float $tax
 * @property-read float $taxTotal
 */
#[\AllowDynamicProperties]
class CartItem implements Arrayable, Jsonable
{
    public string $rowId;

    public int|string|null $id;

    public int|float $qty;

    public string $name;

    public float $price;

    public array|Collection $options;

    protected ?string $associatedModel = null;

    protected float $taxRate = 0;

    public string|Carbon $updated_at;

    public string|Carbon $created_at;

    public function __construct(int|string|null $id, ?string $name, float $price, array $options = [])
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Please supply a valid identifier.');
        }

        if (empty($name)) {
            throw new InvalidArgumentException('Please supply a valid name.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->options = new CartItemOptions($options);
        $this->rowId = $this->generateRowId($id, $options);
        $this->created_at = Carbon::now();
        $this->updated_at = Carbon::now();
    }

    public function price(): string
    {
        return format_price($this->price);
    }

    public function priceTax(): string
    {
        return format_price($this->priceTax);
    }

    public function subtotal(): string
    {
        return format_price($this->subtotal);
    }

    public function total(): string
    {
        return format_price($this->total);
    }

    public function tax(): string
    {
        return format_price($this->tax);
    }

    public function taxTotal(): string
    {
        return format_price($this->taxTotal);
    }

    public function setQuantity(int|float $qty): void
    {
        if (empty($qty) || ! is_numeric($qty)) {
            throw new InvalidArgumentException('Please supply a valid quantity.');
        }

        $this->qty = $qty;
    }

    public function updateFromBuyable(Buyable $item): void
    {
        $this->id = $item->getBuyableIdentifier($this->options);
        $this->name = $item->getBuyableDescription($this->options);
        $this->price = $item->getBuyablePrice($this->options);
        $this->priceTax = $this->price + $this->tax;
    }

    public function updateFromArray(array $attributes): void
    {
        $this->id = Arr::get($attributes, 'id', $this->id);
        $this->qty = Arr::get($attributes, 'qty', $this->qty) ?: 1;
        $this->name = Arr::get($attributes, 'name', $this->name);
        $this->price = Arr::get($attributes, 'price', $this->price);
        $this->priceTax = $this->price + $this->tax;
        $this->options = new CartItemOptions(Arr::get($attributes, 'options', $this->options));

        $this->rowId = $this->generateRowId($this->id, $this->options->all());
    }

    public function associate($model): static
    {
        $this->associatedModel = is_string($model) ? $model : get_class($model);

        return $this;
    }

    public function setTaxRate(float $taxRate): static
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }

        $optionPriceOnce = $this->options->get('option_price_once', 0);

        if ($attribute === 'priceTax') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            $priceIncludesTax = $this->options->get('price_includes_tax', false);

            if ($priceIncludesTax) {
                return $this->price;
            }

            return $this->price + $this->tax;
        }

        if ($attribute === 'subtotal') {
            return EcommerceHelper::roundPrice($this->qty * $this->price + $optionPriceOnce);
        }

        if ($attribute === 'total') {
            $priceIncludesTax = $this->options->get('price_includes_tax', false);

            if ($priceIncludesTax) {
                return $this->qty * $this->price + $optionPriceOnce;
            }

            return $this->qty * $this->price + $optionPriceOnce + $this->taxTotal;
        }

        if ($attribute === 'tax') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            $priceIncludesTax = $this->options->get('price_includes_tax', false);

            if ($priceIncludesTax) {
                return $this->price - ($this->price / (1 + $this->taxRate / 100));
            }

            return $this->price * ($this->taxRate / 100);
        }

        if ($attribute === 'taxTotal') {
            if (! EcommerceHelper::isTaxEnabled()) {
                return 0;
            }

            $priceIncludesTax = $this->options->get('price_includes_tax', false);
            $itemPrice = $this->price * $this->qty + $optionPriceOnce;

            if ($priceIncludesTax) {
                return EcommerceHelper::roundPrice($itemPrice - ($itemPrice / (1 + $this->taxRate / 100)));
            }

            return EcommerceHelper::roundPrice($itemPrice * ($this->taxRate / 100));
        }

        if ($attribute === 'model') {
            return (new $this->associatedModel())->find($this->id);
        }

        return null;
    }

    public static function fromBuyable(Buyable $item, array $options = []): self
    {
        return new self(
            $item->getBuyableIdentifier($options),
            $item->getBuyableDescription($options),
            $item->getBuyablePrice($options),
            $options
        );
    }

    public static function fromArray(array $attributes): self
    {
        $options = Arr::get($attributes, 'options', []);

        $instance = new self(
            $attributes['id'],
            $attributes['name'],
            (float) $attributes['price'],
            is_array($options) ? $options : []
        );

        // Restore the exact rowId from storage. The constructor recomputes it
        // via md5(serialize($options)), but JSON round-trip can coerce numeric
        // option types (0.0 -> 0), producing a different hash. Without this
        // override the same product would split into two cart rows instead of
        // merging qty on re-add.
        if (! empty($attributes['rowId'])) {
            $instance->rowId = $attributes['rowId'];
        }

        if (isset($attributes['qty']) && is_numeric($attributes['qty']) && $attributes['qty'] > 0) {
            $instance->setQuantity($attributes['qty'] + 0);
        }

        if (isset($attributes['tax_rate'])) {
            $instance->setTaxRate((float) $attributes['tax_rate']);
        }

        if (! empty($attributes['associated_model']) && is_string($attributes['associated_model'])) {
            $instance->associate($attributes['associated_model']);
        }

        $instance->created_at = self::parseTimestamp($attributes['created_at'] ?? null) ?? $instance->created_at;
        $instance->updated_at = self::parseTimestamp($attributes['updated_at'] ?? null) ?? $instance->updated_at;

        return $instance;
    }

    protected static function parseTimestamp(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function fromAttributes(int|string|null $id, string $name, float $price, array $options = []): self
    {
        return new self($id, $name, $price, $options);
    }

    protected function generateRowId(int|string|null $id, array $options): string
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    public function toArray(): array
    {
        return [
            'rowId' => $this->rowId,
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'options' => $this->options instanceof Collection
                ? $this->options->toArray()
                : (array) $this->options,
            'tax' => $this->tax,
            'subtotal' => $this->subtotal,
            'updated_at' => $this->updated_at instanceof Carbon
                ? $this->updated_at->toIso8601String()
                : $this->updated_at,
        ];
    }

    // Extended shape used ONLY by Cart::putToSession to persist across request.
    // Includes internal fields (taxRate, associatedModel, created_at) that should
    // NOT leak through toArray() / toJson() to API consumers.
    public function toSessionArray(): array
    {
        return array_merge($this->toArray(), [
            'tax_rate' => $this->taxRate,
            'associated_model' => $this->associatedModel,
            'created_at' => $this->created_at instanceof Carbon
                ? $this->created_at->toIso8601String()
                : $this->created_at,
        ]);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
