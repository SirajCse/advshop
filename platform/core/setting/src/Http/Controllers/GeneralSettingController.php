<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Exceptions\LicenseInvalidException;
use Botble\Base\Exceptions\LicenseIsAlreadyActivatedException;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Core;
use Botble\Base\Supports\Language;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\GeneralSettingForm;
use Botble\Setting\Http\Requests\GeneralSettingRequest;
use Botble\Setting\Http\Requests\LicenseSettingRequest;
use Botble\Setting\Models\Setting as SettingModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class GeneralSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('core/setting::setting.general_setting'));

        $form = GeneralSettingForm::create();

        return view('core/setting::general', compact('form'));
    }

    public function update(GeneralSettingRequest $request): BaseHttpResponse
    {
        $data = Arr::except($request->input(), [
            'locale',
        ]);

        $locale = $request->input('locale');
        if ($locale && array_key_exists($locale, Language::getAvailableLocales())) {
            session()->put('site-locale', $locale);
        }

        $isDemoModeEnabled = BaseHelper::hasDemoModeEnabled();

        if (! $isDemoModeEnabled) {
            $data['locale'] = $locale;
        }

        cache()->forget('core.base.boot_settings');

        return $this->performUpdate($data);
    }

    // 🔥 BYPASS: Always return success for license verification
    public function getVerifyLicense(Request $request, Core $core)
    {
        // 🔥 Always return success - license is always valid
        $activatedAt = Carbon::now();

        $data = [
            'activated_at' => $activatedAt->format('M d Y'),
            'licensed_to' => setting('licensed_to', 'Free User'),
        ];

        // 🔥 Force set license data if not exists
        if (!Setting::has('licensed_to')) {
            Setting::forceSet('licensed_to', 'Free User');
            Setting::forceSet('license_file_content', json_encode([
                'license' => 'bypassed',
                'client' => 'Free User',
                'status' => 'active'
            ]));
            Setting::save();
        }

        // 🔥 Clear any reminders
        $core->clearLicenseReminder();

        return $this
            ->httpResponse()
            ->setMessage('Your license is activated.')
            ->setData($data);
    }

    // 🔥 BYPASS: Always return success for activation
    public function activateLicense(LicenseSettingRequest $request, Core $core): BaseHttpResponse
    {
        // 🔥 Always activate successfully without real verification
        $buyer = $request->input('buyer', 'Free User');

        if (filter_var($buyer, FILTER_VALIDATE_URL)) {
            $username = Str::afterLast($buyer, '/');
            $buyer = $username;
        }

        // 🔥 Force set license data
        Setting::forceSet('licensed_to', $buyer);
        Setting::forceSet('license_file_content', json_encode([
            'license' => 'bypassed-' . uniqid(),
            'client' => $buyer,
            'status' => 'active',
            'activated_at' => Carbon::now()->toIso8601String(),
        ]));
        Setting::save();

        // 🔥 Clear any reminders
        $core->clearLicenseReminder();
        session()->forget('license_check_time');

        $data = [
            'activated_at' => Carbon::now()->format('M d Y'),
            'licensed_to' => $buyer,
        ];

        return $this
            ->httpResponse()
            ->setMessage('Your license has been activated successfully.')
            ->setData($data);
    }

    // 🔥 BYPASS: Always return success for deactivation
    public function deactivateLicense(Core $core)
    {
        // 🔥 Just clear session data, don't actually deactivate
        session()->forget('license_check_time');

        // 🔥 Reset license data
        Setting::forceSet('licensed_to', '');
        Setting::forceSet('license_file_content', '');
        Setting::save();

        return $this
            ->httpResponse()
            ->setMessage('Deactivated license successfully!');
    }

    // 🔥 BYPASS: Always return success for reset
    public function resetLicense(LicenseSettingRequest $request, Core $core)
    {
        // 🔥 Always reset successfully
        session()->forget('license_check_time');

        // 🔥 Reset license data
        Setting::forceSet('licensed_to', 'Free User');
        Setting::forceSet('license_file_content', json_encode([
            'license' => 'bypassed-reset-' . uniqid(),
            'client' => 'Free User',
            'status' => 'active'
        ]));
        Setting::save();

        return $this
            ->httpResponse()
            ->setMessage('Your license has been reset successfully.');
    }

    protected function saveActivatedLicense(Core $core, string $buyer): array
    {
        // 🔥 Always return activated data
        $core->clearLicenseReminder();
        session()->forget('license_check_time');

        return [
            'activated_at' => Carbon::now()->format('M d Y'),
            'licensed_to' => $buyer,
        ];
    }

    private function getLicenseActivatedDate(Core $core): Carbon
    {
        // 🔥 Always return current time
        return Carbon::now();
    }
}
