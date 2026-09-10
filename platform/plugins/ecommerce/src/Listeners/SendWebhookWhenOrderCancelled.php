<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SendWebhookWhenOrderCancelled
{
    public function handle(OrderCancelledEvent $event): void
    {
        $webhookURL = get_ecommerce_setting('order_cancelled_webhook_url');

        if (! $webhookURL || ! URL::isValidUrl($webhookURL) || BaseHelper::hasDemoModeEnabled()) {
            return;
        }

        try {
            $order = $event->order;

            $data = $order->toWebhookData();
            $data['cancellation_reason'] = $event->reason ?? null;

            $data = apply_filters('ecommerce_order_cancelled_webhook_data', $data, $order);

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
