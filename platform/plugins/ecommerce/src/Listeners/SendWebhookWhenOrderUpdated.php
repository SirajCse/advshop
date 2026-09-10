<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SendWebhookWhenOrderUpdated
{
    public function handle(UpdatedContentEvent $event): void
    {
        if ($event->screen !== 'plugin-order' || ! $event->data instanceof Order) {
            return;
        }

        $webhookURL = get_ecommerce_setting('order_updated_webhook_url');

        if (! $webhookURL || ! URL::isValidUrl($webhookURL) || BaseHelper::hasDemoModeEnabled()) {
            return;
        }

        try {
            $order = $event->data;

            $data = $order->toWebhookData();

            $data = apply_filters('ecommerce_order_updated_webhook_data', $data, $order);

            Http::withoutVerifying()
                ->connectTimeout(5)
                ->timeout(10)
                ->acceptJson()
                ->post($webhookURL, $data);
        } catch (Exception $exception) {
            info($exception->getMessage());
        }
    }
}
