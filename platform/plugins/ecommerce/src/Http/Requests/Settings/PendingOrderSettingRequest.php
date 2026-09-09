<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class PendingOrderSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'auto_cancel_pending_orders_enabled' => new OnOffRule(),
            'auto_cancel_pending_orders_threshold_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ];
    }
}
