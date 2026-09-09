<?php

namespace Botble\Ecommerce\Tax\Enums;

enum TaxCalculationType: string
{
    case EXCLUSIVE = 'exclusive';
    case INCLUSIVE = 'inclusive';
}
