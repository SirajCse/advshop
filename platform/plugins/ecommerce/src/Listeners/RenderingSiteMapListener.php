<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Support\Arr;

class RenderingSiteMapListener
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'product-tags':
                    $tags = ProductTag::query()
                        ->with('slugable')
                        ->wherePublished()->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($tags as $tag) {
                        if (! $tag->slugable) {
                            continue;
                        }

                        SiteMapManager::add($tag->url, $tag->updated_at, '0.3', 'weekly');
                    }

                    break;
                case 'product-categories':
                    $productCategories = ProductCategory::query()
                        ->with('slugable')
                        ->wherePublished()->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($productCategories as $productCategory) {
                        if (! $productCategory->slugable) {
                            continue;
                        }

                        SiteMapManager::add($productCategory->url, $productCategory->updated_at, '0.6');
                    }

                    break;
                case 'product-brands':
                    $brands = Brand::query()
                        ->with('slugable')
                        ->wherePublished()->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($brands as $brand) {
                        if (! $brand->slugable) {
                            continue;
                        }

                        SiteMapManager::add($brand->url, $brand->updated_at, '0.6');
                    }

                    break;
                case 'pages':
                    SiteMapManager::add(route('public.products'), null, '1', 'monthly');
                    if (EcommerceHelper::isCartEnabled()) {
                        SiteMapManager::add(route('public.cart'), null, '1', 'monthly');
                    }

                    break;
            }

            // Consolidated products handler with optional page-based pagination.
            // Matches: products, products-page-{N}
            if ($key === 'products' || preg_match('/^products-page-(\d+)$/', $key, $pageMatches)) {
                $itemsPerPage = SiteMapManager::getItemsPerPage();
                $page = isset($pageMatches[1]) ? max(1, (int) $pageMatches[1]) : 1;
                $offset = ($page - 1) * $itemsPerPage;

                $products = Product::query()
                    ->with('slugable')
                    ->wherePublished()
                    ->where('is_variation', 0)
                    ->latest('updated_at')
                    ->select(['id', 'name', 'updated_at'])
                    ->skip($offset)
                    ->take($itemsPerPage)
                    ->get();

                foreach ($products as $product) {
                    if (! $product->slugable) {
                        continue;
                    }

                    SiteMapManager::add($product->url, $product->updated_at, '0.8');
                }

                return;
            }

            // Backward compatibility: legacy monthly archive URLs (products-YYYY-MM[-page-N]).
            $paginationData = SiteMapManager::extractPaginationDataByPattern($key, 'products', 'monthly-archive');

            if ($paginationData) {
                $matches = $paginationData['matches'];
                $year = Arr::get($matches, 1);
                $month = Arr::get($matches, 2);

                if ($year && $month) {
                    $products = Product::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->where('is_variation', 0)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->latest('updated_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->skip($paginationData['offset'])
                        ->take($paginationData['limit'])
                        ->get();

                    foreach ($products as $product) {
                        if (! $product->slugable) {
                            continue;
                        }

                        SiteMapManager::add($product->url, $product->updated_at, '0.8');
                    }
                }
            } elseif (preg_match('/^products-((?:19|20|21|22)\d{2})-(0?[1-9]|1[012])$/', $key, $matches)) {
                if (($year = Arr::get($matches, 1)) && ($month = Arr::get($matches, 2))) {
                    $products = Product::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->where('is_variation', 0)
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($products as $product) {
                        if (! $product->slugable) {
                            continue;
                        }

                        SiteMapManager::add($product->url, $product->updated_at, '0.8');
                    }
                }
            }
        } else {
            // Sitemap index registration.
            // Match pages.xml behavior: single consolidated products.xml by default,
            // auto-paginate (products-page-N.xml) only when total products exceed items_per_page threshold.
            $totalProducts = Product::query()
                ->wherePublished()
                ->where('is_variation', 0)
                ->count();

            if ($totalProducts > 0) {
                $latestUpdated = Product::query()
                    ->wherePublished()
                    ->where('is_variation', 0)
                    ->latest('updated_at')
                    ->value('updated_at');

                SiteMapManager::createPaginatedSitemaps('products', $totalProducts, $latestUpdated);
            }

            $productCategoryUpdated = ProductCategory::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->latest('updated_at')
                ->value('updated_at');

            if ($productCategoryUpdated) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-categories'), $productCategoryUpdated);
            }

            $brandUpdated = Brand::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->latest('updated_at')
                ->value('updated_at');

            if ($brandUpdated) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-brands'), $brandUpdated);
            }

            $productTagUpdated = ProductTag::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->latest('updated_at')
                ->value('updated_at');

            if ($productTagUpdated) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-tags'), $productTagUpdated);
            }
        }
    }
}
