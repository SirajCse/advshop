<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('request_logs', function (Blueprint $table): void {
            // PERFORMANCE FIX: RequestHandlerListener runs on EVERY request and does
            // firstOrNew(['url' => ..., 'status_code' => ...]). Without this composite
            // index the query becomes a full table scan that gets slower every day
            // as the log table grows (this is a common cause of progressively
            // slower page loads in production).
            if (! Schema::hasIndex('request_logs', 'request_logs_url_status_code_index')) {
                $table->index(['url', 'status_code']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('request_logs', function (Blueprint $table): void {
            if (Schema::hasIndex('request_logs', 'request_logs_url_status_code_index')) {
                $table->dropIndex('request_logs_url_status_code_index');
            }
        });
    }
};
