<?php

namespace Botble\Ecommerce\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class AccountDeletionRequest extends Request
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'reason' => [
                'description' => 'Optional reason provided by the customer for deleting their account.',
                'example' => 'I no longer need the account.',
            ],
        ];
    }
}
