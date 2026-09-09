<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('posts_translations')) {
            Schema::table('posts_translations', function (Blueprint $table): void {
                $table->index('posts_id', 'idx_posts_trans_posts_id');
                $table->index(['posts_id', 'lang_code'], 'idx_posts_trans_post_lang');
            });
        }

        if (Schema::hasTable('categories_translations')) {
            Schema::table('categories_translations', function (Blueprint $table): void {
                $table->index('categories_id', 'idx_categories_trans_categories_id');
                $table->index(['categories_id', 'lang_code'], 'idx_categories_trans_category_lang');
            });
        }

        if (Schema::hasTable('tags_translations')) {
            Schema::table('tags_translations', function (Blueprint $table): void {
                $table->index('tags_id', 'idx_tags_trans_tags_id');
                $table->index(['tags_id', 'lang_code'], 'idx_tags_trans_tag_lang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts_translations')) {
            Schema::table('posts_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_posts_trans_posts_id');
                $table->dropIndex('idx_posts_trans_post_lang');
            });
        }

        if (Schema::hasTable('categories_translations')) {
            Schema::table('categories_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_categories_trans_categories_id');
                $table->dropIndex('idx_categories_trans_category_lang');
            });
        }

        if (Schema::hasTable('tags_translations')) {
            Schema::table('tags_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_tags_trans_tags_id');
                $table->dropIndex('idx_tags_trans_tag_lang');
            });
        }
    }
};
