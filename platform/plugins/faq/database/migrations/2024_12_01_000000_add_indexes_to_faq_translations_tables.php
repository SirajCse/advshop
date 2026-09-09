<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('faq_categories_translations')) {
            Schema::table('faq_categories_translations', function (Blueprint $table): void {
                $table->index('faq_categories_id', 'idx_faq_cat_trans_faq_cat_id');
                $table->index(['faq_categories_id', 'lang_code'], 'idx_faq_cat_trans_faq_cat_lang');
            });
        }

        if (Schema::hasTable('faqs_translations')) {
            Schema::table('faqs_translations', function (Blueprint $table): void {
                $table->index('faqs_id', 'idx_faqs_trans_faqs_id');
                $table->index(['faqs_id', 'lang_code'], 'idx_faqs_trans_faq_lang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('faq_categories_translations')) {
            Schema::table('faq_categories_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_faq_cat_trans_faq_cat_id');
                $table->dropIndex('idx_faq_cat_trans_faq_cat_lang');
            });
        }

        if (Schema::hasTable('faqs_translations')) {
            Schema::table('faqs_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_faqs_trans_faqs_id');
                $table->dropIndex('idx_faqs_trans_faq_lang');
            });
        }
    }
};
