<div class="row row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3">
    @foreach($products as $product)
        <div class="col">
            @include(EcommerceHelper::viewPath('includes.product-item'))
        </div>
    @endforeach
</div>

@if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {!! $products->withQueryString()->links() !!}
    </div>
@endif

@include(EcommerceHelper::viewPath('includes.quick-shop-modal'))
@include(EcommerceHelper::viewPath('includes.quick-view-modal'))
