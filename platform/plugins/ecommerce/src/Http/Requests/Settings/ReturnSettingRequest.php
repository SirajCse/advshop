<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class ReturnSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'is_enabled_order_return' => $onOffRule = new OnOffRule(),
            'can_custom_return_product_quantity' => $onOffRule,
            'returnable_days' => ['nullable', 'integer', 'min:1'],
            'allow_customer_upload_image_in_return' => $onOffRule,
            'return_max_file_size' => ['nullable', 'integer', 'min:1', 'max:1024'],
            'return_max_file_number' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
