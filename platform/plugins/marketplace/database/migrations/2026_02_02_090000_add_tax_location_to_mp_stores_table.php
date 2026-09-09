<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('mp_stores', 'tax_country')) {
            return;
        }

        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->string('tax_country', 120)->nullable()->after('tax_id');
            $table->string('tax_state', 120)->nullable()->after('tax_country');
        });
    }

    public function down(): void
    {
        Schema::table('mp_stores', function (Blueprint $table): void {
            $table->dropColumn(['tax_country', 'tax_state']);
        });
    }
};
