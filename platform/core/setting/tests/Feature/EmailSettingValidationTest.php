<?php

namespace Botble\Setting\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmailSettingValidationTest extends TestCase
{
    protected function getEmailDefaultLocaleRules(): array
    {
        return ['nullable', 'string', 'max:20'];
    }

    public function test_email_default_locale_accepts_valid_locale(): void
    {
        $validator = Validator::make(
            ['email_default_locale' => 'ar'],
            ['email_default_locale' => $this->getEmailDefaultLocaleRules()]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_email_default_locale_accepts_empty_string(): void
    {
        $validator = Validator::make(
            ['email_default_locale' => ''],
            ['email_default_locale' => $this->getEmailDefaultLocaleRules()]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_email_default_locale_accepts_null(): void
    {
        $validator = Validator::make(
            ['email_default_locale' => null],
            ['email_default_locale' => $this->getEmailDefaultLocaleRules()]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_email_default_locale_rejects_too_long_value(): void
    {
        $validator = Validator::make(
            ['email_default_locale' => str_repeat('a', 21)],
            ['email_default_locale' => $this->getEmailDefaultLocaleRules()]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_default_locale_accepts_common_locales(): void
    {
        $locales = ['en', 'ar', 'fr', 'de', 'ja', 'zh-CN', 'pt-BR', 'vi'];

        foreach ($locales as $locale) {
            $validator = Validator::make(
                ['email_default_locale' => $locale],
                ['email_default_locale' => $this->getEmailDefaultLocaleRules()]
            );

            $this->assertTrue($validator->passes(), "Locale '$locale' should be accepted");
        }
    }
}
