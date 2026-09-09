<?php

namespace Botble\Base\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Supports\Core;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnlicensedController extends BaseController
{
    public function __construct(private readonly Core $core)
    {
    }

    public function index(Request $request): RedirectResponse
    {
        // 🔥 COMPLETELY BYPASS LICENSE CHECK
        // Always redirect to dashboard without any license verification
        return redirect()->route('dashboard.index');
    }

    public function postSkip(Request $request): RedirectResponse
    {
        // 🔥 SKIP LICENSE REMINDER
        // Always redirect to dashboard or redirect_url
        $this->validateRedirectUrl($request);

        return $request->filled('redirect_url')
            ? redirect()->to($request->input('redirect_url'))
            : redirect()->route('dashboard.index');
    }

    protected function validateRedirectUrl(Request $request): void
    {
        $request->validate(['redirect_url' => ['nullable', 'string', 'url']]);
    }
}
