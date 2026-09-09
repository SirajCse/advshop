<div class="customer-info-form">
    @if (!EcommerceHelper::isHideCustomerInfoAtCheckout())
        <div class="mb-3 form-group checkout-login-prompt">
            @if (auth('customer')->check())
                <p>{{ __('Account') }}: <strong>{{ auth('customer')->user()->name }}</strong> - {!! Html::email(auth('customer')->user()->email) !!} (<a href="{{ route('customer.logout') }}">{{ __('Logout') }})</a></p>
            @else
                <p>{{ __('Already have an account?') }} <a href="{{ route('customer.login') }}">{{ __('Login') }}</a></p>
            @endif
        </div>
    @endif

    {!! apply_filters('ecommerce_checkout_customer_info_form_before') !!}

    <div class="form-group mb-3 @error('address.name') has-error @enderror">
        <div class="form-input-wrapper">
            <input
                class="form-control"
                id="address_name"
                name="address[name]"
                autocomplete="family-name"
                type="text"
                placeholder=" "
                value="{{ old('address.name', Arr::get($sessionCheckoutData, 'name')) ?: (auth('customer')->check() ? auth('customer')->user()->name : null) }}"
                required
            >
            <label for="address_name">{{ __('Full Name') }}</label>
        </div>
        {!! Form::error('address.name', $errors) !!}
    </div>

    @if (!in_array('email', EcommerceHelper::getHiddenFieldsAtCheckout()))
        <div class="form-group mb-3 @error('address.email') has-error @enderror">
            <div class="form-input-wrapper">
                <input
                    class="form-control"
                    id="address_email"
                    name="address[email]"
                    autocomplete="email"
                    type="email"
                    placeholder=" "
                    value="{{ old('address.email', Arr::get($sessionCheckoutData, 'email')) ?: (auth('customer')->check() ? auth('customer')->user()->email : null) }}"
                    required
                >
                <label for="address_email">{{ __('Email') }}</label>
            </div>
            {!! Form::error('address.email', $errors) !!}
        </div>
    @endif

    @guest('customer')
        <div id="register-an-account-wrapper">
            <div class="form-group mb-3">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        id="create_account"
                        name="create_account"
                        type="checkbox"
                        value="1"
                        @checked(old('create_account', Arr::get($sessionCheckoutData, 'create_account')))
                    >
                    <label
                        class="form-check-label"
                        for="create_account"
                    >{{ __('Register an account with above information?') }}</label>
                </div>
                <div
                    class="password-group @if (!old('create_account', Arr::get($sessionCheckoutData, 'create_account'))) d-none @endif"
                    style="margin-top: 15px"
                >
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group @error('password') has-error @enderror">
                                <div class="form-input-wrapper">
                                    <input
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        autocomplete="new-password"
                                        type="password"
                                        placeholder=" "
                                        required
                                    >
                                    <label for="password">{{ __('Password') }}</label>
                                </div>
                                {!! Form::error('password', $errors) !!}
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group @error('password_confirmation') has-error @enderror">
                                <div class="form-input-wrapper">
                                    <input
                                        class="form-control"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autocomplete="new-password"
                                        type="password"
                                        placeholder=" "
                                        required
                                    >
                                    <label for="password_confirmation">{{ __('Password confirmation') }}</label>
                                </div>
                                {!! Form::error('password_confirmation', $errors) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endguest

    {!! apply_filters('ecommerce_checkout_customer_info_form_after') !!}
</div>
