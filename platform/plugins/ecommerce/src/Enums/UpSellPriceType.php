<?php

namespace Botble\Ecommerce\Enums;

use Botble\Base\Supports\Enum;

class UpSellPriceType extends Enum
{
    public const FIXED = 'fixed';

    public const PERCENT = 'percent';

    protected static $langPath = 'plugins/ecommerce::products.up_sell_price_type';
}
