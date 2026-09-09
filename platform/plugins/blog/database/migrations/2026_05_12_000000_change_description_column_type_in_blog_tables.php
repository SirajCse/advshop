<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        foreach (['posts', 'categories', 'tags', 'posts_translations', 'categories_translations', 'tags_translations'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'description')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->text('description')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['posts', 'categories', 'tags', 'posts_translations', 'categories_translations', 'tags_translations'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'description')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->string('description', 400)->nullable()->change();
                });
            }
        }
    }
};
