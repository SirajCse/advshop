<?php

namespace Botble\Ecommerce\Http\Requests\API;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'product_id' => ['required', 'exists:ec_products,id'],
            'star' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => [EcommerceHelper::isReviewCommentRequired() ? 'required' : 'nullable', 'string', 'max:5000'],
        ];

        if (EcommerceHelper::isCustomerReviewImageUploadEnabled()) {
            $rules['images'] = 'array|max:' . EcommerceHelper::reviewMaxFileNumber();
            $rules['images.*'] = 'image|mimes:jpg,jpeg,png|max:' . EcommerceHelper::reviewMaxFileSize(true);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'product_id.required' => trans('plugins/ecommerce::review.validations.product_id_required'),
            'product_id.exists' => trans('plugins/ecommerce::review.validations.product_id_exists'),
            'star.required' => trans('plugins/ecommerce::review.validations.star_required'),
            'star.numeric' => trans('plugins/ecommerce::review.validations.star_numeric'),
            'star.min' => trans('plugins/ecommerce::review.validations.star_min'),
            'star.max' => trans('plugins/ecommerce::review.validations.star_max'),
            'comment.required' => trans('plugins/ecommerce::review.validations.comment_required'),
            'comment.string' => trans('plugins/ecommerce::review.validations.comment_string'),
            'comment.max' => trans('plugins/ecommerce::review.validations.comment_max'),
            'images.array' => trans('plugins/ecommerce::review.validations.images_array'),
            'images.max' => trans('plugins/ecommerce::review.validations.images_max', ['max' => EcommerceHelper::reviewMaxFileNumber()]),
            'images.*.image' => trans('plugins/ecommerce::review.validations.images_image'),
            'images.*.mimes' => trans('plugins/ecommerce::review.validations.images_mimes'),
            'images.*.max' => trans('plugins/ecommerce::review.validations.images_file_max', ['max' => EcommerceHelper::reviewMaxFileSize(true)]),
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'product_id' => [
                'description' => 'The ID of the product to review',
                'example' => 1,
            ],
            'star' => [
                'description' => 'The rating from 1 to 5 stars',
                'example' => 5,
            ],
            'comment' => [
                'description' => 'The review comment',
                'example' => 'This is a great product! I highly recommend it.',
            ],
            'images' => [
                'description' => 'Array of images for the review (optional)',
                'example' => null,
            ],
        ];
    }

    /**
     * Handle a failed validation attempt for API requests.
     * Returns all validation errors joined together instead of "(and X more errors)" suffix.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->all();
        $message = implode(' ', $errors);

        throw new HttpResponseException(
            response()->json([
                'message' => $message,
                'errors' => $validator->errors()->toArray(),
            ], 422)
        );
    }
}
