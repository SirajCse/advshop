<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Support\Http\Requests\Request;

class TaxInformationSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'tax_info.*' => ['nullable', 'array'],
            'tax_info.business_name' => ['nullable', 'string', 'max:255'],
            'tax_info.tax_id' => ['nullable', 'string', 'max:255'],
            'tax_info.address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge(['tax_info' => BaseHelper::sanitizeUtf8($this->input('tax_info', []))]);
    }
}
