<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SendWebhookWhenPaymentStatusUpdated
{
    public function handle(OrderPaymentConfirmedEvent $event): void
    {
        $webhookURL = get_ecommerce_setting('payment_status_updated_webhook_url');

        if (! $webhookURL || ! URL::isValidUrl($webhookURL) || BaseHelper::hasDemoModeEnabled()) {
            return;
        }

        try {
            $order = $event->order;

            $data = $order->toWebhookData();

            $data = apply_filters('ecommerce_payment_status_updated_webhook_data', $data, $order);

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
