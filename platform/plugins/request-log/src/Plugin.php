<?php

namespace Botble\RequestLog;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Widget\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('request_logs');

        Widget::query()
            ->where('widget_id', 'widget_request_errors')
            ->each(fn (Model $dashboardWidget) => $dashboardWidget->delete());
    }
}
