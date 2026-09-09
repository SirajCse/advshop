<?php

namespace Botble\LanguageAdvanced\Http\Requests;

use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class LanguageAdvancedRequest extends Request
{
    public function rules(): array
    {
        return [
            'model' => ['required', 'string', 'max:255'],
            'language' => [
                'nullable',
                'string',
                'max:20',
                Rule::in(LanguageAdvancedManager::getActiveLanguageCodes()),
            ],
            'slug_id' => ['nullable', 'string', 'min:1'],
        ];
    }
}
