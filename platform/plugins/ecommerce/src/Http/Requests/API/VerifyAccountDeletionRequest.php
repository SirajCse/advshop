<?php

namespace Botble\Ecommerce\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class VerifyAccountDeletionRequest extends Request
{
    public function rules(): array
    {
        return [
            'verification_code' => ['required', 'string', 'size:6'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'verification_code' => [
                'description' => 'The 6-character code sent to confirm account deletion.',
                'example' => '123456',
            ],
        ];
    }
}
