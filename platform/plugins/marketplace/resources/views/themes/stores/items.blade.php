@foreach($products as $product)
    @include(EcommerceHelper::viewPath('includes.product-item'))
@endforeach
