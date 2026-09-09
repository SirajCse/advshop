<?php

namespace Botble\RequestLog\Listeners;

use Botble\Base\Facades\BaseHelper;
use Botble\RequestLog\Events\RequestHandlerEvent;
use Botble\RequestLog\Models\RequestLog;
use Botble\Setting\Enums\DataRetentionPeriod;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RequestHandlerListener
{
    public function __construct(protected Request $request)
    {
    }

    public function handle(RequestHandlerEvent $event): bool
    {
        try {
            if ($event->code === 404) {
                return false;
            }

            $url = $this->request->fullUrl();

            if (Str::contains($url, ['.js.map', '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot'])) {
                return false;
            }

            if (! Cache::has('pruned_request_logs_table')) {
                $days = setting('request_log_data_retention_period', DataRetentionPeriod::ONE_MONTH);

                if ($days != DataRetentionPeriod::NEVER) {
                    (new RequestLog())->pruneAll();
                }

                Cache::put('pruned_request_logs_table', 1, Carbon::now()->addDay());
            }

            $requestLog = RequestLog::query()->firstOrNew([
                'url' => Str::limit($url, 120),
                'status_code' => $event->code,
            ]);

            if ($referrer = $this->request->header('referrer')) {
                $requestLog->referrer = array_filter(array_unique(array_merge((array) $requestLog->referrer, [$referrer])));
            }

            if (Auth::guard()->check()) {
                $requestLog->user_id = array_filter(array_unique(array_merge((array) $requestLog->user_id, [Auth::guard()->id()])));
            }

            $requestLog->count = $requestLog->exists ? $requestLog->count + 1 : 1;

            return $requestLog->save();
        } catch (Exception $exception) {
            BaseHelper::logError($exception);

            return false;
        }
    }
}
