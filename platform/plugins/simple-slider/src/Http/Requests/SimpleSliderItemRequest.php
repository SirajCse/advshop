<?php

namespace Botble\SimpleSlider\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class SimpleSliderItemRequest extends Request
{
    public function rules(): array
    {
        return [
            'simple_slider_id' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:0', 'max:1000'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
