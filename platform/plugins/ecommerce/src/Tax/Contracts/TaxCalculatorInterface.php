<?php

namespace Botble\Ecommerce\Tax\Contracts;

use Botble\Ecommerce\Tax\DTOs\TaxContext;
use Botble\Ecommerce\Tax\DTOs\TaxResult;

interface TaxCalculatorInterface
{
    public function calculate(TaxContext $context): TaxResult;

    public function supports(TaxContext $context): bool;
}
