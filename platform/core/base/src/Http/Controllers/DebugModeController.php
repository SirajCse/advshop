<?php

namespace Botble\Base\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class DebugModeController extends BaseController
{
    public function postTurnOff(): BaseHttpResponse
    {
        $response = $this->httpResponse();

        if (! App::hasDebugModeEnabled()) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::system.debug_mode_already_disabled'));
        }

        $env = App::environmentFilePath();

        if (! File::isWritable($env)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::system.env_file_not_writable'));
        }

        $replaced = preg_replace(
            '/^APP_DEBUG=(.+)/m',
            'APP_DEBUG=false',
            $input = File::get($env)
        );

        if ($replaced === $input || $replaced === null) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::system.unable_set_debug_mode'));
        }

        File::put($env, $replaced);

        File::delete(App::getCachedConfigPath());

        return $response
            ->setMessage(trans('core/base::system.debug_mode_disabled_successfully'));
    }
}
