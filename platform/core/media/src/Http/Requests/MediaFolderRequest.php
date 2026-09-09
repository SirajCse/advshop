<?php

namespace Botble\Media\Http\Requests;

use Botble\Base\Rules\ColorRule;
use Botble\Support\Http\Requests\Request;

class MediaFolderRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[\pL\s\ \_\-0-9]+$/u'],
            'color' => ['nullable', new ColorRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => trans('core/media::media.name_invalid'),
        ];
    }
}
