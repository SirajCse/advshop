<?php

namespace Botble\ACL\Listeners;

use Botble\ACL\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginListener
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        try {
            $event->user->last_login = Carbon::now();
            $event->user->sessions_invalidated_at = null;
            $event->user->save();
        } catch (Throwable $exception) {
            Log::error('Failed to update user login timestamp: ' . $exception->getMessage(), [
                'user_id' => $event->user->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
