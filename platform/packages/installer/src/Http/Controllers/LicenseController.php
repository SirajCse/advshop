<?php

namespace Botble\Installer\Http\Controllers;

use Botble\Base\Exceptions\LicenseInvalidException;
use Botble\Base\Exceptions\LicenseIsAlreadyActivatedException;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Core;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Http\Requests\LicenseSettingRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LicenseController extends BaseController
{
    public function index(): View|RedirectResponse
    {
        return view('packages/installer::license');
    }

    public function store(LicenseSettingRequest $request, Core $core): RedirectResponse
    {
        // Bypass all license checks
        // Optionally set a default value
        Setting::forceSet('licensed_to', 'Pro User')->save();

        $finalUrl = URL::temporarySignedRoute('installers.final', Carbon::now()->addMinutes(30));
        return redirect()->to($finalUrl);
    }

    public function skip(): RedirectResponse
    {
        Core::make()->skipLicenseReminder();

        return redirect()->to(URL::temporarySignedRoute('installers.final', Carbon::now()->addMinutes(30)));
    }
}
