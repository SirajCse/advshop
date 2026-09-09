<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('mp_stores', 'vacation_mode')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->boolean('vacation_mode')->default(false)->after('status');
            });
        }

        if (! Schema::hasColumn('mp_stores', 'vacation_message')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->text('vacation_message')->nullable()->after('vacation_mode');
            });
        }
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            foreach (['vacation_mode', 'vacation_message'] as $column) {
                if (Schema::hasColumn('mp_stores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
