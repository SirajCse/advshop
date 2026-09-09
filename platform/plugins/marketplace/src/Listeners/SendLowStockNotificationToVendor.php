<?php

namespace Botble\Marketplace\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Illuminate\Support\Facades\Cache;

class SendLowStockNotificationToVendor
{
    public function handle(ProductQuantityUpdatedEvent $event): void
    {
        $product = $event->product;

        $parentProduct = $product->original_product;

        if (! $parentProduct->with_storehouse_management) {
            return;
        }

        $threshold = MarketplaceHelper::lowStockThreshold();

        if ($product->quantity > $threshold) {
            return;
        }

        $store = $parentProduct->store;

        if (! $store || ! $store->email) {
            return;
        }

        $cacheKey = 'marketplace:low_stock_notified:' . $product->getKey();

        if (Cache::has($cacheKey)) {
            return;
        }

        $mailer = EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME);

        if (! $mailer->templateEnabled('store-low-stock')) {
            return;
        }

        $productUrl = $parentProduct->url;

        $mailer->setVariableValues([
            'store_name' => $store->name,
            'product_name' => $parentProduct->name,
            'product_url' => $productUrl,
            'product_quantity' => (string) $product->quantity,
            'low_stock_threshold' => (string) $threshold,
        ]);

        $mailer->sendUsingTemplate('store-low-stock', $store->email);

        Cache::put($cacheKey, true, now()->addHours(24));
    }
}
