<?php

use Botble\ACL\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('audit_histories', 'actor_type')) {
            return;
        }

        Schema::table('audit_histories', function (Blueprint $table): void {
            $table->string('actor_type')->nullable()->after('actor_id')->default(addslashes(User::class));
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('audit_histories', 'actor_type')) {
            return;
        }

        Schema::table('audit_histories', function (Blueprint $table): void {
            $table->dropColumn(['actor_type']);
        });
    }
};
