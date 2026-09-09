<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Rules\EmailRule;
use Botble\Base\Rules\MediaImageRule;
use Botble\Ecommerce\Enums\ReviewBadgeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ReviewRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'images' => array_filter($this->input('images', []) ?? []),
        ]);
    }

    public function rules(): array
    {
        return [
            'created_at' => ['required', 'date'],
            'product_id' => ['required', 'exists:ec_products,id'],
            'customer_id' => ['nullable', 'exists:ec_customers,id'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_email' => ['nullable', new EmailRule(), 'max:50'],
            'star' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => [get_ecommerce_setting('review_comment_required', 1) ? 'required' : 'nullable', 'string', 'max:5000'],
            'badge_type' => ['nullable', Rule::in(ReviewBadgeEnum::values())],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', new MediaImageRule()],
        ];
    }
}
