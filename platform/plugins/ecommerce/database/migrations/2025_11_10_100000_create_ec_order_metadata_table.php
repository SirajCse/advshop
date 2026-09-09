<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ec_order_metadata')) {
            return;
        }

        Schema::create('ec_order_metadata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->index();
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'meta_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_metadata');
    }
};
