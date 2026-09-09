<?php

namespace Tests\Unit\Plugins\Captcha;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Regression tests for the captcha plugin's `core_request_attributes` filter.
 *
 * Bug: CaptchaServiceProvider::boot() merged CaptchaFacade::attributes() into the
 * filtered attributes array WITHOUT the spread operator, e.g.:
 *
 *     return [...$attributes, CaptchaFacade::attributes()];
 *
 * That appended the captcha attributes as a single nested array at numeric
 * index 0 of the validator's customAttributes map. When the validator later
 * resolved an attribute name to that nested array, Laravel's
 * `replaceAttributePlaceholder` called `Str::upper($value)` on it, raising
 * `TypeError: mb_strtoupper(): Argument #1 ($string) must be of type string, array given`
 * (vendor/laravel/framework/src/Illuminate/Support/Str.php:1429).
 *
 * Fix: add the spread on the captcha attributes too:
 *
 *     return [...$attributes, ...CaptchaFacade::attributes()];
 *
 * @see platform/plugins/captcha/src/Providers/CaptchaServiceProvider.php
 */
class CoreRequestAttributesFilterTest extends TestCase
{
    private const PROVIDER_PATH = __DIR__ . '/../../../../platform/plugins/captcha/src/Providers/CaptchaServiceProvider.php';

    public function test_provider_source_uses_spread_when_merging_captcha_attributes(): void
    {
        $source = file_get_contents(self::PROVIDER_PATH);

        $this->assertNotFalse($source, 'CaptchaServiceProvider source must be readable.');

        $this->assertMatchesRegularExpression(
            '/add_filter\(\s*[\'"]core_request_attributes[\'"].*?\.\.\.CaptchaFacade::attributes\(\)/s',
            $source,
            'core_request_attributes filter must spread CaptchaFacade::attributes() to keep the result a flat associative array.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/add_filter\(\s*[\'"]core_request_attributes[\'"].*?(?<!\.\.\.)CaptchaFacade::attributes\(\)/s',
            $source,
            'core_request_attributes filter must not append CaptchaFacade::attributes() without the spread operator (regression of nested-array bug).'
        );
    }

    public function test_spread_merge_produces_flat_associative_array(): void
    {
        $base = [
            'name' => 'Name',
            'email' => 'Email',
            'agree_terms_and_policy' => 'Agree terms and policy',
        ];

        $captchaAttributes = [
            'captcha' => 'Captcha',
            'math-captcha' => 'Math Captcha',
        ];

        // Mirrors the FIXED filter in CaptchaServiceProvider::boot().
        $merged = [...$base, ...$captchaAttributes];

        foreach (array_keys($merged) as $key) {
            $this->assertIsString($key, 'Merged customAttributes must not contain integer keys.');
        }

        foreach ($merged as $value) {
            $this->assertIsString(
                $value,
                'Merged customAttributes must not contain nested arrays — they break Str::upper() in validator message replacement.'
            );
        }

        $this->assertSame(
            ['name', 'email', 'agree_terms_and_policy', 'captcha', 'math-captcha'],
            array_keys($merged)
        );
    }

    public function test_buggy_merge_without_spread_produces_nested_array_at_integer_key(): void
    {
        $base = ['name' => 'Name'];
        $captchaAttributes = ['captcha' => 'Captcha', 'math-captcha' => 'Math Captcha'];

        // Mirrors the ORIGINAL buggy filter — kept here as an executable
        // counter-example so a regression is unambiguous.
        $merged = [...$base, $captchaAttributes];

        $this->assertArrayHasKey(0, $merged);
        $this->assertIsArray($merged[0]);
        $this->assertSame($captchaAttributes, $merged[0]);
    }

    public function test_str_upper_throws_type_error_when_given_an_array(): void
    {
        // This is the proximate crash documented in the production log:
        // mb_strtoupper(): Argument #1 ($string) must be of type string, array given
        // at vendor/laravel/framework/src/Illuminate/Support/Str.php:1429.
        $this->expectException(TypeError::class);

        Str::upper(['captcha' => 'Captcha']); // @phpstan-ignore-line
    }

    public function test_str_upper_handles_string_attribute_label_safely(): void
    {
        $this->assertSame('AGREE TERMS AND POLICY', Str::upper('Agree terms and policy'));
        $this->assertSame('CAPTCHA', Str::upper('Captcha'));
        $this->assertSame('MATH-CAPTCHA', Str::upper('math-captcha'));
    }
}
