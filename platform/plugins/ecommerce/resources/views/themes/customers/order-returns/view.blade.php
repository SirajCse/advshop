@extends(EcommerceHelper::viewPath('customers.master'))

@section('title',  trans('plugins/ecommerce::customer-dashboard.request_return_products'))

@section('content')
    @include(EcommerceHelper::viewPath('includes.order-return-form'))
@stop
