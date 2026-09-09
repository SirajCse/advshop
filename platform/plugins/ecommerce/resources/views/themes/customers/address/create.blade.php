@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.add_new_address'))

@section('content')
    {!! Form::open(['route' => 'customer.address.create']) !!}
        @include(EcommerceHelper::viewPath('customers.address.form'), ['address' => new Botble\Ecommerce\Models\Address(), 'form'])
    {!! Form::close() !!}
@endsection
