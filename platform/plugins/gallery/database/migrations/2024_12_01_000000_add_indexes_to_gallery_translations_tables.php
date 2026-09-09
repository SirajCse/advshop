<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('galleries_translations')) {
            Schema::table('galleries_translations', function (Blueprint $table): void {
                $table->index('galleries_id', 'idx_galleries_trans_galleries_id');
                $table->index(['galleries_id', 'lang_code'], 'idx_galleries_trans_gallery_lang');
            });
        }

        if (Schema::hasTable('gallery_meta_translations')) {
            Schema::table('gallery_meta_translations', function (Blueprint $table): void {
                $table->index('gallery_meta_id', 'idx_gallery_meta_trans_gm_id');
                $table->index(['gallery_meta_id', 'lang_code'], 'idx_gallery_meta_trans_gm_lang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('galleries_translations')) {
            Schema::table('galleries_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_galleries_trans_galleries_id');
                $table->dropIndex('idx_galleries_trans_gallery_lang');
            });
        }

        if (Schema::hasTable('gallery_meta_translations')) {
            Schema::table('gallery_meta_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_gallery_meta_trans_gm_id');
                $table->dropIndex('idx_gallery_meta_trans_gm_lang');
            });
        }
    }
};
