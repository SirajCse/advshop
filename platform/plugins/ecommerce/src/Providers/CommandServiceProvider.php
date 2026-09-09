<?php

namespace Botble\Ecommerce\Providers;

use Botble\Ecommerce\Commands\CancelExpiredDeletionRequests;
use Botble\Ecommerce\Commands\CancelPendingOrdersCommand;
use Botble\Ecommerce\Commands\CheckAbandonedCartsCommand;
use Botble\Ecommerce\Commands\CleanupExpiredCartsCommand;
use Botble\Ecommerce\Commands\SeedEuVatRatesCommand;
use Botble\Ecommerce\Commands\SendAbandonedCartsEmailCommand;
use Botble\Ecommerce\Models\SharedWishlist;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            SendAbandonedCartsEmailCommand::class,
            CancelExpiredDeletionRequests::class,
            CancelPendingOrdersCommand::class,
            CheckAbandonedCartsCommand::class,
            CleanupExpiredCartsCommand::class,
            SeedEuVatRatesCommand::class,
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command(SendAbandonedCartsEmailCommand::class)->weekly();
            $schedule->command(CancelExpiredDeletionRequests::class)->daily();

            $schedule->command(CheckAbandonedCartsCommand::class)
                ->hourly()
                ->when(fn () => get_ecommerce_setting('abandoned_cart_enabled', false));

            $schedule->command(CheckAbandonedCartsCommand::class, ['--cleanup'])
                ->daily()
                ->when(fn () => get_ecommerce_setting('abandoned_cart_enabled', false));

            $schedule->command('model:prune', [
                '--model' => [SharedWishlist::class],
            ])->daily();

            $schedule->command(CleanupExpiredCartsCommand::class)->daily();

            $schedule->command(CancelPendingOrdersCommand::class)
                ->everyFiveMinutes()
                ->when(fn () => get_ecommerce_setting('auto_cancel_pending_orders_enabled', false));
        });
    }
}
