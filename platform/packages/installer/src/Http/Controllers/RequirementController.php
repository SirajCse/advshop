<?php

namespace Botble\Installer\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Installer\Supports\RequirementsChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class RequirementController extends BaseController
{
    public function index(Request $request, RequirementsChecker $requirements): View|RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('installers.welcome');
        }

        $phpSupportInfo = $requirements->checkPhpVersion(get_minimum_php_version());
        $requirements = $requirements->check(config('packages.installer.installer.requirements'));

        return view('packages/installer::requirements', compact('requirements', 'phpSupportInfo'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('installers.welcome');
        }

        $requirements = app(RequirementsChecker::class);
        $phpSupportInfo = $requirements->checkPhpVersion(get_minimum_php_version());

        if (!$phpSupportInfo['supported']) {
            return redirect()
                ->route('installers.requirements.index')
                ->with('error', 'PHP version does not meet the minimum requirement.');
        }

        $requirementsCheck = $requirements->check(config('packages.installer.installer.requirements'));

        if (!$this->allRequirementsMet($requirementsCheck)) {
            return redirect()
                ->route('installers.requirements.index')
                ->with('error', 'Please fix all requirements before proceeding.');
        }

        return redirect()->to(
            URL::signedRoute(
                'installers.environment.index',
                expiration: Carbon::now()->addMinutes(30)
            )
        );
    }

    private function allRequirementsMet(array $requirements): bool
    {
        foreach ($requirements as $requirement) {
            if (!$requirement['check']) {
                return false;
            }
        }
        return true;
    }
}
