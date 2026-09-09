<?php

namespace Botble\Language\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Illuminate\Http\Request;

class LanguageNegotiatorTest extends BaseTestCase
{
    protected array $supportedLanguages;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supportedLanguages = [
            'ar' => [
                'lang_name' => 'Arabic',
                'lang_locale' => 'ar',
                'lang_code' => 'ar',
                'lang' => 'ar',
            ],
            'en' => [
                'lang_name' => 'English',
                'lang_locale' => 'en',
                'lang_code' => 'en_US',
                'lang' => 'en',
                'regional' => 'en_US',
            ],
            'fr' => [
                'lang_name' => 'French',
                'lang_locale' => 'fr',
                'lang_code' => 'fr_FR',
                'lang' => 'fr',
                'regional' => 'fr_FR',
            ],
        ];
    }

    protected function createNegotiator(string $defaultLocale, Request $request): object
    {
        $class = 'Botble\\Language\\LanguageNegotiator';

        return new $class($defaultLocale, $this->supportedLanguages, $request);
    }

    public function testNegotiatesExactMatchFromAcceptLanguage(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('en', $negotiator->negotiateLanguage());
    }

    public function testNegotiatesHighestQualityFactor(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'fr;q=0.5, en;q=0.9, ar;q=0.3');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('en', $negotiator->negotiateLanguage());
    }

    public function testNegotiatesWithCountryCodeVariant(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en-US;q=0.9');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('en', $negotiator->negotiateLanguage());
    }

    public function testFallsBackToDefaultWhenNoMatch(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'zh;q=0.9, ko;q=0.8');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('ar', $negotiator->negotiateLanguage());
    }

    public function testFallsBackToDefaultWhenNoHeader(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->remove('Accept-Language');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('ar', $negotiator->negotiateLanguage());
    }

    public function testWildcardReturnsFirstSupported(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', '*');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('ar', $negotiator->negotiateLanguage());
    }

    public function testMultipleLanguagesWithDefaultQualityFactors(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'fr, en');

        $negotiator = $this->createNegotiator('ar', $request);

        $this->assertEquals('fr', $negotiator->negotiateLanguage());
    }

    public function testComplexAcceptLanguageHeader(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('Accept-Language', 'en-UK;q=0.7, en-US;q=0.6, fr, ar;q=0.8');

        $negotiator = $this->createNegotiator('en', $request);

        $this->assertEquals('fr', $negotiator->negotiateLanguage());
    }
}
