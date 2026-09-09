<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class AbandonedCartSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'abandoned_cart_enabled' => $onOffRule = new OnOffRule(),
            'abandoned_cart_send_email' => $onOffRule,
            'abandoned_cart_max_reminders' => ['nullable', 'integer', 'min:1', 'max:3'],
            'abandoned_cart_email_1_delay' => ['nullable', 'integer', 'min:1', 'max:48'],
            'abandoned_cart_email_2_delay' => ['nullable', 'integer', 'min:2', 'max:96'],
            'abandoned_cart_email_3_delay' => ['nullable', 'integer', 'min:24', 'max:168'],
            'abandoned_cart_cleanup_days' => ['nullable', 'integer', 'min:7', 'max:365'],
        ];
    }
}
