<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ShoppingSettingRequest extends Request
{
    public function rules(): array
    {
        $paymentProofMethodsRule = ['required', 'string'];

        $paymentMethodEnumClass = 'Botble\Payment\Enums\PaymentMethodEnum';

        if (class_exists($paymentMethodEnumClass)) {
            $paymentProofMethodsRule[] = Rule::in($paymentMethodEnumClass::values());
        }

        return [
            'shopping_cart_enabled' => $onOffRule = new OnOffRule(),
            'cart_destroy_on_logout' => $onOffRule,
            'wishlist_enabled' => $onOffRule,
            'wishlist_sharing' => $onOffRule,
            'shared_wishlist_lifetime' => [Rule::when($this->boolean('wishlist_sharing'), 'required', 'nullable'), 'integer', 'min:0'],
            'compare_enabled' => $onOffRule,
            'order_tracking_enabled' => $onOffRule,
            'order_tracking_method' => ['nullable', 'string', 'in:email,phone'],
            'payment_proof_enabled' => $onOffRule,
            'payment_proof_payment_methods' => ['nullable', 'array'],
            'payment_proof_payment_methods.*' => $paymentProofMethodsRule,
            'guest_payment_proof_enabled' => $onOffRule,
            'enable_quick_buy_button' => $onOffRule,
            'order_auto_confirmed' => $onOffRule,
            'order_deletion_enabled' => $onOffRule,
            'quick_buy_target_page' => ['nullable', 'required_if:enable_quick_buy_button,1', 'in:checkout,cart'],
            'hide_product_price' => $onOffRule,
            'hide_product_price_when_zero' => $onOffRule,
        ];
    }
}
