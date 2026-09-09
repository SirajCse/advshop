<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Services\Products\GetProductBySlugService;
use Botble\Ecommerce\Services\Products\GetProductWithCrossSalesBySlugService;
use Botble\Ecommerce\Services\Products\GetProductWithUpSalesBySlugService;
use Botble\Ecommerce\Services\Products\ProductCrossSalePriceService;
use Botble\Ecommerce\Services\Products\ProductUpSalePriceService;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;

class QuickShopController extends BaseController
{
    public function show(
        string $slug,
        GetProductBySlugService $getProductBySlugService,
        GetProductWithCrossSalesBySlugService $getProductWithCrossSalesBySlugService,
        GetProductWithUpSalesBySlugService $getProductWithUpSalesBySlugService,
        Request $request,
    ) {
        $request->validate([
            'reference_product' => ['sometimes', 'required', 'string'],
            'sale_type' => ['sometimes', 'in:cross,up'],
        ]);

        $product = $getProductBySlugService->handle($slug, [
            'with' => [
                'slugable',
                'tags',
                'tags.slugable',
                'options',
                'options.values',
            ],
        ]);

        abort_unless($product && $product->exists, 404);

        $referenceProduct = null;
        $saleType = $request->input('sale_type', 'cross');

        if ($request->filled('reference_product')) {
            if ($saleType === 'up') {
                $referenceProduct = $getProductWithUpSalesBySlugService->handle($request->input('reference_product'));

                if ($referenceProduct) {
                    app(ProductUpSalePriceService::class)->applyProduct($referenceProduct);
                }
            } else {
                $referenceProduct = $getProductWithCrossSalesBySlugService->handle($request->input('reference_product'));

                if ($referenceProduct) {
                    app(ProductCrossSalePriceService::class)->applyProduct($referenceProduct);
                }
            }
        }

        [$productImages, $productVariation, $selectedAttrs] = EcommerceHelper::getProductVariationInfo($product);

        $data = apply_filters('ecommerce_quick_shop_data', [
            'product' => $product,
            'productImages' => $productImages,
            'productVariation' => $productVariation,
            'selectedAttrs' => $selectedAttrs,
            'referenceProduct' => $referenceProduct,
            'saleType' => $saleType,
        ]);

        $view = apply_filters('ecommerce_quick_shop_template', $this->getQuickShopTemplate());

        return $this
            ->httpResponse()
            ->setData(view($view, $data)->render());
    }

    protected function getQuickShopTemplate(): string
    {
        if (view()->exists($view = Theme::getThemeNamespace('views.ecommerce.quick-shop'))) {
            return $view;
        }

        if (view()->exists($view = Theme::getThemeNamespace('partials.ecommerce.quick-shop'))) {
            return $view;
        }

        if (view()->exists($view = Theme::getThemeNamespace('partials.quick-shop'))) {
            return $view;
        }

        return EcommerceHelper::viewPath('includes.quick-shop');
    }
}
