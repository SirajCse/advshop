<?php

namespace Botble\Base\Http\Middleware;

use Botble\Base\Facades\AdminHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicCacheControl
{
    public function handleRequestHandled(RequestHandled $event): void
    {
        $request = $event->request;
        $response = $event->response;

        if (! $this->shouldApplyPublicCache($request, $response)) {
            return;
        }

        $maxAge = (int) config('core.base.general.public_cache_max_age', 600);

        $response->headers->set('Cache-Control', sprintf('public, max-age=%d, s-maxage=%d', $maxAge, $maxAge));
        $response->headers->remove('Pragma');

        if (! $this->responseContainsCsrfTokens($response)) {
            $this->removeSessionCookies($response);
        }
    }

    protected function shouldApplyPublicCache(Request $request, mixed $response): bool
    {
        if (! config('core.base.general.enable_public_cache_control', false)) {
            return false;
        }

        if (! $request->isMethodSafe()) {
            return false;
        }

        if (! $response->isSuccessful()) {
            return false;
        }

        if (AdminHelper::isInAdmin()) {
            return false;
        }

        if (Auth::guard()->check()) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        return true;
    }

    protected function responseContainsCsrfTokens(mixed $response): bool
    {
        $content = $response->getContent();

        if (! $content) {
            return false;
        }

        return str_contains($content, 'name="_token"')
            || str_contains($content, 'X-CSRF-TOKEN')
            || str_contains($content, 'csrf_token');
    }

    protected function removeSessionCookies(mixed $response): void
    {
        // A publicly cacheable response must not carry ANY per-visitor Set-Cookie
        // (session, XSRF, ecommerce footprints, cookie-consent, etc.); otherwise
        // reverse proxies / CDNs refuse to store it. This runs only for safe,
        // non-admin, non-authenticated responses with no CSRF tokens (see caller),
        // so dropping all cookies is safe and lets the response be cached.
        $response->headers->remove('Set-Cookie');

        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
        }
    }
}
