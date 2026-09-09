<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('media_folder_permissions')) {
            return;
        }

        Schema::create('media_folder_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->index();
            $table->foreignId('user_id')->index();
            $table->string('permission', 20)->default('view');
            $table->foreignId('granted_by')->nullable();
            $table->timestamps();

            $table->unique(['folder_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_folder_permissions');
    }
};
