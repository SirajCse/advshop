<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('testimonials_translations')) {
            return;
        }

        Schema::table('testimonials_translations', function (Blueprint $table): void {
            $table->index('testimonials_id', 'idx_testimonials_trans_testimonials_id');
            $table->index(['testimonials_id', 'lang_code'], 'idx_testimonials_trans_testimonial_lang');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('testimonials_translations')) {
            return;
        }

        Schema::table('testimonials_translations', function (Blueprint $table): void {
            $table->dropIndex('idx_testimonials_trans_testimonials_id');
            $table->dropIndex('idx_testimonials_trans_testimonial_lang');
        });
    }
};
