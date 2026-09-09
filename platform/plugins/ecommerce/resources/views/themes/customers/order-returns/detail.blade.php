@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.return_products'))

@section('content')
    @php
        Theme::set('pageName', trans('plugins/ecommerce::customer-dashboard.return_products'));
    @endphp

    @include(EcommerceHelper::viewPath('includes.order-return-detail'))
@stop
