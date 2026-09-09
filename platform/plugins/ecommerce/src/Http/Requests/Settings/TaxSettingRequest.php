<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class TaxSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'ecommerce_tax_enabled' => $onOffRule = new OnOffRule(),
            'display_tax_fields_at_checkout_page' => $onOffRule,
            'display_product_price_including_taxes' => $onOffRule,
            'display_checkout_tax_information' => $onOffRule,
            'display_item_tax_at_checkout' => $onOffRule,
            'display_tax_description' => $onOffRule,
            'tax_on_shipping_fee' => $onOffRule,
        ];
    }
}
