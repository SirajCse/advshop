<?php

namespace Botble\Base\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Http\Middleware\PublicCacheControl;
use Botble\Base\Supports\BaseTestCase;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Cookie;

class PublicCacheControlTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['core.base.general.enable_public_cache_control' => false]);
        config(['core.base.general.public_cache_max_age' => 600]);
    }

    protected function invokeHandler(Request $request, Response $response): void
    {
        $handler = new PublicCacheControl();
        $handler->handleRequestHandled(new RequestHandled($request, $response));
    }

    public function test_disabled_by_default_does_not_apply_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => false]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content', 200, [
            'Cache-Control' => 'private, max-age=0',
        ]);

        $this->invokeHandler($request, $response);

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=0', $response->headers->get('Cache-Control'));
    }

    public function test_enabled_anonymous_get_request_applies_public_cache(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=600', $cacheControl);
        $this->assertStringContainsString('s-maxage=600', $cacheControl);
    }

    public function test_custom_max_age_config_is_respected(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['core.base.general.public_cache_max_age' => 3600]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=3600', $cacheControl);
        $this->assertStringContainsString('s-maxage=3600', $cacheControl);
    }

    public function test_post_request_skips_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'POST');
        $response = new Response('Test content', 200, ['Cache-Control' => 'private']);

        $this->invokeHandler($request, $response);

        $this->assertEquals('private', $response->headers->get('Cache-Control'));
    }

    public function test_put_request_skips_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'PUT');
        $response = new Response('Test content', 200, ['Cache-Control' => 'private']);

        $this->invokeHandler($request, $response);

        $this->assertEquals('private', $response->headers->get('Cache-Control'));
    }

    public function test_delete_request_skips_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'DELETE');
        $response = new Response('Test content', 200, ['Cache-Control' => 'private']);

        $this->invokeHandler($request, $response);

        $this->assertEquals('private', $response->headers->get('Cache-Control'));
    }

    public function test_head_request_applies_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'HEAD');
        $response = new Response('');

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=600', $cacheControl);
    }

    public function test_authenticated_user_skips_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => bcrypt('password'),
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);
        Auth::login($user);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content', 200, ['Cache-Control' => 'private']);

        $this->invokeHandler($request, $response);

        $this->assertEquals('private', $response->headers->get('Cache-Control'));

        Auth::logout();
    }

    public function test_json_request_skips_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = new Response(json_encode(['data' => 'test']), 200, [
            'Cache-Control' => 'private',
            'Content-Type' => 'application/json',
        ]);

        $this->invokeHandler($request, $response);

        $this->assertEquals('private', $response->headers->get('Cache-Control'));
    }

    public function test_pragma_header_is_removed_when_cache_applied(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content', 200, ['Pragma' => 'no-cache']);

        $this->invokeHandler($request, $response);

        $this->assertNull($response->headers->get('Pragma'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    public function test_pragma_header_preserved_when_cache_not_applied(): void
    {
        config(['core.base.general.enable_public_cache_control' => false]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content', 200, ['Pragma' => 'no-cache']);

        $this->invokeHandler($request, $response);

        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
    }

    public function test_session_cookies_removed_when_cache_applied(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['session.cookie' => 'botble_session']);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));

        $this->invokeHandler($request, $response);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertNotContains('XSRF-TOKEN', $cookieNames);
        $this->assertNotContains('botble_session', $cookieNames);
    }

    public function test_non_session_cookies_preserved_when_cache_applied(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['session.cookie' => 'botble_session']);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));
        $response->headers->setCookie(new Cookie('cookie_consent', 'accepted'));

        $this->invokeHandler($request, $response);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertNotContains('XSRF-TOKEN', $cookieNames);
        $this->assertNotContains('botble_session', $cookieNames);
        $this->assertContains('cookie_consent', $cookieNames);
    }

    public function test_session_cookies_preserved_when_cache_not_applied(): void
    {
        config(['core.base.general.enable_public_cache_control' => false]);
        config(['session.cookie' => 'botble_session']);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));

        $this->invokeHandler($request, $response);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertContains('XSRF-TOKEN', $cookieNames);
        $this->assertContains('botble_session', $cookieNames);
    }

    public function test_error_responses_skip_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'GET');

        foreach ([404, 500, 403, 503] as $statusCode) {
            $response = new Response('Error', $statusCode, ['Cache-Control' => 'no-cache, private']);

            $this->invokeHandler($request, $response);

            $this->assertStringContainsString('private', $response->headers->get('Cache-Control'), "Status $statusCode should not get public cache");
        }
    }

    public function test_redirect_responses_skip_cache_control(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);

        $request = Request::create('/', 'GET');
        $response = new Response('', 302, ['Cache-Control' => 'no-cache, private', 'Location' => '/login']);

        $this->invokeHandler($request, $response);

        $this->assertStringNotContainsString('public', $response->headers->get('Cache-Control'));
    }

    public function test_zero_max_age_is_allowed(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['core.base.general.public_cache_max_age' => 0]);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringContainsString('s-maxage=0', $cacheControl);
    }

    public function test_string_max_age_config_converted_to_int(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['core.base.general.public_cache_max_age' => '1800']);

        $request = Request::create('/', 'GET');
        $response = new Response('Test content');

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=1800', $cacheControl);
        $this->assertStringContainsString('s-maxage=1800', $cacheControl);
    }

    public function test_session_cookies_preserved_when_response_contains_csrf_form(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['session.cookie' => 'botble_session']);

        $html = '<html><body><form><input type="hidden" name="_token" value="abc123"></form></body></html>';
        $request = Request::create('/', 'GET');
        $response = new Response($html);
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));

        $this->invokeHandler($request, $response);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertContains('XSRF-TOKEN', $cookieNames);
        $this->assertContains('botble_session', $cookieNames);
    }

    public function test_session_cookies_preserved_when_response_contains_csrf_ajax(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['session.cookie' => 'botble_session']);

        $html = '<html><body><script>fetch(url, {headers: {"X-CSRF-TOKEN": token}})</script></body></html>';
        $request = Request::create('/', 'GET');
        $response = new Response($html);
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));

        $this->invokeHandler($request, $response);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertContains('XSRF-TOKEN', $cookieNames);
        $this->assertContains('botble_session', $cookieNames);
    }

    public function test_session_cookies_removed_when_no_csrf_in_response(): void
    {
        config(['core.base.general.enable_public_cache_control' => true]);
        config(['session.cookie' => 'botble_session']);

        $html = '<html><body><h1>Static content page</h1></body></html>';
        $request = Request::create('/', 'GET');
        $response = new Response($html);
        $response->headers->setCookie(new Cookie('XSRF-TOKEN', 'token123'));
        $response->headers->setCookie(new Cookie('botble_session', 'session123'));

        $this->invokeHandler($request, $response);

        $cookieNames = array_map(fn (Cookie $c) => $c->getName(), $response->headers->getCookies());
        $this->assertNotContains('XSRF-TOKEN', $cookieNames);
        $this->assertNotContains('botble_session', $cookieNames);
    }
}
