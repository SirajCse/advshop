<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('ec_customers', 'tax_class')) {
            return;
        }

        Schema::table('ec_customers', function (Blueprint $table): void {
            $table->string('tax_class', 50)->default('regular')->after('dob');
            $table->string('tax_id', 191)->nullable()->after('tax_class');
        });
    }

    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table): void {
            $table->dropColumn(['tax_class', 'tax_id']);
        });
    }
};
