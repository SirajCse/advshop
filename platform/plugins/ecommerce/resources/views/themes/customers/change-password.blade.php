@extends(EcommerceHelper::viewPath('customers.layouts.account-settings'))

@section('title', trans('plugins/ecommerce::customer-dashboard.change_password'))

@section('account-content')
    {!! $form->renderForm() !!}
@stop
