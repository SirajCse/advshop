<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('ec_abandoned_carts')) {
            return;
        }

        Schema::table('ec_abandoned_carts', function (Blueprint $table): void {
            if (! Schema::hasColumn('ec_abandoned_carts', 'last_email_sequence')) {
                $table->unsignedTinyInteger('last_email_sequence')->default(0)->after('reminders_sent');
            }

            if (! Schema::hasColumn('ec_abandoned_carts', 'recovery_token')) {
                $table->string('recovery_token', 64)->nullable()->unique()->after('last_email_sequence');
            }

            if (! Schema::hasColumn('ec_abandoned_carts', 'coupon_code')) {
                $table->string('coupon_code', 32)->nullable()->after('recovery_token');
            }

            if (! Schema::hasColumn('ec_abandoned_carts', 'clicked_at')) {
                $table->timestamp('clicked_at')->nullable()->after('coupon_code');
            }

            if (! Schema::hasColumn('ec_abandoned_carts', 'unsubscribe_token')) {
                $table->string('unsubscribe_token', 64)->nullable()->unique()->after('clicked_at');
            }

            if (! Schema::hasColumn('ec_abandoned_carts', 'unsubscribed_at')) {
                $table->timestamp('unsubscribed_at')->nullable()->after('unsubscribe_token');
            }
        });

        // Generate recovery tokens for existing records
        DB::table('ec_abandoned_carts')
            ->whereNull('recovery_token')
            ->chunkById(100, function ($carts): void {
                foreach ($carts as $cart) {
                    DB::table('ec_abandoned_carts')
                        ->where('id', $cart->id)
                        ->update([
                            'recovery_token' => Str::random(64),
                            'unsubscribe_token' => Str::random(64),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ec_abandoned_carts', function (Blueprint $table): void {
            $table->dropColumn([
                'last_email_sequence',
                'recovery_token',
                'coupon_code',
                'clicked_at',
                'unsubscribe_token',
                'unsubscribed_at',
            ]);
        });
    }
};
