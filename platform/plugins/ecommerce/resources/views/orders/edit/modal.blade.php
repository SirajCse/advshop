<x-core::modal.action
    id="resend-order-confirmation-email-modal"
    :title="trans('plugins/ecommerce::order.resend_order_confirmation')"
    :description="trans('plugins/ecommerce::order.resend_order_confirmation_description', [
            'email' => $order->user->id ? $order->user->email : $order->address->email,
        ])"
    :submit-button-attrs="['id' => 'confirm-resend-confirmation-email-button']"
    :submit-button-label="trans('plugins/ecommerce::order.send')"
/>

<x-core::modal
    id="update-shipping-address-modal"
    :title="trans('plugins/ecommerce::order.update_address')"
    button-id="confirm-update-shipping-address-button"
    :button-label="trans('plugins/ecommerce::order.update')"
    size="md"
>
    @include('plugins/ecommerce::orders.shipping-address.form', [
        'address' => $order->address,
        'orderId' => $order->id,
        'url' => route($updateShippingAddressRoute, $order->address->id ?? 0),
    ])
</x-core::modal>

<x-core::modal
    id="cancel-order-modal"
    type="warning"
    :title="trans('plugins/ecommerce::order.cancel_order_confirmation')"
    button-id="confirm-cancel-order-button"
    :button-label="trans('plugins/ecommerce::order.cancel_order')"
    size="md"
>
    <p class="text-muted mb-3">{{ trans('plugins/ecommerce::order.cancel_order_confirmation_description') }}</p>

    <div class="mb-3">
        <label class="form-label required" for="cancellation_reason">{{ trans('plugins/ecommerce::order.order_cancellation_reason') }}</label>
        <select class="form-select" name="cancellation_reason" id="cancellation_reason" required>
            <option value="">{{ trans('plugins/ecommerce::order.select_cancellation_reason') }}</option>
            @foreach (\Botble\Ecommerce\Enums\OrderCancellationReasonEnum::labels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" id="cancellation_reason_description_wrapper" style="display: none;">
        <label class="form-label required" for="cancellation_reason_description">{{ trans('plugins/ecommerce::order.cancellation_reason_description') }}</label>
        <textarea
            class="form-control"
            name="cancellation_reason_description"
            id="cancellation_reason_description"
            rows="3"
            minlength="3"
            maxlength="255"
            placeholder="{{ trans('plugins/ecommerce::order.cancellation_reason_description_placeholder') }}"
        ></textarea>
    </div>
</x-core::modal>
