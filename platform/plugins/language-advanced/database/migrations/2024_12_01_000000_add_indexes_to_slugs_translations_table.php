<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('slugs_translations')) {
            return;
        }

        try {
            if (! Schema::hasIndex('slugs_translations', 'idx_slugid_key_prefix')) {
                Schema::table('slugs_translations', function (Blueprint $table): void {
                    $table->index(['slugs_id', 'key', 'prefix'], 'idx_slugid_key_prefix');
                });
            }
        } catch (Exception) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('slugs_translations')) {
            return;
        }

        if (Schema::hasIndex('slugs_translations', 'idx_slugid_key_prefix')) {
            Schema::table('slugs_translations', function (Blueprint $table): void {
                $table->dropIndex('idx_slugid_key_prefix');
            });
        }
    }
};
