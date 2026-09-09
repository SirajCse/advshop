<?php

namespace Botble\Ecommerce\Events;

use Botble\Base\Events\Event;
use Carbon\CarbonInterface;

class ProductViewed extends Event
{
    public function __construct(public int|string $productId, public CarbonInterface $dateTime)
    {
    }
}
