<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('users', 'sessions_invalidated_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('sessions_invalidated_at')->nullable()->after('last_login');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'sessions_invalidated_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('sessions_invalidated_at');
        });
    }
};
