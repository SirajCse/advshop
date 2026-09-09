<?php

namespace Botble\Analytics\Http\Controllers\Settings;

use Botble\Analytics\Facades\Analytics;
use Botble\Analytics\Forms\AnalyticsSettingForm;
use Botble\Analytics\Http\Requests\Settings\AnalyticsSettingRequest;
use Botble\Analytics\Period;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Setting\Http\Controllers\SettingController;
use Exception;
use Illuminate\Http\JsonResponse;

class AnalyticsSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('core/base::base.panel.others'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/analytics::analytics.settings.title'));

        return AnalyticsSettingForm::create()->renderForm();
    }

    public function update(AnalyticsSettingRequest $request): BaseHttpResponse
    {
        return $this->performUpdate($request->validated());
    }

    public function testConnection(): JsonResponse
    {
        try {
            $credentials = setting('analytics_service_account_credentials');
            $propertyId = setting('analytics_property_id');

            if (empty($credentials) || empty($propertyId)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('plugins/analytics::analytics.settings.credentials_or_property_missing'),
                ], 400);
            }

            $period = Period::days(1);
            Analytics::dateRange($period)->metrics('sessions')->limit(1)->get();

            return response()->json([
                'success' => true,
                'message' => trans('plugins/analytics::analytics.settings.connection_success'),
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            $userMessage = match (true) {
                str_contains($errorMessage, 'PERMISSION_DENIED') => trans('plugins/analytics::analytics.settings.permission_denied'),
                str_contains($errorMessage, 'UNAUTHENTICATED') => trans('plugins/analytics::analytics.settings.authentication_failed'),
                str_contains($errorMessage, 'NOT_FOUND') => trans('plugins/analytics::analytics.settings.property_not_found'),
                str_contains($errorMessage, 'API has not been used') => trans('plugins/analytics::analytics.settings.api_not_enabled'),
                default => trans('plugins/analytics::analytics.settings.connection_failed') . ': ' . $errorMessage,
            };

            return response()->json([
                'success' => false,
                'message' => $userMessage,
            ], 500);
        }
    }
}
