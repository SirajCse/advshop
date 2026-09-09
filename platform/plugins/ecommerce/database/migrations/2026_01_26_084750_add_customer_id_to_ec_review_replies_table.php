<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_review_replies', 'customer_id')) {
            return;
        }

        Schema::table('ec_review_replies', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('customer_id')->nullable()->after('user_id');
            $table->dropUnique(['review_id', 'user_id']);
            $table->unique(['review_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ec_review_replies', function (Blueprint $table): void {
            $table->dropUnique(['review_id']);
            $table->dropColumn('customer_id');
            $table->foreignId('user_id')->change();
            $table->unique(['review_id', 'user_id']);
        });
    }
};
