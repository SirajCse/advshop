@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/ecommerce::customer-dashboard.account_settings'))

@section('content')
    {!! apply_filters('ecommerce_customer_edit_account_before_content', '') !!}

    <div class="bb-customer-card-list account-settings-cards">
        {{-- Profile Information Card --}}
        <div class="bb-customer-card profile-card">
            <div class="bb-customer-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <x-core::icon name="ti ti-user" class="text-primary" />
                    </div>
                    <div>
                        <h3 class="bb-customer-card-title h5 mb-1">{{ trans('plugins/ecommerce::customer-dashboard.profile_information') }}</h3>
                        <p class="text-muted small mb-0">{{ trans('plugins/ecommerce::customer-dashboard.update_profile_description') }}</p>
                    </div>
                </div>
            </div>
            <div class="bb-customer-card-body">
                {!! $form->renderForm() !!}
            </div>
        </div>

        {{-- Change Password Card --}}
        <div class="bb-customer-card password-card">
            <div class="bb-customer-card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                        <x-core::icon name="ti ti-lock" class="text-warning" />
                    </div>
                    <div>
                        <h3 class="bb-customer-card-title h5 mb-1">{{ trans('plugins/ecommerce::customer-dashboard.change_password') }}</h3>
                        <p class="text-muted small mb-0">{{ trans('plugins/ecommerce::customer-dashboard.ensure_secure_password') }}</p>
                    </div>
                </div>
            </div>
            <div class="bb-customer-card-body">
                {!! $passwordForm->renderForm() !!}
            </div>
        </div>

        {{-- Delete Account Card --}}
        @if (get_ecommerce_setting('enabled_customer_account_deletion', true))
            <div class="bb-customer-card delete-account-card">
                <div class="bb-customer-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                            <x-core::icon name="ti ti-trash" class="text-danger" />
                        </div>
                        <div>
                            <h3 class="bb-customer-card-title h5 mb-1 text-danger">{{ trans('plugins/ecommerce::customer-dashboard.delete_account') }}</h3>
                            <p class="text-muted small mb-0">{{ trans('plugins/ecommerce::customer-dashboard.delete_account_description') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bb-customer-card-body">
                    <div class="alert alert-warning d-flex align-items-start gap-3" role="alert">
                        <x-core::icon name="ti ti-alert-triangle" class="text-warning flex-shrink-0 mt-1" />
                        <div>
                            <h6 class="alert-heading mb-1">{{ trans('plugins/ecommerce::customer-dashboard.warning') }}</h6>
                            <p class="mb-0 small">
                                {{ trans('plugins/ecommerce::customer-dashboard.delete_account_warning') }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#delete-account-modal"
                        data-toggle="modal"
                        data-target="#delete-account-modal"
                    >
                        <x-core::icon name="ti ti-trash" class="me-1" />
                        {{ trans('plugins/ecommerce::customer-dashboard.delete_your_account') }}
                    </button>
                </div>
            </div>

            {{-- Delete Account Modal --}}
            <div class="modal fade" id="delete-account-modal" tabindex="-1" aria-labelledby="delete-account-modal-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                                    <x-core::icon name="ti ti-alert-triangle" class="text-danger" />
                                </div>
                                <div>
                                    <h4 class="modal-title h5 mb-0" id="delete-account-modal-title">
                                        {{ trans('plugins/ecommerce::customer-dashboard.delete_account') }}
                                    </h4>
                                    <p class="text-muted small mb-0">{{ trans('plugins/ecommerce::customer-dashboard.cannot_be_undone') }}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
                                <x-core::icon name="ti ti-info-circle" class="text-danger flex-shrink-0 mt-1" />
                                <div>
                                    <p class="mb-0 small">
                                        {{ trans('plugins/ecommerce::customer-dashboard.delete_account_email_confirmation') }}
                                    </p>
                                </div>
                            </div>

                            <x-core::form :url="route('customer.delete-account.store')" method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">{{ trans('plugins/ecommerce::customer-dashboard.confirm_your_password') }}</label>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="{{ trans('plugins/ecommerce::customer-dashboard.enter_current_password') }}"
                                        required
                                    >
                                </div>
                                <div class="mb-4">
                                    <label for="reason" class="form-label fw-semibold">{{ trans('plugins/ecommerce::customer-dashboard.reason_optional') }}</label>
                                    <textarea
                                        id="reason"
                                        name="reason"
                                        class="form-control"
                                        rows="3"
                                        placeholder="{{ trans('plugins/ecommerce::customer-dashboard.tell_us_why_delete') }}"
                                    ></textarea>
                                </div>
                                <div class="d-flex gap-3">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal" data-dismiss="modal">
                                        {{ trans('plugins/ecommerce::customer-dashboard.cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-danger flex-fill">
                                        <x-core::icon name="ti ti-trash" class="me-1" />
                                        {{ trans('plugins/ecommerce::customer-dashboard.delete_account') }}
                                    </button>
                                </div>
                            </x-core::form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {!! apply_filters('ecommerce_customer_edit_account_after_content', '') !!}
@endsection
