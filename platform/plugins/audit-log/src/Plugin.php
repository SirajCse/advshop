<?php

namespace Botble\AuditLog;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Widget\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('audit_histories');

        Widget::query()
            ->where('widget_id', 'widget_audit_logs')
            ->each(fn (Model $dashboardWidget) => $dashboardWidget->delete());
    }
}
