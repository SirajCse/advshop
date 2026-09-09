@php
    use Botble\Ecommerce\Facades\FlashSale;

    Theme::set('breadcrumbStyle', 'without-title');
    Theme::layout('full-width');
    Theme::asset()->container('footer')->usePath()->add('waypoints', 'plugins/waypoints/jquery.waypoints.min.js');

    $flashSale = null;

    if (FlashSale::isEnabled()) {
        $flashSale = FlashSale::getFlashSaleForProduct($product);
    }

    Theme::set('pageTitle', $product->name);
@endphp

<section class="tp-product-details-area @if (! theme_option('theme_breadcrumb_enabled', true)) pt-50 no-breadcrumb @endif">
    {!! apply_filters('ads_render', null, 'detail_page_before') !!}

    <div class="tp-product-details-top bb-product-detail">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    {!! apply_filters('ecommerce_product_detail_before_gallery', null, $product) !!}
                    <div class="tp-product-details-thumb-wrapper me-0 me-md-3 tp-tab">
                        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.badges'))
                        @include(EcommerceHelper::viewPath('includes.product-gallery'), ['productImageSize' => 'medium'])
                        {!! apply_filters('ecommerce_product_detail_after_gallery', null, $product) !!}
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="tp-product-details-wrapper has-sticky">
                        @include(Theme::getThemeNamespace('views.ecommerce.includes.product-detail'))
                        @include(Theme::getThemeNamespace('views.ecommerce.includes.product-sharing'))

                        {!! dynamic_sidebar('product_details_sidebar') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (EcommerceHelper::isEnabledUpSaleProducts())
        @include(EcommerceHelper::viewPath('includes.up-sale-products'), ['parentProduct' => $product])
    @endif

    @if (EcommerceHelper::isEnabledCrossSaleProducts() && isset($product))
        @include(EcommerceHelper::viewPath('includes.cross-sale-products'), ['parentProduct' => $product])
    @endif

    {!! apply_filters('ecommerce_product_detail_before_tabs', null, $product) !!}

    <div class="tp-product-details-bottom">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="tp-product-details-tab-nav tp-tab">
                        <nav>
                            <div class="nav nav-tabs p-relative tp-product-tab" id="navPresentationTab" role="tablist">
                                <button class="nav-link active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button" role="tab" aria-controls="nav-description" aria-selected="true">
                                    {{ __('Description') }}
                                </button>
                                @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                                    <button class="nav-link" id="nav-specification-tab" data-bs-toggle="tab" data-bs-target="#nav-specification" type="button" role="tab" aria-controls="nav-specification" aria-selected="false">
                                        {{ __('Product Specification') }}
                                    </button>
                                @endif
                                @if(EcommerceHelper::isReviewEnabled())
                                    <button class="nav-link" id="nav-review-tab" data-bs-toggle="tab" data-bs-target="#nav-review" type="button" role="tab" aria-controls="nav-review" aria-selected="false">
                                        {{ __('Reviews (:count)', ['count' => $product->reviews_count]) }}
                                    </button>
                                @endif

                                @if (is_plugin_active('marketplace') && $product->store?->id)
                                    <button class="nav-link" id="nav-vendor-tab" data-bs-toggle="tab" data-bs-target="#nav-vendor" type="button" role="tab" aria-controls="nav-store" aria-selected="false">
                                        {{ __('Vendor') }}
                                    </button>
                                @endif
                                @if (is_plugin_active('faq') && $product->faq_items)
                                    <button class="nav-link" id="nav-faq-tab" data-bs-toggle="tab" data-bs-target="#nav-faq" type="button" role="tab" aria-controls="nav-faq" aria-selected="false">
                                        {{ __('FAQs') }}
                                    </button>
                                @endif
                                {!! apply_filters('ecommerce_product_detail_extra_tab_headers', null, $product) !!}
                                <span id="productTabMarker" class="tp-product-details-tab-line"></span>
                            </div>
                        </nav>
                        <div class="tab-content" id="navPresentationTabContent">
                            <div class="tab-pane fade show active" id="nav-description" role="tabpanel" aria-labelledby="nav-description-tab" tabindex="0">
                                <div class="tp-product-details-desc-wrapper">
                                    <div class="ck-content">
                                        {!! BaseHelper::clean($product->content) !!}
                                    </div>

                                    {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $product) !!}
                                </div>
                            </div>
                            @if (EcommerceHelper::isProductSpecificationEnabled() && $product->specificationAttributes->where('pivot.hidden', false)->isNotEmpty())
                                <div class="tab-pane fade" id="nav-specification" role="tabpanel" aria-labelledby="nav-specification-tab" tabindex="0">
                                    <div class="tp-product-details-additional-info">
                                        @include(EcommerceHelper::viewPath('includes.product-specification'))
                                    </div>
                                </div>
                            @endif
                            @if (EcommerceHelper::isReviewEnabled())
                                <div class="tab-pane fade" id="nav-review" role="tabpanel" aria-labelledby="nav-review-tab" tabindex="0">
                                    <div class="tp-product-details-review-wrapper pt-60" id="product-review">
                                        @include(EcommerceHelper::viewPath('includes.reviews'))
                                    </div>
                                </div>
                            @endif
                            @if (is_plugin_active('marketplace') && $product->store?->id)
                                <div class="tab-pane fade" id="nav-vendor" role="tabpanel" aria-labelledby="nav-vendor-tab" tabindex="0">
                                    <div class="pt-60">
                                        @include(Theme::getThemeNamespace('views.marketplace.includes.vendor-info'), [
                                            'store' => $product->store,
                                        ])
                                    </div>
                                </div>
                            @endif

                            @if (is_plugin_active('faq') && $product->faq_items)
                                <div class="tab-pane fade" id="nav-faq" role="tabpanel" aria-labelledby="nav-faq-tab" tabindex="0">
                                    <div class="pt-60">
                                        @include(EcommerceHelper::viewPath('includes.product-faqs'), ['faqs' => $product->faq_items])
                                    </div>
                                </div>
                            @endif
                            {!! apply_filters('ecommerce_product_detail_extra_tab_contents', null, $product) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! apply_filters('ecommerce_product_detail_after_tabs', null, $product) !!}

    <div class="tp-product-details-sticky-actions">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-none d-lg-flex align-items-center gap-3">
                    <div class="sticky-actions-img">
                        {{ RvMedia::image($product->image, $product->name) }}
                    </div>
                    <div class="sticky-actions-content">
                        <h4 class="fs-6 mb-1">{{ $product->name }}</h4>
                        @include(Theme::getThemeNamespace('views.ecommerce.includes.product.style-1.price'))
                    </div>
                </div>
                @php
                    $isOutOfStock = $product->isOutOfStock();
                @endphp
                <div class="sticky-actions-button d-flex align-items-center gap-2">
                    <button
                        type="submit"
                        name="add-to-cart"
                        @class(['tp-product-details-add-to-cart-btn', 'btn-disabled' => $isOutOfStock])
                        @disabled($isOutOfStock)
                        {!! EcommerceHelper::jsAttributes('add-to-cart-in-form', $product) !!}
                    >
                        {{ __('Add To Cart') }}
                    </button>
                    @if (EcommerceHelper::isQuickBuyButtonEnabled())
                        <button
                            type="submit"
                            name="checkout"
                            @class(['tp-product-details-buy-now-btn', 'btn-disabled' => $isOutOfStock])
                            @disabled($isOutOfStock)
                        >{{ __('Buy Now') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {!! apply_filters('ads_render', null, 'detail_page_after') !!}
</section>

@if (EcommerceHelper::isEnabledRelatedProducts())
    @include(Theme::getThemeNamespace('views.ecommerce.includes.related-products'))
@endif
