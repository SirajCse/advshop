<?php

namespace Botble\Marketplace\Http\Requests\API;

use Botble\Base\Rules\EmailRule;
use Botble\Support\Http\Requests\Request;

class ContactStoreRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'max:1000'],
        ];

        if (! auth('customer')->check() && ! auth('sanctum')->check()) {
            $rules['name'] = ['required', 'string', 'max:120'];
            $rules['email'] = ['required', new EmailRule()];
        }

        return $rules;
    }

    public function bodyParameters(): array
    {
        return [
            'content' => [
                'description' => 'Message content (max 1000 characters)',
                'example' => 'Hello, I have a question about your products.',
            ],
            'name' => [
                'description' => 'Sender name (required for guests)',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'Sender email (required for guests)',
                'example' => 'john@example.com',
            ],
        ];
    }
}
