<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_order_product_tax_components')) {
            return;
        }

        Schema::create('ec_order_product_tax_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_product_id');
            $table->string('name', 191);
            $table->string('code', 50);
            $table->decimal('rate', 8, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('jurisdiction', 191)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('order_product_id', 'idx_opt_order_product');
            $table->index(['order_product_id', 'code'], 'idx_opt_order_product_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_product_tax_components');
    }
};
