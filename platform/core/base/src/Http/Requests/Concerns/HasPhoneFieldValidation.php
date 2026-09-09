<?php

namespace Botble\Base\Http\Requests\Concerns;

trait HasPhoneFieldValidation
{
    protected function preparePhoneForValidation(): void
    {
        $phone = $this->input('phone', '');
        $phoneDisplay = $this->input('phone_display', '');

        if (! is_string($phone) || ! is_string($phoneDisplay)) {
            return;
        }

        if ($phoneDisplay) {
            if ($phone && str_starts_with($phone, '+')) {
                $finalPhone = $phone;
            } else {
                $finalPhone = $phone ?: $phoneDisplay;
            }
        } else {
            $finalPhone = $phone;
        }

        if (! $finalPhone) {
            return;
        }

        $cleanedPhone = preg_replace('/[^\d+]/', '', $finalPhone);

        if ($cleanedPhone) {
            $this->merge(['phone' => $cleanedPhone]);
        }
    }
}
