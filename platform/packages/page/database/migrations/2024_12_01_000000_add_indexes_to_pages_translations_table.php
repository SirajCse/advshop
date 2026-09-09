<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('pages_translations')) {
            return;
        }

        Schema::table('pages_translations', function (Blueprint $table): void {
            $table->index('pages_id', 'idx_pages_trans_pages_id');
            $table->index(['pages_id', 'lang_code'], 'idx_pages_trans_page_lang');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pages_translations')) {
            return;
        }

        Schema::table('pages_translations', function (Blueprint $table): void {
            $table->dropIndex('idx_pages_trans_pages_id');
            $table->dropIndex('idx_pages_trans_page_lang');
        });
    }
};
