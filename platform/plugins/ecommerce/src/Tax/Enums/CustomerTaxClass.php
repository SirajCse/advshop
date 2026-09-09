<?php

namespace Botble\Ecommerce\Tax\Enums;

enum CustomerTaxClass: string
{
    case REGULAR = 'regular';
    case BUSINESS = 'business';
    case TAX_EXEMPT = 'tax_exempt';
    case RESELLER = 'reseller';
}
