<?php

namespace Botble\GetStarted\Supports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GetStartedHelper
{
    /**
     * Whether the "Change default account info" step should be shown.
     *
     * Returns false only when the demo default username + password are
     * configured AND the logged-in user has already moved off them (so there
     * is nothing left to secure). Otherwise the account step is shown.
     *
     * Both the wizard view (to render the stepper) and the controller (to skip
     * the step server-side) rely on this single source of truth.
     */
    public static function shouldChangeDefaultAccount(): bool
    {
        $user = Auth::guard()->user();

        if (! $user) {
            return false;
        }

        $defaultUsername = config('core.base.general.demo.account.username');
        $defaultPassword = config('core.base.general.demo.account.password');

        $alreadyChanged = $defaultUsername
            && $defaultPassword
            && $user->username != $defaultUsername
            && ! Hash::check($defaultPassword, $user->getAuthPassword());

        return ! $alreadyChanged;
    }
}
