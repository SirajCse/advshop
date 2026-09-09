<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        try {
            Schema::table('meta_boxes', function (Blueprint $table): void {
                $table->index(['reference_id', 'reference_type'], 'meta_boxes_ref_idx');
            });
        } catch (Throwable) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('meta_boxes', function (Blueprint $table): void {
                $table->dropIndex('meta_boxes_ref_idx');
            });
        } catch (Throwable) {
        }
    }
};
