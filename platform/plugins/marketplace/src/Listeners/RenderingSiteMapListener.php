<?php

namespace Botble\Marketplace\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Store;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;

class RenderingSiteMapListener
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'stores':
                    $stores = Store::query()
                        ->with('slugable')
                        ->where('status', BaseStatusEnum::PUBLISHED)->latest()
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($stores as $store) {
                        if (! $store->slugable) {
                            continue;
                        }

                        SiteMapManager::add($store->url, $store->updated_at, '0.8');
                    }

                    break;
                case 'pages':
                    if (MarketplaceHelper::isStoresPageEnabled()) {
                        SiteMapManager::add(route('public.stores'), null, '1', 'monthly');
                    }

                    break;
            }
        } elseif (MarketplaceHelper::isStoresPageEnabled()) {
            SiteMapManager::addSitemap(SiteMapManager::route('stores'));
        }
    }
}
