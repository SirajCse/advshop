<?php

namespace Botble\Shortcode\Tests\Feature;

use Botble\Shortcode\Http\Middleware\ShortcodePerformanceMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShortcodePerformanceMiddlewareTest extends TestCase
{
    public function testMiddlewareMeasuresExecutionTime(): void
    {
        config()->set('app.debug', true);

        $middleware = new ShortcodePerformanceMiddleware();
        $request = Request::create('/test');

        $response = $middleware->handle($request, function () {
            usleep(10000); // 10ms delay

            return new Response('OK');
        });

        $executionTime = (float) $response->headers->get('X-Shortcode-Execution-Time');

        $this->assertGreaterThan(0.005, $executionTime, 'Execution time should reflect actual request duration');
    }

    public function testMiddlewareSkipsTimingWhenDebugDisabled(): void
    {
        config()->set('app.debug', false);

        $middleware = new ShortcodePerformanceMiddleware();
        $request = Request::create('/test');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertNull(
            $response->headers->get('X-Shortcode-Execution-Time'),
            'Should not add timing header when debug mode is off'
        );
    }
}
