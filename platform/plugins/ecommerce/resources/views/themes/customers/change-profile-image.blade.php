@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.change_avatar'))

@section('content')
    <div class="panel panel-default">
        <div class="panel-body">

            {!! Form::open(['route' => 'customer.change-avatar', 'files' => true]) !!}

            <label class="btn-bs-file btn btn-lg btn-primary">
                {{ trans('plugins/ecommerce::customer-dashboard.select_file') }}
                <input
                    id="avatar"
                    name="avatar"
                    type="file"
                />
            </label>

            {!! Form::error('avatar', $errors) !!}

            <div class="form-group col s12 text-center">
                <button
                    class="btn btn-primary btn-sm"
                    id="change-avatar-btn"
                    type="submit"
                >{{ trans('plugins/ecommerce::customer-dashboard.update') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
