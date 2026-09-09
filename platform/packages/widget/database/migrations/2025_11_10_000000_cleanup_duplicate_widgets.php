<?php

use Botble\Widget\Models\Widget;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->cleanupDuplicateWidgets();

        if (! $this->hasUniqueIndex()) {
            Schema::table('widgets', function (Blueprint $table): void {
                $table->unique(['theme', 'sidebar_id', 'widget_id', 'position'], 'widgets_unique_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasUniqueIndex()) {
            Schema::table('widgets', function (Blueprint $table): void {
                $table->dropUnique('widgets_unique_index');
            });
        }
    }

    protected function cleanupDuplicateWidgets(): void
    {
        $totalBefore = Widget::query()->count();

        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                DELETE w1 FROM widgets w1
                INNER JOIN widgets w2
                WHERE w1.theme = w2.theme
                    AND w1.sidebar_id = w2.sidebar_id
                    AND w1.widget_id = w2.widget_id
                    AND w1.position = w2.position
                    AND w1.id < w2.id
            ');
        } else {
            $this->cleanupDuplicatesGeneric();
        }

        $totalAfter = Widget::query()->count();
        $deletedCount = $totalBefore - $totalAfter;

        if ($deletedCount > 0) {
            Log::info("Widget cleanup: removed {$deletedCount} duplicate widgets. Remaining: {$totalAfter}");
        }
    }

    protected function cleanupDuplicatesGeneric(): void
    {
        $allWidgets = Widget::query()
            ->orderBy('id', 'desc')
            ->get();

        $grouped = $allWidgets->groupBy(function ($widget) {
            return $widget->theme . '|' . $widget->sidebar_id . '|' . $widget->widget_id . '|' . $widget->position;
        });

        $idsToKeep = [];
        $duplicateCount = 0;

        foreach ($grouped as $key => $widgets) {
            if ($widgets->count() > 1) {
                $idsToKeep[] = $widgets->first()->id;

                $duplicateCount += $widgets->count() - 1;

                $widgets->slice(1)->each(function ($widget): void {
                    $widget->delete();
                });
            } else {
                $idsToKeep[] = $widgets->first()->id;
            }
        }

        if ($duplicateCount > 0) {
            Log::info("Removed {$duplicateCount} duplicate widgets");
        }
    }

    protected function hasUniqueIndex(): bool
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $tableName = 'widgets';

        if ($driver === 'mysql') {
            $indexes = DB::select(
                "SHOW INDEX FROM {$tableName} WHERE Key_name = 'widgets_unique_index'"
            );

            return ! empty($indexes);
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = '{$tableName}' AND name = 'widgets_unique_index'"
            );

            return ! empty($indexes);
        }

        if ($driver === 'pgsql') {
            $indexes = DB::select(
                "SELECT indexname FROM pg_indexes WHERE tablename = '{$tableName}' AND indexname = 'widgets_unique_index'"
            );

            return ! empty($indexes);
        }

        return false;
    }
};
