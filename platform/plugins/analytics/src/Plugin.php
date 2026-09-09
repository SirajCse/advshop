<?php

namespace Botble\Analytics;

use Botble\Dashboard\Models\DashboardWidget;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Database\Eloquent\Model;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        DashboardWidget::query()
            ->whereIn('name', [
                'widget_analytics_general',
                'widget_analytics_page',
                'widget_analytics_browser',
                'widget_analytics_referrer',
            ])
            ->each(fn (Model $dashboardWidget) => $dashboardWidget->delete());

        Setting::delete([
            'google_analytics',
            'analytics_property_id',
            'analytics_service_account_credentials',
        ]);
    }
}
