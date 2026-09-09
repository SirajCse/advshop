<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_shipping_rule_items', 'name')) {
            Schema::table('ec_shipping_rule_items', function (Blueprint $table): void {
                $table->string('name', 120)->nullable()->after('shipping_rule_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_shipping_rule_items', 'name')) {
            Schema::table('ec_shipping_rule_items', function (Blueprint $table): void {
                $table->dropColumn('name');
            });
        }
    }
};
