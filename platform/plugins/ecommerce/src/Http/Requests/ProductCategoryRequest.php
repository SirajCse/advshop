<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Rules\ProductCategoryParentRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends Request
{
    public function rules(): array
    {
        $current = $this->route('product_category');
        $currentId = $current instanceof ProductCategory ? $current->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:100000'],
            'image' => ['nullable', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                Rule::when($this->input('parent_id'), function () use ($currentId) {
                    return [
                        Rule::exists('ec_product_categories', 'id'),
                        new ProductCategoryParentRule($currentId),
                    ];
                }),
            ],
            'order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'icon_image' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
