<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Ecommerce\Events\OrderReturnedEvent;

class OrderReturnedNotification
{
    public function handle(OrderReturnedEvent $event): void
    {
        $orderReturn = $event->order;
        $customer = $orderReturn->customer;

        if (! $customer?->id) {
            return;
        }

        $customerName = $customer->name ?: $orderReturn->order?->address?->name;

        event(new AdminNotificationEvent(
            AdminNotificationItem::make()
                ->title(trans('plugins/ecommerce::order.return_order_notifications.return_order'))
                ->description(trans('plugins/ecommerce::order.return_order_notifications.description', [
                    'customer' => $customerName,
                ]))
                ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), route('order_returns.edit', $orderReturn->id))
        ));
    }
}
