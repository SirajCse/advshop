<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Ecommerce\Enums\OrderReturnReasonEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class OrderReturnRequest extends Request
{
    protected function prepareForValidation(): void
    {
        if (EcommerceHelper::allowPartialReturn() && $this->has('return_items')) {
            $this->merge([
                'return_items' => array_filter(
                    $this->input('return_items', []),
                    fn ($item) => ! empty($item['is_return'])
                ),
            ]);
        }
    }

    public function rules(): array
    {
        $validReasons = array_values(array_filter(
            OrderReturnReasonEnum::toArray(),
            fn ($value) => $value !== '' && $value !== null
        ));

        $rules = [
            'order_id' => ['required', 'integer', 'exists:ec_orders,id', 'unique:ec_order_returns,order_id'],
            'return_items' => ['required', 'array', 'min:1'],
            'return_items.*.is_return' => ['sometimes'],
            'return_items.*.order_item_id' => [
                'required_with:return_items.*.is_return,checked',
                'numeric',
                'exists:ec_order_product,id',
            ],
            'return_items.*.qty' => ['nullable', 'numeric', 'min:1'],
        ];

        if (! EcommerceHelper::allowPartialReturn()) {
            $rules['reason'] = ['required', 'string', Rule::in($validReasons)];
        } else {
            $rules['return_items.*.reason'] = ['required', 'string', Rule::in($validReasons)];
        }

        if (EcommerceHelper::isReturnImageUploadEnabled()) {
            $rules['images'] = ['nullable', 'array', 'max:' . EcommerceHelper::returnMaxFileNumber()];
            $rules['images.*'] = ['image', 'mimes:jpg,jpeg,png', 'max:' . EcommerceHelper::returnMaxFileSize(true)];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'order_id' => trans('plugins/ecommerce::order.order_id'),
            'reason' => trans('plugins/ecommerce::order.refund_reason'),
            'return_items.*.order_item_id' => trans('plugins/ecommerce::order.product_id'),
            'return_items.*.reason' => trans('plugins/ecommerce::order.refund_reason'),
            'return_items.*.qty' => trans('plugins/ecommerce::products.quantity'),
            'return_items.*.is_return' => trans('plugins/ecommerce::order.is_return'),
            'images' => trans('plugins/ecommerce::order.return_images'),
            'images.*' => trans('plugins/ecommerce::order.return_image'),
        ];
    }

    public function messages(): array
    {
        return [
            'unique' => trans('plugins/ecommerce::order.return_order_unique'),
            'reason.required' => trans('plugins/ecommerce::order.return_reason_required'),
            'reason.in' => trans('plugins/ecommerce::order.return_reason_invalid'),
            'return_items.*.reason.required' => trans('plugins/ecommerce::order.return_reason_required'),
            'return_items.*.reason.in' => trans('plugins/ecommerce::order.return_reason_invalid'),
            'return_items.min' => trans('plugins/ecommerce::order.return_items_required'),
        ];
    }

    /**
     * Get the body parameters for API documentation.
     */
    public function bodyParameters(): array
    {
        $params = [
            'order_id' => [
                'description' => 'The ID of the order to return',
                'example' => 1,
            ],
            'return_items' => [
                'description' => 'Array of items to return',
                'example' => [
                    [
                        'is_return' => true,
                        'order_item_id' => 1,
                        'qty' => 2,
                    ],
                ],
            ],
            'return_items.*.is_return' => [
                'description' => 'Whether this item should be returned',
                'example' => true,
            ],
            'return_items.*.order_item_id' => [
                'description' => 'The ID of the order item to return',
                'example' => 1,
            ],
            'return_items.*.qty' => [
                'description' => 'The quantity to return',
                'example' => 2,
            ],
        ];

        if (! EcommerceHelper::allowPartialReturn()) {
            $params['reason'] = [
                'description' => 'The reason for the return. Must be one of the values from OrderReturnReasonEnum.',
                'example' => OrderReturnReasonEnum::DAMAGED,
            ];
        } else {
            $params['return_items.*.reason'] = [
                'description' => 'The reason for returning this item. Must be one of the values from OrderReturnReasonEnum.',
                'example' => OrderReturnReasonEnum::DAMAGED,
            ];
        }

        return $params;
    }
}
