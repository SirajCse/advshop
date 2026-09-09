<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('contact_custom_fields_translations')) {
            Schema::table('contact_custom_fields_translations', function (Blueprint $table): void {
                $table->index('contact_custom_fields_id', 'idx_contact_cf_trans_cf_id');
                $table->index(['contact_custom_fields_id', 'lang_code'], 'idx_contact_cf_trans_cf_lang');
            });
        }

        if (Schema::hasTable('contact_custom_field_options_translations')) {
            Schema::table('contact_custom_field_options_translations', function (Blueprint $table): void {
                $table->index('contact_custom_field_options_id', 'idx_contact_cfo_trans_cfo_id');
                $table->index(['contact_custom_field_options_id', 'lang_code'], 'idx_contact_cfo_trans_cfo_lang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contact_custom_fields_translations')) {
            Schema::table('contact_custom_fields_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_contact_cf_trans_cf_id');
                $table->dropIndex('idx_contact_cf_trans_cf_lang');
            });
        }

        if (Schema::hasTable('contact_custom_field_options_translations')) {
            Schema::table('contact_custom_field_options_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_contact_cfo_trans_cfo_id');
                $table->dropIndex('idx_contact_cfo_trans_cfo_lang');
            });
        }
    }
};
