<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\AdsTracking\GoogleTagManager;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\SpecificationGroup;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Services\CompareUrlParser;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CompareController extends BaseController
{
    /**
     * Maximum number of products that may appear on the compare page at once.
     */
    public const MAX_PRODUCTS = 4;

    public function __construct(
        protected ProductInterface $productRepository,
        protected CompareUrlParser $urlParser
    ) {
    }

    public function index()
    {
        return $this->renderComparePage();
    }

    /**
     * Slug-based shareable view: /compare/{slug1}-vs-{slug2}-vs-...
     *
     * Renders the shared comparison transiently (does NOT mutate the visitor's
     * session compare list — clicking a friend's link should not silently nuke
     * the four products the user just curated). The visitor can opt-in to
     * "save this comparison" via the toolbar action exposed in the view.
     */
    public function indexBySlugs(string $slugs)
    {
        $products = $this->urlParser->parseShareSlug($slugs)->take(self::MAX_PRODUCTS);

        abort_if($products->isEmpty(), 404);

        return $this->renderComparePage($products->values());
    }

    public function store(int|string $productId)
    {
        $product = Product::query()->findOrFail($productId);

        $result = $this->addOrFail($product);

        if ($result['error']) {
            return $this->httpResponse()->setMessage($result['message'])->setError();
        }

        $product = $result['product'];

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::products.compare.added_success', ['product' => $product->name]))
            ->setData([
                'count' => Cart::instance('compare')->count(),
                'redirect_url' => $this->buildShareUrl(),
                'extra_data' => app(GoogleTagManager::class)->formatProductTrackingData($product->original_product),
            ]);
    }

    /**
     * Add a product to the compare list by pasting its public URL.
     * Only same-host URLs are accepted.
     */
    public function addByUrl(Request $request)
    {
        $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $product = $this->urlParser->extractProductFromUrl((string) $request->input('url'));

        if (! $product) {
            return $this
                ->httpResponse()
                ->setMessage(trans('plugins/ecommerce::products.compare.url_not_recognized'))
                ->setError();
        }

        $result = $this->addOrFail($product);

        if ($result['error']) {
            return $this->httpResponse()->setMessage($result['message'])->setError();
        }

        $product = $result['product'];

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::products.compare.added_success', ['product' => $product->name]))
            ->setData([
                'count' => Cart::instance('compare')->count(),
                'redirect_url' => $this->buildShareUrl(),
            ]);
    }

    /**
     * AJAX search for the product-picker modal. Returns paginated published products
     * with the data the modal grid needs (id, name, image, formatted prices).
     *
     * Rate-limited at the route level (throttle middleware) — this endpoint is
     * unauthenticated and would otherwise be a free catalog scrape vector.
     */
    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $perPage = 10;

        $builder = Product::query()
            ->wherePublished()
            ->where('is_variation', false)
            ->with(['slugable']);

        if ($query !== '') {
            $builder->where('name', 'like', "%$query%");
        }

        $excluded = collect(Cart::instance('compare')->content())->pluck('id')->all();

        if (! empty($excluded)) {
            $builder->whereNotIn('id', $excluded);
        }

        $products = $builder
            ->orderByDesc('id')
            ->paginate($perPage);

        $items = $products->getCollection()->map(function (Product $product): array {
            $hasDiscount = $product->front_sale_price < $product->price
                && (int) round((($product->price - $product->front_sale_price) / max($product->price, 0.01)) * 100) >= 1;

            return [
                'id' => $product->getKey(),
                'name' => $product->name,
                'image' => RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()),
                'url' => $product->url,
                'price' => format_price($product->front_sale_price),
                'original_price' => $hasDiscount ? format_price($product->price) : null,
                'add_url' => route('public.compare.add', $product->getKey()),
            ];
        });

        return $this
            ->httpResponse()
            ->setData([
                'items' => $items->all(),
                'has_more' => $products->hasMorePages(),
                'page' => $products->currentPage(),
                'total' => $products->total(),
            ]);
    }

    public function destroy(int|string $productId)
    {
        $product = Product::query()->findOrFail($productId);

        $cart = Cart::instance('compare');
        $rowId = $cart->search(fn ($cartItem) => (string) $cartItem->id === (string) $productId)->keys()->first();

        if ($rowId !== null) {
            $cart->remove($rowId);
        }

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::products.compare.removed_success', ['product' => $product->name]))
            ->setData([
                'count' => $cart->count(),
                'redirect_url' => $this->buildShareUrl(),
                'extra_data' => app(GoogleTagManager::class)->formatProductTrackingData($product->original_product),
            ]);
    }

    /**
     * Render the compare page from either an explicit product collection (used
     * by indexBySlugs to show shared comparisons transiently) or — when no
     * collection is passed — the current session compare list.
     */
    protected function renderComparePage(?Collection $sharedProducts = null)
    {
        $title = __('Compare');

        Theme::breadcrumb()
            ->add($title, route('public.compare'));

        $isShared = $sharedProducts !== null;

        $products = collect();
        $attributeSets = collect();
        $specGroups = collect();

        if ($isShared) {
            $products = $this->loadProductsForCompare($sharedProducts->pluck('id')->all());
        } else {
            $itemIds = collect(Cart::instance('compare')->content())
                ->sortBy([['updated_at', 'desc']])
                ->pluck('id');

            if ($itemIds->isNotEmpty()) {
                $products = $this->loadProductsForCompare($itemIds->all());
            }
        }

        if ($products->isNotEmpty()) {
            $products->load('productAttributeSets.attributes');

            $attributeSets = $products
                ->flatMap(fn (Product $product) => $product->productAttributeSets)
                ->unique('id')
                ->filter(fn ($set) => (bool) $set->is_comparable)
                ->values();

            $specGroups = $this->collectSpecGroups($products);
        }

        // Dynamic <title>: when ≥2 products are being compared, surface the
        // matchup ("Compare: A vs B vs C") so shared-link previews and search
        // results read meaningfully. Falls back to the admin-configured SEO
        // title (or the static "Compare" label) when the page is empty or
        // only one item is present.
        if ($products->count() >= 2) {
            $separator = ' ' . trans('plugins/ecommerce::products.compare.vs_separator') . ' ';
            $seoTitle = $title . ': ' . $products->pluck('name')->implode($separator);
        } else {
            $seoTitle = theme_option('ecommerce_compare_seo_title') ?: $title;
        }

        SeoHelper::setTitle($seoTitle)
            ->setDescription(theme_option('ecommerce_compare_seo_description'));

        $shareUrl = $this->buildShareUrl($products);
        $maxProducts = self::MAX_PRODUCTS;
        $emptySlots = $isShared ? 0 : max(0, $maxProducts - $products->count());

        return Theme::scope(
            'ecommerce.compare',
            compact('products', 'attributeSets', 'specGroups', 'shareUrl', 'maxProducts', 'emptySlots', 'isShared'),
            'plugins/ecommerce::themes.compare'
        )->render();
    }

    /**
     * Eager-load the relations the compare view needs, then preserve the input
     * order (the repository may reorder by FIELD() on MySQL, but other drivers
     * don't, so we re-sort defensively).
     *
     * Returns an Eloquent\Collection (not the base Collection) so the caller
     * can still ->load() additional relations on it.
     *
     * @param array<int, int|string> $ids
     */
    protected function loadProductsForCompare(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $products = $this->productRepository
            ->getProductsByIds($ids, [
                'take' => self::MAX_PRODUCTS,
                'with' => array_merge(EcommerceHelper::withProductEagerLoadingRelations(), [
                    'specificationTable.groups.specificationAttributes',
                    'specificationAttributes',
                ]),
            ]);

        // Defensive reorder against the requested id sequence; keep as Eloquent
        // Collection so ->load() and other relation helpers still work.
        $orderMap = array_flip(array_values($ids));

        return $products
            ->sortBy(fn (Product $p) => $orderMap[$p->getKey()] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Build the shared union of SpecificationGroup → SpecificationAttribute rows
     * across all products on the compare page. Groups appear once even if multiple
     * products share them; attribute order within each group preserves the order
     * defined on each product's spec table.
     */
    protected function collectSpecGroups(Collection $products): Collection
    {
        $groups = collect();
        $attributesByGroup = [];

        foreach ($products as $product) {
            $table = $product->specificationTable;

            if (! $table) {
                continue;
            }

            foreach ($table->getSortedAttributesForProduct($product) as $row) {
                /** @var SpecificationGroup $group */
                $group = $row['group'];
                $groupId = $group->getKey();

                if (! $groups->has($groupId)) {
                    $groups->put($groupId, $group);
                    $attributesByGroup[$groupId] = collect();
                }

                foreach ($row['attributes'] as $attribute) {
                    $attrId = $attribute->getKey();

                    if (! $attributesByGroup[$groupId]->has($attrId)) {
                        $attributesByGroup[$groupId]->put($attrId, $attribute);
                    }
                }
            }
        }

        return $groups->values()->map(function (SpecificationGroup $group) use ($attributesByGroup) {
            return [
                'group' => $group,
                'attributes' => $attributesByGroup[$group->getKey()]->values(),
            ];
        });
    }

    /**
     * Single guarded add path. Collapses the variant→parent remap, duplicate
     * check and cap check that store(), addByUrl() and (legacy) indexBySlugs()
     * all need. Returns ['error'=>bool, 'message'=>string|null, 'product'=>Product].
     *
     * Defense-in-depth: even if two requests pass the cap check concurrently
     * (session cart isn't transactional), the post-write trim below ensures the
     * list never exceeds MAX_PRODUCTS — the racing item gets rejected.
     *
     * @return array{error: bool, message: ?string, product: Product}
     */
    protected function addOrFail(Product $product): array
    {
        if ($product->is_variation) {
            $product = $product->original_product;
        }

        if ($this->isAlreadyInCompare($product->getKey())) {
            return [
                'error' => true,
                'message' => trans('plugins/ecommerce::products.compare.already_in_list', ['product' => $product->name]),
                'product' => $product,
            ];
        }

        if (Cart::instance('compare')->count() >= self::MAX_PRODUCTS) {
            return [
                'error' => true,
                'message' => trans('plugins/ecommerce::products.compare.list_full', ['max' => self::MAX_PRODUCTS]),
                'product' => $product,
            ];
        }

        $this->addProductToCompare($product);

        // Race guard: if a parallel request already pushed us past the cap,
        // remove the row we just added and bail out with a list_full error.
        if (Cart::instance('compare')->count() > self::MAX_PRODUCTS) {
            $rowId = Cart::instance('compare')
                ->search(fn ($cartItem) => (string) $cartItem->id === (string) $product->getKey())
                ->keys()
                ->first();

            if ($rowId !== null) {
                Cart::instance('compare')->remove($rowId);
            }

            return [
                'error' => true,
                'message' => trans('plugins/ecommerce::products.compare.list_full', ['max' => self::MAX_PRODUCTS]),
                'product' => $product,
            ];
        }

        return ['error' => false, 'message' => null, 'product' => $product];
    }

    /**
     * Add a Product to the session compare cart. Caller is responsible for cap
     * and duplicate checks (use addOrFail() unless you have already validated).
     */
    protected function addProductToCompare(Product $product): void
    {
        Cart::instance('compare')
            ->add($product->getKey(), $product->name, 1, $product->front_sale_price)
            ->associate(Product::class);
    }

    protected function isAlreadyInCompare(int|string $productId): bool
    {
        return Cart::instance('compare')
            ->search(fn ($cartItem) => (string) $cartItem->id === (string) $productId)
            ->isNotEmpty();
    }

    /**
     * Build the canonical /compare/{slug1}-vs-{slug2} URL from a product
     * collection (or the current session list when none passed). Returns the
     * bare /compare URL when fewer than 2 products are present.
     */
    protected function buildShareUrl(?Collection $products = null): string
    {
        if ($products !== null) {
            $ordered = $products;
        } else {
            $itemIds = collect(Cart::instance('compare')->content())->pluck('id')->all();

            if (count($itemIds) < 2) {
                return route('public.compare');
            }

            $loaded = Product::query()->whereIn('id', $itemIds)->get();
            $ordered = collect($itemIds)
                ->map(fn ($id) => $loaded->firstWhere('id', $id))
                ->filter();
        }

        if ($ordered->count() < 2) {
            return route('public.compare');
        }

        $slug = $this->urlParser->buildShareSlug($ordered);

        if ($slug === '') {
            return route('public.compare');
        }

        return route('public.compare.shared', ['slugs' => $slug]);
    }
}
