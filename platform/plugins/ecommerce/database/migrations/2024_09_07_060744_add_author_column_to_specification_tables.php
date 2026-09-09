<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        foreach (['ec_specification_groups', 'ec_specification_attributes', 'ec_specification_tables'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $hasType = Schema::hasColumn($tableName, 'author_type');
            $hasId = Schema::hasColumn($tableName, 'author_id');

            if ($hasType && $hasId) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($hasType, $hasId): void {
                if (! $hasType) {
                    $table->string('author_type')->nullable();
                }

                if (! $hasId) {
                    $table->unsignedBigInteger('author_id')->nullable();
                }

                $table->index(['author_type', 'author_id'], 'author_index');
            });
        }
    }

    public function down(): void
    {
        foreach (['ec_specification_groups', 'ec_specification_attributes', 'ec_specification_tables'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'author_id') && ! Schema::hasColumn($tableName, 'author_type')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (Schema::hasColumn($tableName, 'author_id') && Schema::hasColumn($tableName, 'author_type')) {
                    $table->dropMorphs('author');

                    return;
                }

                if (Schema::hasColumn($tableName, 'author_id')) {
                    $table->dropColumn('author_id');
                }

                if (Schema::hasColumn($tableName, 'author_type')) {
                    $table->dropColumn('author_type');
                }
            });
        }
    }
};
