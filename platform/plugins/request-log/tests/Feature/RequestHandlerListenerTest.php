<?php

namespace Botble\RequestLog\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\RequestLog\Events\RequestHandlerEvent;
use Botble\RequestLog\Listeners\RequestHandlerListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class RequestHandlerListenerTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_skips_404_errors(): void
    {
        $request = Request::create('/non-existent-page');
        $listener = new RequestHandlerListener($request);
        $event = new RequestHandlerEvent(404);

        $result = $listener->handle($event);

        $this->assertFalse($result);
        $this->assertDatabaseCount('request_logs', 0);
    }

    public function test_skips_asset_file_urls(): void
    {
        $extensions = ['.css', '.js', '.png', '.jpg', '.ico', '.svg', '.woff', '.woff2'];

        foreach ($extensions as $ext) {
            $request = Request::create('/assets/file' . $ext);
            $listener = new RequestHandlerListener($request);
            $event = new RequestHandlerEvent(500);

            $result = $listener->handle($event);

            $this->assertFalse($result, "Should skip URL with extension {$ext}");
        }

        $this->assertDatabaseCount('request_logs', 0);
    }

    public function test_logs_500_errors_for_non_asset_urls(): void
    {
        $request = Request::create('/api/checkout');
        $listener = new RequestHandlerListener($request);
        $event = new RequestHandlerEvent(500);

        $result = $listener->handle($event);

        $this->assertTrue($result);
        $this->assertDatabaseCount('request_logs', 1);
        $this->assertDatabaseHas('request_logs', [
            'status_code' => 500,
        ]);
    }

    public function test_logs_403_errors(): void
    {
        $request = Request::create('/admin/restricted');
        $listener = new RequestHandlerListener($request);
        $event = new RequestHandlerEvent(403);

        $result = $listener->handle($event);

        $this->assertTrue($result);
        $this->assertDatabaseHas('request_logs', [
            'status_code' => 403,
        ]);
    }

    public function test_increments_count_on_duplicate_url(): void
    {
        $request = Request::create('/api/error');
        $event = new RequestHandlerEvent(500);

        $listener = new RequestHandlerListener($request);
        $listener->handle($event);
        $listener->handle($event);

        $this->assertDatabaseCount('request_logs', 1);
        $this->assertDatabaseHas('request_logs', [
            'count' => 2,
        ]);
    }
}
