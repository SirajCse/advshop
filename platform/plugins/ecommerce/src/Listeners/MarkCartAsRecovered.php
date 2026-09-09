<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Services\AbandonedCartService;
use Throwable;

class MarkCartAsRecovered
{
    public function __construct(
        protected AbandonedCartService $abandonedCartService
    ) {
    }

    public function handle(OrderPlacedEvent $event): void
    {
        try {
            $this->abandonedCartService->markCartAsRecovered($event->order);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
