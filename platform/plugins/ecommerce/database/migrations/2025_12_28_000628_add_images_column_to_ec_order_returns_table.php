<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_order_returns', function (Blueprint $table): void {
            $table->json('images')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('ec_order_returns', function (Blueprint $table): void {
            $table->dropColumn('images');
        });
    }
};
