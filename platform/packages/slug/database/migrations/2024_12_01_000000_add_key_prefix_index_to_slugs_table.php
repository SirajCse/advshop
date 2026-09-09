<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasIndex('slugs', 'idx_key_prefix')) {
            Schema::table('slugs', function (Blueprint $table): void {
                $table->index(['key', 'prefix'], 'idx_key_prefix');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('slugs', 'idx_key_prefix')) {
            Schema::table('slugs', function (Blueprint $table): void {
                $table->dropIndex('idx_key_prefix');
            });
        }
    }
};
