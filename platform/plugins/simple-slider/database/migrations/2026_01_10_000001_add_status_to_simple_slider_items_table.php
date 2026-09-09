<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('simple_slider_items', 'status')) {
            return;
        }

        Schema::table('simple_slider_items', function (Blueprint $table): void {
            $table->string('status', 60)->default('published')->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('simple_slider_items', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
