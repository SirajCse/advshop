<?php

namespace Botble\Ecommerce\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class InvoiceTemplateSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'template' => ['required', 'string'],
            'content' => ['required', 'string', 'max:1000000'],
            'invoice_template_custom_css' => ['nullable', 'string', 'max:100000'],
        ];
    }
}
