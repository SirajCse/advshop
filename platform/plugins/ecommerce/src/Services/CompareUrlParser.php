<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Models\Product;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CompareUrlParser
{
    /**
     * Maximum products a single share URL may resolve to. Mirrors
     * CompareController::MAX_PRODUCTS — duplicated as a constant so the parser
     * stays self-contained.
     */
    public const MAX_PRODUCTS = 4;

    /**
     * Parse a "{slug1}-vs-{slug2}-vs-{slug3}" share string into resolved Product models.
     *
     * Strategy: greedy reverse-resolver. Tries the whole string as a single slug
     * first (so a slug like `redmi-vs-pro` resolves cleanly); otherwise splits
     * off candidates from the right one `-vs-` at a time, validating each pair.
     * Falls back to skipping unresolvable slugs to keep URLs durable when a
     * product is unpublished.
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    public function parseShareSlug(string $slugs): Collection
    {
        $slugs = trim($slugs, " \t\n\r\0\x0B/");

        if ($slugs === '') {
            return collect();
        }

        $resolved = $this->resolveGreedy($slugs, 0);

        if ($resolved->isNotEmpty()) {
            return $resolved;
        }

        // Last-ditch: split naively and keep whatever resolves. Useful for URLs
        // where a product was renamed but a partial slug still maps cleanly.
        $products = collect();

        foreach (preg_split('/-vs-/i', $slugs) as $candidate) {
            $product = $this->findProductBySlug($candidate);

            if ($product) {
                $products->push($product);
            }

            if ($products->count() >= self::MAX_PRODUCTS) {
                break;
            }
        }

        return $products;
    }

    /**
     * Greedy reverse-resolver for "{slug1}-vs-{slug2}-vs-…" share strings.
     *
     * Tries the whole remaining string as a single slug first; otherwise splits
     * off candidates from the rightmost `-vs-` boundary backwards, recursing on
     * the left half. Bounded by MAX_PRODUCTS so pathological inputs cannot
     * cause unbounded recursion.
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    protected function resolveGreedy(string $remaining, int $depth): Collection
    {
        if ($remaining === '' || $depth >= self::MAX_PRODUCTS) {
            return collect();
        }

        if ($product = $this->findProductBySlug($remaining)) {
            return collect([$product]);
        }

        $parts = preg_split('/-vs-/i', $remaining);
        $count = count($parts);

        for ($i = $count - 1; $i > 0; $i--) {
            $left = implode('-vs-', array_slice($parts, 0, $i));
            $right = implode('-vs-', array_slice($parts, $i));

            $rightProduct = $this->findProductBySlug($right);

            if (! $rightProduct) {
                continue;
            }

            $leftProducts = $this->resolveGreedy($left, $depth + 1);

            if ($leftProducts->isNotEmpty()) {
                return $leftProducts->push($rightProduct);
            }
        }

        return collect();
    }

    /**
     * Build a canonical "{slug1}-vs-{slug2}" share string from a list of products.
     *
     * @param iterable<Product> $products
     */
    public function buildShareSlug(iterable $products): string
    {
        $slugs = [];

        foreach ($products as $product) {
            $slug = $this->getProductSlugKey($product);

            if ($slug !== null) {
                $slugs[] = $slug;
            }
        }

        return implode('-vs-', $slugs);
    }

    /**
     * Resolve a product from a full URL pasted by the user. Only same-host URLs are accepted.
     */
    public function extractProductFromUrl(string $url): ?Product
    {
        $url = trim($url);

        if ($url === '' || strlen($url) > 2048) {
            return null;
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! is_array($parsed)) {
            return null;
        }

        // Require an explicit host that matches the configured app host.
        // Reject path-only inputs ("/products/foo") and any third-party URL.
        if (! isset($parsed['host']) || ! $this->isSameHost($parsed['host'])) {
            return null;
        }

        $path = $parsed['path'] ?? $url;
        $path = trim($path, '/');

        if ($path === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));
        $lastSegment = end($segments);

        if ($lastSegment === false || $lastSegment === '') {
            return null;
        }

        // Strip URL-ending suffix (e.g., ".html") if configured.
        $extension = SlugHelper::getPublicSingleEndingURL();

        if (! empty($extension)) {
            $lastSegment = Str::replaceLast($extension, '', $lastSegment);
        }

        return $this->findProductBySlug($lastSegment);
    }

    protected function findProductBySlug(string $key): ?Product
    {
        $key = trim($key);

        if ($key === '') {
            return null;
        }

        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Product::class));

        if (! $slug) {
            return null;
        }

        return Product::query()
            ->wherePublished()
            ->where('id', $slug->reference_id)
            ->first();
    }

    protected function getProductSlugKey(Product $product): ?string
    {
        $slug = SlugHelper::getSlug(null, SlugHelper::getPrefix(Product::class), Product::class, $product->getKey());

        return $slug?->key;
    }

    protected function isSameHost(string $host): bool
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! $appHost) {
            return true; // Permissive fallback when app.url is not set.
        }

        return strcasecmp($host, $appHost) === 0;
    }
}
