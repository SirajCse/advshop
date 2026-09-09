<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductLabelRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'color' => ['required', 'string', 'min:4', 'max:20'],
            'text_color' => ['nullable', 'string', 'min:4', 'max:20'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
