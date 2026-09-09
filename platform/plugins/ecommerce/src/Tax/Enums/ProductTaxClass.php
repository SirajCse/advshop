<?php

namespace Botble\Ecommerce\Tax\Enums;

enum ProductTaxClass: string
{
    case STANDARD = 'standard';
    case REDUCED = 'reduced';
    case ZERO_RATED = 'zero_rated';
    case EXEMPT = 'exempt';
}
