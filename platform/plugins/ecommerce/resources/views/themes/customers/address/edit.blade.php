@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.address_books'))

@section('content')
    {!! Form::open(['route' => ['customer.address.edit', $address->id]]) !!}
        @include(EcommerceHelper::viewPath('customers.address.form'), compact('form', 'address'))
    {!! Form::close() !!}
@endsection
