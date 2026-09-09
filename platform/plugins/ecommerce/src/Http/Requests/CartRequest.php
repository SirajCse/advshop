<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Support\Arr;

class CartRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $id = $this->input('id');

        if (is_array($id)) {
            $id = Arr::first($id);
        }

        if ($id !== null) {
            $this->merge(['id' => (string) $id]);
        }
    }

    public function rules(): array
    {
        return [
            'id' => ['required'],
            'qty' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => __('Product ID is required'),
        ];
    }
}
