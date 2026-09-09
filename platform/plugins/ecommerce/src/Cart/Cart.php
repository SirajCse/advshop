<?php

namespace Botble\Ecommerce\Cart;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Cart\Contracts\Buyable;
use Botble\Ecommerce\Cart\Exceptions\CartAlreadyStoredException;
use Botble\Ecommerce\Cart\Exceptions\UnknownModelException;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Services\HandleApplyProductCrossSaleService;
use Botble\Ecommerce\Services\HandleApplyProductUpSaleService;
use Botble\Ecommerce\Services\Products\ProductUpSalePriceService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Closure;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\NullDispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Cart
{
    protected static Dispatcher $dispatcher;

    public const DEFAULT_INSTANCE = 'cart';

    protected string $instance;

    protected ?Collection $products = null;

    protected float $weight = 0;

    protected array $counts = [];

    public function __construct(protected SessionManager $session, Dispatcher $events)
    {
        static::$dispatcher = $events;

        $this->instance(self::DEFAULT_INSTANCE);
    }

    public function instance(?string $instance = null): self
    {
        $instance = $instance ?: self::DEFAULT_INSTANCE;

        $this->instance = sprintf('%s.%s', 'cart', $instance);

        return $this;
    }

    public function getLastUpdatedAt(): ?CarbonInterface
    {
        $value = $this->session->get($this->instance . '_updated_at');

        if (! $value) {
            return null;
        }

        return $value instanceof CarbonInterface ? $value : Carbon::parse($value);
    }

    public function add($id, $name = null, $qty = null, $price = null, array $options = [])
    {
        if ($this->isMulti($id)) {
            return array_map(function ($item) {
                return $this->add($item);
            }, $id);
        }

        $cartItem = $this->createCartItem($id, $name, $qty, $price, $options);

        $content = $this->getContent();

        if ($content->has($cartItem->rowId)) {
            $cartItem->qty += $content->get($cartItem->rowId)->qty;
        }

        $content->put($cartItem->rowId, $cartItem);

        $this->putToSession($content);

        $this->products = null;

        static::dispatchEvent('cart.added', $cartItem);

        return $cartItem;
    }

    public function addQuietly($id, $name = null, $qty = null, $price = null, array $options = [])
    {
        return static::withoutEvents(
            fn () => $this->add($id, $name, $qty, $price, $options)
        );
    }

    protected function isMulti($item): bool
    {
        if (! is_array($item)) {
            return false;
        }

        $item = reset($item);

        return is_array($item) || $item instanceof Buyable;
    }

    protected function createCartItem($id, $name, $qty, $price, array $options): CartItem
    {
        if (
            EcommerceHelper::isEnabledProductOptions() &&
            ($productOptions = Arr::get($options, 'options', [])) &&
            is_array($productOptions)
        ) {
            $priceResult = $this->getPriceByOptions($price, $productOptions);
            $price = $priceResult['price'];
            $options['option_price_once'] = $priceResult['option_price_once'];
        }

        if ($id instanceof Buyable) {
            $cartItem = CartItem::fromBuyable($id, $qty ?: []);
            $cartItem->setQuantity($name ?: 1);
            $cartItem->associate($id);
        } elseif (is_array($id)) {
            if (
                EcommerceHelper::isEnabledProductOptions() &&
                ($itemProductOptions = Arr::get($id, 'options.options', [])) &&
                is_array($itemProductOptions)
            ) {
                $priceResult = $this->getPriceByOptions($id['price'], $itemProductOptions);
                $id['price'] = $priceResult['price'];
                $id['options']['option_price_once'] = $priceResult['option_price_once'];
            }

            $cartItem = CartItem::fromArray($id);
            $cartItem->setQuantity($id['qty']);
        } else {
            $cartItem = CartItem::fromAttributes($id, $name, $price, $options);
            $cartItem->setQuantity($qty);
        }

        $taxRate = is_array($id) ? Arr::get($id, 'options.taxRate', 0) : ($options['taxRate'] ?? 0);
        $cartItem->setTaxRate($taxRate);

        return $cartItem;
    }

    public function getPriceByOptions(float|int $price, array $options = []): array
    {
        $basePrice = $price;
        $optionPriceOnce = 0;

        foreach (Arr::get($options, 'optionCartValue', []) as $value) {
            if (is_array($value)) {
                foreach ($value as $valueItem) {
                    $affectPrice = $valueItem['affect_price'];

                    if ($valueItem['affect_type'] == 1) {
                        $affectPrice = ($basePrice * $affectPrice) / 100;
                    }

                    if (! empty($valueItem['price_per_product'])) {
                        $optionPriceOnce += $affectPrice;
                    } else {
                        $price += $affectPrice;
                    }
                }
            } else {
                if (Arr::get($value, 'option_type') == 'field') {
                    continue;
                }

                $affectPrice = $value['affect_price'];

                if ($value['affect_type'] == 1) {
                    $affectPrice = ($basePrice * $affectPrice) / 100;
                }

                if (! empty($value['price_per_product'])) {
                    $optionPriceOnce += $affectPrice;
                } else {
                    $price += $affectPrice;
                }
            }
        }

        return [
            'price' => $price,
            'option_price_once' => $optionPriceOnce,
        ];
    }

    protected function getContent(): Collection
    {
        if (! $this->session->has($this->instance)) {
            return new Collection();
        }

        $raw = $this->session->get($this->instance);

        // PHP session serializer path — already a Collection of CartItem objects.
        if ($raw instanceof Collection) {
            return $raw;
        }

        // JSON session serializer path — stored as array<rowId, array>.
        // Rehydrate into Collection<CartItem> to preserve the public contract.
        if (! is_array($raw)) {
            return new Collection();
        }

        $hydrated = new Collection();

        foreach ($raw as $row) {
            if (! is_array($row) || ! isset($row['id'], $row['name'], $row['price'])) {
                continue;
            }

            $item = CartItem::fromArray($row);
            $hydrated->put($item->rowId, $item);
        }

        return $hydrated;
    }

    public function putToSession($content): static
    {
        $this->setLastUpdatedAt();

        // Under PHP session serializer, write the Collection<CartItem> directly.
        // This preserves any dynamic/__set properties third-party plugins may have
        // attached to cart items. Only convert to array-of-arrays when the JSON
        // serializer is active, which cannot round-trip PHP objects.
        if ($content instanceof Collection && config('session.serialization') === 'json') {
            $payload = $content
                ->map(fn ($item) => $item instanceof CartItem ? $item->toSessionArray() : $item)
                ->all();
        } else {
            $payload = $content;
        }

        $this->session->put($this->instance, $payload);

        return $this;
    }

    public function setLastUpdatedAt(): void
    {
        $this->session->put($this->instance . '_updated_at', Carbon::now()->toIso8601String());
    }

    public function update(string $rowId, int|Buyable|array $qty): bool|CartItem|null
    {
        $cartItem = $this->get($rowId);

        if (! $cartItem) {
            return null;
        }

        if ($qty instanceof Buyable) {
            $cartItem->updateFromBuyable($qty);
        } elseif (is_array($qty)) {
            $cartItem->updateFromArray($qty);
        } else {
            $cartItem->qty = $qty;
        }

        $content = $this->getContent();

        if ($rowId !== $cartItem->rowId) {
            $content->pull($rowId);

            if ($content->has($cartItem->rowId)) {
                $existingCartItem = $this->get($cartItem->rowId);
                $cartItem->setQuantity((int) $existingCartItem->qty + (int) $cartItem->qty);
            }
        }

        if ($cartItem->qty <= 0) {
            $this->remove($cartItem->rowId);

            return false;
        }

        $content->put($cartItem->rowId, $cartItem);

        $cartItem->updated_at = Carbon::now();

        static::dispatchEvent('cart.updated', $cartItem);

        $this->putToSession($content);

        $this->products = null;

        return $cartItem;
    }

    public function updateQuietly($rowId, $qty)
    {
        return static::withoutEvents(fn () => $this->update($rowId, $qty));
    }

    public function get(string $rowId): ?CartItem
    {
        $content = $this->getContent();

        if (! $content->has($rowId)) {
            return null;
        }

        return $content->get($rowId);
    }

    public function remove(string $rowId): void
    {
        $cartItem = $this->get($rowId);

        $content = $this->getContent();

        $content->pull($cartItem->rowId);

        static::dispatchEvent('cart.removed', $cartItem);

        $this->putToSession($content);

        $this->products = null;
    }

    public function removeQuietly($rowId)
    {
        return static::withoutEvents(fn () => $this->remove($rowId));
    }

    public function destroy(): void
    {
        $this->session->remove($this->instance);
    }

    public function count(): int
    {
        if (isset($this->counts[$this->instance])) {
            return $this->counts[$this->instance];
        }

        if ($this->instance == 'cart.wishlist' && auth('customer')->check()) {
            $this->counts[$this->instance] = auth('customer')->user()->wishlist()->count();
        } else {
            $content = $this->getContent();

            $this->counts[$this->instance] = (int) apply_filters('ecommerce_cart_count', $content->sum('qty'), $content, $this->instance);
        }

        return $this->counts[$this->instance];
    }

    public function resetCount(): void
    {
        unset($this->counts[$this->instance]);
    }

    public function resetProducts(): void
    {
        $this->products = null;
    }

    public function isNotEmpty(): bool
    {
        return $this->content()->isNotEmpty();
    }

    public function isEmpty(): bool
    {
        return $this->content()->isEmpty();
    }

    public function countByItems($content): float|int
    {
        return $content->sum('qty');
    }

    public function rawTotal(): float
    {
        $content = $this->getContent();

        $total = $content->reduce(function ($total, ?CartItem $cartItem) {
            if (! $cartItem) {
                return 0;
            }

            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            if (! EcommerceHelper::isTaxEnabled()) {
                return $total + $cartItem->qty * $cartItem->price + $optionPriceOnce;
            }

            $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);

            if ($priceIncludesTax) {
                return $total + $cartItem->qty * $cartItem->price + $optionPriceOnce;
            }

            return $total + ($cartItem->qty * ($cartItem->priceTax == 0 ? $cartItem->price : $cartItem->priceTax) + $optionPriceOnce);
        }, 0);

        return apply_filters('ecommerce_cart_raw_total', $total, $content);
    }

    public function rawTotalByItems($content): float
    {
        $total = $content->reduce(function ($total, ?CartItem $cartItem) {
            if (! $cartItem) {
                return 0;
            }

            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            if (! EcommerceHelper::isTaxEnabled()) {
                return $total + $cartItem->qty * $cartItem->price + $optionPriceOnce;
            }

            $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);

            if ($priceIncludesTax) {
                return $total + $cartItem->qty * $cartItem->price + $optionPriceOnce;
            }

            return $total + ($cartItem->qty * ($cartItem->priceTax == 0 ? $cartItem->price : $cartItem->priceTax) + $optionPriceOnce);
        }, 0);

        return (float) apply_filters('ecommerce_cart_raw_total_by_items', $total, $content);
    }

    public function rawTaxByItems($content, float $discountAmount = 0): float
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return 0;
        }

        $rawTotal = $this->rawTotalByItems($content);
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $discountAmount) / $rawTotal : 0;

        $totalTax = 0;
        foreach ($content as $cartItem) {
            $taxRate = $cartItem->taxRate;
            if ($taxRate > 0) {
                $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);
                $optionPriceOnce = $cartItem->options->get('option_price_once', 0);
                $itemPrice = $cartItem->qty * $cartItem->price + $optionPriceOnce;
                $effectiveItemPrice = $itemPrice * $discountRatio;

                if ($priceIncludesTax) {
                    $totalTax += EcommerceHelper::roundPrice($effectiveItemPrice - ($effectiveItemPrice / (1 + $taxRate / 100)));
                } else {
                    $totalTax += EcommerceHelper::roundPrice($effectiveItemPrice * ($taxRate / 100));
                }
            }
        }

        return (float) apply_filters('ecommerce_cart_raw_tax_by_items', $totalTax, $content, $discountAmount);
    }

    public function rawSubTotal(): float
    {
        $content = $this->getContent();

        $subTotal = $content->reduce(function ($subTotal, CartItem $cartItem) {
            $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);
            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            if (EcommerceHelper::isTaxEnabled() && $priceIncludesTax && $cartItem->taxRate > 0) {
                $basePrice = $cartItem->price / (1 + $cartItem->taxRate / 100);

                return $subTotal + EcommerceHelper::roundPrice($cartItem->qty * $basePrice + $optionPriceOnce);
            }

            return $subTotal + EcommerceHelper::roundPrice($cartItem->qty * $cartItem->price + $optionPriceOnce);
        }, 0);

        return apply_filters('ecommerce_cart_raw_subtotal', $subTotal, $content);
    }

    public function rawSubTotalByItems($content): float
    {
        $subTotal = $content->reduce(function ($subTotal, CartItem $cartItem) {
            $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);
            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            if (EcommerceHelper::isTaxEnabled() && $priceIncludesTax && $cartItem->taxRate > 0) {
                $basePrice = $cartItem->price / (1 + $cartItem->taxRate / 100);

                return $subTotal + EcommerceHelper::roundPrice($cartItem->qty * $basePrice + $optionPriceOnce);
            }

            return $subTotal + EcommerceHelper::roundPrice($cartItem->qty * $cartItem->price + $optionPriceOnce);
        }, 0);

        return (float) apply_filters('ecommerce_cart_raw_subtotal_by_items', $subTotal, $content);
    }

    public function rawQuantityByItemId($id): int
    {
        return $this->getContent()->reduce(function ($qty, CartItem $cartItem) use ($id) {
            return $cartItem->id == $id ? $qty + $cartItem->qty : $qty;
        }, 0);
    }

    public function rawTotalQuantity(): int
    {
        $content = $this->getContent();

        return $content->reduce(function ($qty, CartItem $cartItem) {
            return $qty + $cartItem->qty;
        }, 0);
    }

    public function search(Closure $search): Collection
    {
        $content = $this->getContent();

        return $content->filter($search);
    }

    public function associate(string $rowId, BaseModel|string $model): void
    {
        if (is_string($model) && ! class_exists($model)) {
            throw new UnknownModelException('The supplied model ' . $model . ' does not exist.');
        }

        $cartItem = $this->get($rowId);

        $cartItem->associate($model);

        $content = $this->getContent();

        $content->put($cartItem->rowId, $cartItem);

        $this->putToSession($content);
    }

    public function setTax(string $rowId, float $taxRate): void
    {
        $cartItem = $this->get($rowId);

        $cartItem->setTaxRate($taxRate);

        $cartItem->updated_at = Carbon::now();

        $content = $this->getContent();

        $content->put($cartItem->rowId, $cartItem);

        $this->putToSession($content);
    }

    public function store(string $identifier): void
    {
        if ($this->storedCartWithIdentifierExists($identifier)) {
            throw new CartAlreadyStoredException(sprintf('A cart with identifier %s was already stored.', $identifier));
        }

        $this->getConnection()->table($this->getTableName())->insert([
            'identifier' => $identifier,
            'instance' => $this->currentInstance(),
            'content' => serialize($this->getContent()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        static::dispatchEvent('cart.stored');
    }

    public function storeOrIgnore(string $identifier): void
    {
        if ($this->storedCartWithIdentifierExists($identifier)) {
            return;
        }

        $this->store($identifier);
    }

    public function storeQuietly($identifier)
    {
        return static::withoutEvents(fn () => $this->store($identifier));
    }

    public function updateOrStore(string $identifier): void
    {
        $table = $this->getConnection()->table($this->getTableName());
        $exists = $table
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->exists();

        $now = Carbon::now();

        if ($exists) {
            $table
                ->where('identifier', $identifier)
                ->where('instance', $this->currentInstance())
                ->update([
                    'content' => serialize($this->getContent()),
                    'updated_at' => $now,
                ]);
        } else {
            $table->insert([
                'identifier' => $identifier,
                'instance' => $this->currentInstance(),
                'content' => serialize($this->getContent()),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Sync session timestamp with the DB write so the
        // RestoreCustomerCartMiddleware does not treat the DB as
        // "newer" and overwrite the session with stale content.
        $this->session->put($this->instance . '_updated_at', $now->toIso8601String());

        static::dispatchEvent('cart.stored');
    }

    public function updateOrStoreQuietly(string $identifier): void
    {
        static::withoutEvents(fn () => $this->updateOrStore($identifier));
    }

    public function storedCartWithIdentifierExists(string $identifier): bool
    {
        return $this->getConnection()->table($this->getTableName())
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->exists();
    }

    protected function getConnection(): Connection
    {
        $connectionName = $this->getConnectionName();

        return app(DatabaseManager::class)->connection($connectionName);
    }

    protected function getConnectionName(): string
    {
        $connection = config('plugins.ecommerce.cart.database.connection');

        return empty($connection) ? config('database.default') : $connection;
    }

    protected function getTableName(): string
    {
        return config('plugins.ecommerce.cart.database.table', 'ec_cart');
    }

    public function currentInstance(): string
    {
        return str_replace('cart.', '', $this->instance);
    }

    public function restore(string $identifier): void
    {
        if (! $this->storedCartWithIdentifierExists($identifier)) {
            $this->putToSession(new Collection());
            $this->products = null;
            unset($this->counts[$this->instance]);

            return;
        }

        $stored = $this
            ->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)->first();

        if ($stored) {
            $storedContent = unserialize($stored->content);

            $currentInstance = $this->currentInstance();

            $this->instance($stored->instance);

            $content = $this->getContent();

            foreach ($storedContent as $cartItem) {
                $content->put($cartItem->rowId, $cartItem);
            }

            static::dispatchEvent('cart.restored');

            $this->putToSession($content);

            $this->products = null;
            unset($this->counts[$this->instance]);

            $this->instance($currentInstance);
        }

        $this
            ->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)
            ->delete();
    }

    public function restoreQuietly($identifier)
    {
        return static::withoutEvents(fn () => $this->restore($identifier));
    }

    public function storeForCustomer(int|string $customerId): void
    {
        $table = $this->getConnection()->table($this->getTableName());
        $exists = $table
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->exists();

        $now = Carbon::now();

        if ($exists) {
            $table
                ->where('customer_id', $customerId)
                ->where('instance', $this->currentInstance())
                ->update([
                    'content' => serialize($this->getContent()),
                    'updated_at' => $now,
                ]);
        } else {
            $table->insert([
                'identifier' => (string) $customerId,
                'instance' => $this->currentInstance(),
                'customer_id' => $customerId,
                'content' => serialize($this->getContent()),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Sync session timestamp with the DB write so the
        // RestoreCustomerCartMiddleware does not treat the DB as
        // "newer" and overwrite the session with stale content.
        $this->session->put($this->instance . '_updated_at', $now->toIso8601String());

        static::dispatchEvent('cart.stored');
    }

    public function storeForCustomerQuietly(int|string $customerId): void
    {
        static::withoutEvents(fn () => $this->storeForCustomer($customerId));
    }

    public function restoreForCustomer(int|string $customerId): void
    {
        $stored = $this
            ->getConnection()
            ->table($this->getTableName())
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->first();

        if (! $stored) {
            $this->putToSession(new Collection());
            $this->products = null;
            unset($this->counts[$this->instance]);

            return;
        }

        $storedContent = unserialize($stored->content);

        $content = new Collection();

        foreach ($storedContent as $cartItem) {
            $content->put($cartItem->rowId, $cartItem);
        }

        static::dispatchEvent('cart.restored');

        $this->putToSession($content);

        $this->products = null;
        unset($this->counts[$this->instance]);
    }

    public function restoreForCustomerQuietly(int|string $customerId): void
    {
        static::withoutEvents(fn () => $this->restoreForCustomer($customerId));
    }

    public function mergeGuestCart(string $guestIdentifier, int|string $customerId): void
    {
        $this->restore($guestIdentifier);

        $this->storeForCustomer($customerId);
    }

    public function mergeGuestCartQuietly(string $guestIdentifier, int|string $customerId): void
    {
        static::withoutEvents(fn () => $this->mergeGuestCart($guestIdentifier, $customerId));
    }

    public function linkGuestCartToCustomer(string $identifier, int|string $customerId): bool
    {
        return (bool) $this->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->whereNull('customer_id')
            ->update([
                'customer_id' => $customerId,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function customerCartExists(int|string $customerId): bool
    {
        return $this->getConnection()
            ->table($this->getTableName())
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->exists();
    }

    public function getCustomerCart(int|string $customerId): ?object
    {
        return $this->getConnection()
            ->table($this->getTableName())
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->first();
    }

    public function getCustomerCartUpdatedAt(int|string $customerId): ?CarbonInterface
    {
        $updatedAt = $this->getConnection()
            ->table($this->getTableName())
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->value('updated_at');

        return $updatedAt ? Carbon::parse($updatedAt) : null;
    }

    public function deleteCustomerCart(int|string $customerId): bool
    {
        return (bool) $this->getConnection()
            ->table($this->getTableName())
            ->where('customer_id', $customerId)
            ->where('instance', $this->currentInstance())
            ->delete();
    }

    public function getGuestCartUpdatedAt(string $identifier): ?CarbonInterface
    {
        $updatedAt = $this->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->whereNull('customer_id')
            ->value('updated_at');

        return $updatedAt ? Carbon::parse($updatedAt) : null;
    }

    public function restoreGuestCart(string $identifier): void
    {
        $stored = $this
            ->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->whereNull('customer_id')
            ->first();

        if (! $stored) {
            return;
        }

        $storedContent = unserialize($stored->content);

        $content = new Collection();

        foreach ($storedContent as $cartItem) {
            $content->put($cartItem->rowId, $cartItem);
        }

        static::dispatchEvent('cart.restored');

        $this->putToSession($content);

        unset($this->counts[$this->instance]);
    }

    public function restoreGuestCartQuietly(string $identifier): void
    {
        static::withoutEvents(fn () => $this->restoreGuestCart($identifier));
    }

    public function deleteGuestCart(string $identifier): bool
    {
        return (bool) $this->getConnection()
            ->table($this->getTableName())
            ->where('identifier', $identifier)
            ->where('instance', $this->currentInstance())
            ->whereNull('customer_id')
            ->delete();
    }

    public function __get($attribute)
    {
        if ($attribute === 'total') {
            return $this->total();
        }

        if ($attribute === 'tax') {
            return $this->tax();
        }

        if ($attribute === 'subtotal') {
            return $this->subtotal();
        }

        return null;
    }

    public function total(): string
    {
        $content = $this->getContent();

        $total = $content->reduce(function ($total, ?CartItem $cartItem) {
            if (! $cartItem) {
                return 0;
            }

            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            return $total + ($cartItem->qty * ($cartItem->priceTax == 0 ? $cartItem->price : $cartItem->priceTax) + $optionPriceOnce);
        }, 0);

        $total = apply_filters('ecommerce_cart_total', $total, $content);

        return format_price($total);
    }

    public function tax(): float|string
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return 0;
        }

        return format_price($this->rawTax());
    }

    public function rawTax(float $discountAmount = 0): float
    {
        if (! EcommerceHelper::isTaxEnabled()) {
            return 0;
        }

        $content = $this->getContent();
        $rawTotal = $this->rawTotal();
        $discountRatio = $rawTotal > 0 ? max(0, $rawTotal - $discountAmount) / $rawTotal : 0;

        $totalTax = 0;
        foreach ($content as $cartItem) {
            $taxRate = $cartItem->taxRate;
            if ($taxRate > 0) {
                $priceIncludesTax = $cartItem->options->get('price_includes_tax', false);
                $optionPriceOnce = $cartItem->options->get('option_price_once', 0);
                $itemPrice = $cartItem->qty * $cartItem->price + $optionPriceOnce;
                $effectiveItemPrice = $itemPrice * $discountRatio;

                if ($priceIncludesTax) {
                    $totalTax += EcommerceHelper::roundPrice($effectiveItemPrice - ($effectiveItemPrice / (1 + $taxRate / 100)));
                } else {
                    $totalTax += EcommerceHelper::roundPrice($effectiveItemPrice * ($taxRate / 100));
                }
            }
        }

        return (float) apply_filters('ecommerce_cart_raw_tax', $totalTax, $content, $discountAmount);
    }

    public function subtotal(): string
    {
        $content = $this->getContent();

        $subTotal = $content->reduce(function ($subTotal, CartItem $cartItem) {
            $optionPriceOnce = $cartItem->options->get('option_price_once', 0);

            return $subTotal + ($cartItem->qty * $cartItem->price + $optionPriceOnce);
        }, 0);

        $subTotal = apply_filters('ecommerce_cart_subtotal', $subTotal, $content);

        return format_price($subTotal);
    }

    public function products(): Collection|EloquentCollection
    {
        if ($this->products) {
            return $this->products;
        }

        $cartContent = $this->instance('cart')->content();
        $productIds = array_unique($cartContent->pluck('id')->toArray());
        $products = collect();
        $weight = 0;
        if ($productIds) {
            $with = [
                'variationInfo',
                'variationInfo.configurableProduct',
                'variationInfo.configurableProduct.slugable',
                'variationProductAttributes',
            ];

            if (is_plugin_active('marketplace')) {
                $with = array_merge($with, [
                    'variationInfo.configurableProduct.store',
                    'variationInfo.configurableProduct.store.slugable',
                ]);
            }

            $products = app(ProductInterface::class)->getProducts([
                'condition' => [
                    ['ec_products.id', 'IN', $productIds],
                ],
                'with' => $with,
            ]);
        }

        $productsInCart = new EloquentCollection();

        if ($products->isNotEmpty()) {
            foreach ($cartContent as $cartItem) {
                $product = $products->firstWhere('id', $cartItem->id);
                if (! $product || $product->original_product->status != BaseStatusEnum::PUBLISHED) {
                    $this->remove($cartItem->rowId);
                } else {
                    $productInCart = clone $product;
                    $productInCart->cartItem = $cartItem;
                    // Use rowId as unique_id to allow same product with different options/extras
                    $productInCart->unique_id = $cartItem->rowId;

                    $productsInCart->push($productInCart);
                    $weight += $product->weight * $cartItem->qty;
                }
            }
        }

        $weight = EcommerceHelper::validateOrderWeight($weight);

        $this->products = $productsInCart->unique('unique_id');
        $this->weight = $weight;

        if ($this->products->isEmpty()) {
            $this->instance('cart')->destroy();
        }

        return $this->products;
    }

    public function content(): Collection
    {
        if (empty($this->session->get($this->instance))) {
            return collect();
        }

        $content = $this->getContent();

        return apply_filters('ecommerce_cart_content', $content, $this->instance);
    }

    public function rawContent(): Collection
    {
        return $this->getContent();
    }

    public function weight(): float
    {
        return EcommerceHelper::validateOrderWeight($this->weight);
    }

    public static function getEventDispatcher(): Dispatcher
    {
        return static::$dispatcher;
    }

    public static function setEventDispatcher(Dispatcher $dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }

    public static function withoutEvents(callable $callback)
    {
        $dispatcher = static::getEventDispatcher();

        static::setEventDispatcher(new NullDispatcher($dispatcher));

        try {
            return $callback();
        } finally {
            static::setEventDispatcher($dispatcher);
        }
    }

    protected static function dispatchEvent(string $event, $parameters = []): void
    {
        if (isset(static::$dispatcher)) {
            static::$dispatcher->dispatch($event, $parameters);
        }
    }

    public function refresh(): void
    {
        $cart = $this->instance('cart');

        if ($cart->isEmpty()) {
            return;
        }

        $ids = $cart->content()->pluck('id')->toArray();

        $products = get_products([
            'condition' => [
                ['ec_products.id', 'IN', $ids],
            ],
        ]);

        if ($products->isEmpty()) {
            return;
        }

        // Disable auto-loading of up-sale context to prevent up-sale pricing from being
        // incorrectly applied to products without cartItem attached. Up-sale pricing
        // depends on individual cart item's upsale_reference_product.
        $upSalePriceService = app(ProductUpSalePriceService::class);
        $upSalePriceService->disableAutoLoad();

        try {
            foreach ($cart->content() as $rowId => $cartItem) {
                $product = $products->firstWhere('id', $cartItem->id);
                if (! $product || $product->original_product->status != BaseStatusEnum::PUBLISHED) {
                    $this->remove($cartItem->rowId);
                } elseif (apply_filters('ecommerce_skip_cart_item_price_update', false, $cartItem)) {
                    continue;
                } else {
                    $cart->removeQuietly($rowId);

                    $parentProduct = $product->original_product;

                    $options = $cartItem->options->toArray();
                    $options['image'] = $product->image ?: $parentProduct->image;

                    // Preserve the original tax option so the rowId stays stable across
                    // refresh. The CartItem taxRate property defaults to 0 and is only set
                    // later by setTax() at checkout; overwriting the option with it flipped
                    // the rowId, so a second Buy Now of the same product no longer merged
                    // and produced a duplicate cart line. The effective tax rate lives on
                    // the property (recalculated by HandleTaxService), not in this option.
                    $options['taxRate'] = $cartItem->options->taxRate ?? $cartItem->getTaxRate();

                    $cart->addQuietly(
                        $cartItem->id,
                        $cartItem->name,
                        $cartItem->qty,
                        $product->price()->getPrice(false),
                        $options
                    );
                }
            }
        } finally {
            $upSalePriceService->enableAutoLoad();
        }

        try {
            app(HandleApplyProductCrossSaleService::class)->handle();
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }

        // Apply up-sale pricing to cart items with bundle references
        try {
            app(HandleApplyProductUpSaleService::class)->handle();
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }

    public function taxClassesName(): string
    {
        $taxes = [];

        foreach ($this->content() as $cartItem) {
            if (! $cartItem->taxRate || ! $cartItem->options->taxClasses) {
                continue;
            }

            foreach ($cartItem->options->taxClasses as $taxName => $taxRate) {
                $taxes[] = $taxName . ' - ' . (count($cartItem->options->taxClasses) > 1 ? $taxRate : $cartItem->taxRate) . '%';
            }
        }

        if (empty($taxes) && $defaultTaxRate = get_ecommerce_setting('default_tax_rate')) {
            $tax = Tax::query()->where('id', $defaultTaxRate)->first();

            if ($tax) {
                $taxes[] = $tax->title . ' - ' . ($tax->percentage) . '%';
            }
        }

        return implode(', ', array_unique($taxes));
    }
}
