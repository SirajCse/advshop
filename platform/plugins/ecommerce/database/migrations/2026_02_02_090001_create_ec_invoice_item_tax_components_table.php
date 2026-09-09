<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_invoice_item_tax_components')) {
            return;
        }

        Schema::create('ec_invoice_item_tax_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_item_id')->constrained('ec_invoice_items')->cascadeOnDelete();
            $table->string('name', 191);
            $table->string('code', 50);
            $table->decimal('rate', 8, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('jurisdiction', 191)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('invoice_item_id', 'idx_iitc_invoice_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_invoice_item_tax_components');
    }
};
