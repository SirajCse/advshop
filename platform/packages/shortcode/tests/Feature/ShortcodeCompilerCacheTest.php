<?php

namespace Botble\Shortcode\Tests\Feature;

use Botble\Shortcode\Compilers\ShortcodeCompiler;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShortcodeCompilerCacheTest extends TestCase
{
    protected ShortcodeCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new ShortcodeCompiler();
        Cache::flush();
    }

    public function testRenderUsesCache(): void
    {
        $callCount = 0;

        $this->compiler->add('test-block', 'Test Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Test Content</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[test-block][/test-block]';

        $result1 = $this->compiler->compile($shortcodeHtml);
        $this->assertStringContainsString('Test Content', $result1);
        $this->assertEquals(1, $callCount, 'Callback should be called on first render');

        $result2 = $this->compiler->compile($shortcodeHtml);
        $this->assertStringContainsString('Test Content', $result2);
        $this->assertEquals(1, $callCount, 'Callback should NOT be called on second render (cache hit)');

        $this->assertEquals($result1, $result2, 'Cached result should match first render');
    }

    public function testRenderSkipsCacheWhenDisabled(): void
    {
        $callCount = 0;

        $this->compiler->add('test-block', 'Test Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Test Content</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', false);
        setting()->save();

        $shortcodeHtml = '[test-block][/test-block]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Callback should be called every time when cache is disabled');
    }

    public function testRenderSkipsCacheWhenShortcodeOptOut(): void
    {
        $callCount = 0;

        $this->compiler->add('test-block', 'Test Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Test Content</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[test-block enable_caching="no"][/test-block]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Callback should be called every time when shortcode opts out of caching');
    }

    public function testRenderSkipsCacheForIgnoredShortcodes(): void
    {
        $callCount = 0;

        $this->compiler->add('test-block', 'Test Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Test Content</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        ShortcodeCompiler::ignoreCaches(['test-block']);

        $shortcodeHtml = '[test-block][/test-block]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Callback should be called every time for ignored shortcodes');
    }

    public function testShouldBlockCacheForContactForm(): void
    {
        $callCount = 0;

        $this->compiler->add('contact-form', 'Contact Form', null, function () use (&$callCount) {
            $callCount++;

            return '<form><input type="hidden" name="_token" value="abc"></form>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[contact-form][/contact-form]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Contact form should not be cached');
    }

    public function testShouldNotBlockCacheForNonFormShortcodes(): void
    {
        $callCount = 0;

        $this->compiler->add('hero-banner', 'Hero Banner', null, function () use (&$callCount) {
            $callCount++;

            return '<div class="hero"><form><input type="text" name="search"></form></div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[hero-banner][/hero-banner]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(1, $callCount, 'Non-form shortcodes should be cached even if they contain form HTML');
    }

    public function testSkipsCacheForShortcodeWithCsrfToken(): void
    {
        $callCount = 0;

        $this->compiler->add('custom-form', 'Custom Form', null, function () use (&$callCount) {
            $callCount++;

            return '<form><input type="hidden" name="_token" value="' . csrf_token() . '"></form>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[custom-form][/custom-form]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Shortcodes with CSRF tokens should not be cached');
    }

    public function testSkipsCacheForShortcodeWithRecaptcha(): void
    {
        $callCount = 0;

        $this->compiler->add('recaptcha-form', 'Recaptcha Form', null, function () use (&$callCount) {
            $callCount++;

            return '<div class="g-recaptcha" data-sitekey="abc"></div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[recaptcha-form][/recaptcha-form]';

        $this->compiler->compile($shortcodeHtml);
        $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Shortcodes with reCAPTCHA should not be cached');
    }

    public function testCacheKeyVariesByLocale(): void
    {
        $callCount = 0;

        $this->compiler->add('test-block', 'Test Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Content ' . $callCount . '</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        $shortcodeHtml = '[test-block][/test-block]';

        app()->setLocale('en');
        $resultEn = $this->compiler->compile($shortcodeHtml);

        app()->setLocale('hu');
        $resultHu = $this->compiler->compile($shortcodeHtml);

        $this->assertEquals(2, $callCount, 'Different locales should produce separate cache entries');
        $this->assertNotEquals($resultEn, $resultHu);
    }

    public function testCacheStoresEntry(): void
    {
        $callCount = 0;

        $this->compiler->add('test-ttl-block', 'Test TTL Block', null, function () use (&$callCount) {
            $callCount++;

            return '<div>Test</div>';
        });

        $this->compiler->enable();

        setting()->set('shortcode_cache_enabled', true);
        setting()->set('shortcode_cache_ttl', 3600);
        setting()->save();

        $shortcodeHtml = '[test-ttl-block][/test-ttl-block]';
        $this->compiler->compile($shortcodeHtml);
        $this->assertEquals(1, $callCount);

        $this->compiler->compile($shortcodeHtml);
        $this->assertEquals(1, $callCount, 'Cache entry should persist and prevent re-rendering');
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(ShortcodeCompiler::class);
        $reflection->setStaticPropertyValue('ignoredCaches', []);
        $reflection->setStaticPropertyValue('cacheableFormShortcodes', []);

        Cache::flush();

        parent::tearDown();
    }
}
