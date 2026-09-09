<?php

namespace Botble\Shortcode\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShortcodeControllerCacheTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->user = $this->createUser();
    }

    protected function createUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

    public function testAjaxRenderUiBlockCachesContent(): void
    {
        $this->actingAs($this->user);

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        shortcode()->register('test-cached', 'Test Cached', null, function () {
            return '<div>Cached Block</div>';
        });

        $payload = [
            'name' => 'test-cached',
            'attributes' => [],
        ];

        $response1 = $this->postJson(route('public.ajax.render-ui-block'), $payload);
        $response1->assertSuccessful();

        $response2 = $this->postJson(route('public.ajax.render-ui-block'), $payload);
        $response2->assertSuccessful();

        $this->assertEquals(
            $response1->json('data'),
            $response2->json('data'),
            'Second request should return cached content identical to first'
        );
    }

    public function testAjaxRenderUiBlockSkipsCacheWhenDisabled(): void
    {
        $this->actingAs($this->user);

        setting()->set('shortcode_cache_enabled', false);
        setting()->save();

        shortcode()->register('test-no-cache', 'Test No Cache', null, function () {
            return '<div>No Cache Block</div>';
        });

        $response = $this->postJson(route('public.ajax.render-ui-block'), [
            'name' => 'test-no-cache',
            'attributes' => [],
        ]);

        $response->assertSuccessful();
        $this->assertStringContainsString('No Cache Block', $response->json('data'));
    }

    public function testAjaxRenderUiBlockSkipsCacheWhenShortcodeOptsOut(): void
    {
        $this->actingAs($this->user);

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        shortcode()->register('test-opt-out', 'Test Opt Out', null, function () {
            return '<div>Opt Out Block</div>';
        });

        $response = $this->postJson(route('public.ajax.render-ui-block'), [
            'name' => 'test-opt-out',
            'attributes' => ['enable_caching' => 'no'],
        ]);

        $response->assertSuccessful();
        $this->assertStringContainsString('Opt Out Block', $response->json('data'));

        $authorized = auth()->check() ? 'auth' : 'anon';
        $cacheKey = 'shortcode_' . md5('test-opt-out' . url('/') . serialize(['enable_caching' => 'no']) . app()->getLocale() . $authorized);
        $this->assertFalse(Cache::has($cacheKey), 'Cache should not be set when shortcode opts out');
    }

    public function testAjaxRenderUiBlockSkipsCacheForVisualBuilder(): void
    {
        $this->actingAs($this->user);

        setting()->set('shortcode_cache_enabled', true);
        setting()->save();

        shortcode()->register('test-vb', 'Test VB', null, function () {
            return '<div>Visual Builder Block</div>';
        });

        $response = $this->postJson(route('public.ajax.render-ui-block'), [
            'name' => 'test-vb',
            'attributes' => [],
            'shortcodeId' => 'abc-123',
        ]);

        $response->assertSuccessful();
        $this->assertStringContainsString('Visual Builder Block', $response->json('data'));
    }

    public function testAjaxRenderUiBlockUsesSettingTtl(): void
    {
        $this->actingAs($this->user);

        setting()->set('shortcode_cache_enabled', true);
        setting()->set('shortcode_cache_ttl', 3600);
        setting()->save();

        shortcode()->register('test-ttl', 'Test TTL', null, function () {
            return '<div>TTL Block</div>';
        });

        $this->postJson(route('public.ajax.render-ui-block'), [
            'name' => 'test-ttl',
            'attributes' => [],
        ]);

        $authorized = auth()->check() ? 'auth' : 'anon';
        $extraCacheKeys = apply_filters('shortcode_cache_key_parts', [], 'test-ttl');
        $cacheKey = 'shortcode_' . md5('test-ttl' . url('/') . serialize([]) . app()->getLocale() . $authorized . serialize($extraCacheKeys));
        $this->assertTrue(Cache::has($cacheKey), 'Cache entry should exist after rendering');
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }
}
