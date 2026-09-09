<?php

namespace Botble\Media\Http\Requests;

use Botble\Support\Http\Requests\Request;

class MediaListRequest extends Request
{
    public function rules(): array
    {
        return [
            'folder_id' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'include_files' => ['nullable', 'string'],
        ];
    }
}
